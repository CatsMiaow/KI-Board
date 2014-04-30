<?php
class Modify extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->config->load('cf_register');
		$this->config->load('cf_icon');
		$this->load->library(array('form_validation', 'encrypt'));
		$this->load->model(array('Member_infor_model', 'Register_model'));
		define('WIDGET_SKIN', 'main');
		define('CSS_SKIN', 'jquery');
	}

	function index() {
		if (!$this->input->post('mb_password'))
			goto_url('/');

		if (!IS_MEMBER)
			alert('로그인 후 이용하여 주십시오.');

		if (SU_ADMIN)
			alert('관리자 아이디는 접근 불가합니다.', '/');

		$member = unserialize(MEMBER);
		
		if ($member['mb_id'] != $this->input->post('mb_id'))
			alert('로그인된 회원과 넘어온 정보가 서로 다릅니다.');
		
		$mb_password = ($this->session->userdata('ss_tmp_password')) ? $this->session->userdata('ss_tmp_password') : md5($this->input->post('mb_password'));
		if ($this->encrypt->decode($member['mb_password']) != $mb_password)
			alert('비밀번호가 맞지 않습니다.', 'member/confirm/qry/member.modify');

		// 수정 후 다시 이 폼으로 돌아오기 위해 임시로 저장해 놓음
		$this->session->set_userdata('ss_tmp_password', $mb_password);

		$this->_form($member);
	}

	function _form($member) {
		$token = get_token();
		
        $nick_modify = FALSE;
		if ($this->config->item('cf_use_nick'))
			$nick_modify = ($member['mb_nick_date'] <= date("Y-m-d", time() - ($this->config->item('cf_nick_modify') * 86400))) ? TRUE : FALSE;
			
        $open_modify = FALSE;
		if ($member['mb_open_date'] <= date('Y-m-d', time() - ($this->config->item('cf_open_modify') * 86400)))
			$open_modify = TRUE;
		
        // 회원경로
        $mb_path = '/member/'.substr($member['mb_id'],0,2).'/';
        
        // 아이콘
		$icon_path = $mb_path.$member['mb_id'].'.gif';
		$mb_icon = DATA_DIR.$icon_path;
		if (!file_exists(DATA_PATH.$icon_path))
			$mb_icon = FALSE;
            
        // 이미지이름
        $named_path = $mb_path.'n_'.$member['mb_id'].'.gif';
        $mb_named = DATA_DIR.$named_path;
		if (!file_exists(DATA_PATH.$named_path))
			$mb_named = FALSE;
        	
        $cf_icon_width = $cf_icon_height = $cf_icon_size = FALSE;
        $cf_named_width = $cf_named_height = $cf_named_size = FALSE;
        if ($this->config->item('cf_use_icon')) {
            if ($member['mb_level'] >= $this->config->item('cf_icon_level')) {
    			$cf_icon_width 	= $this->config->item('cf_icon_width');
    			$cf_icon_height = $this->config->item('cf_icon_height');
    			$cf_icon_size 	= $this->config->item('cf_icon_size');
    		}
            
            if ($member['mb_level'] >= $this->config->item('cf_named_level')) {
    			$cf_named_width	 = $this->config->item('cf_named_width');
    			$cf_named_height = $this->config->item('cf_named_height');
    			$cf_named_size	 = $this->config->item('cf_named_size');
    		}
        }
        
        $mb_zip = explode('-', $member['mb_zip']);
        $member['mb_zip1'] = $mb_zip[0];
        $member['mb_zip2'] = $mb_zip[1];

		$head = array('title' => '회원 정보 수정');
		$data = array_merge(array(
			'open_chk' => $member['mb_open'],
			'mail_chk' => $member['mb_mailling'],
            
            'mb_icon'  => $mb_icon,
            'mb_named' => $mb_named,
			'cf_icon_width'  => $cf_icon_width,
			'cf_icon_height' => $cf_icon_height,
			'cf_icon_size'   => $cf_icon_size,
            'cf_named_width'  => $cf_named_width,
			'cf_named_height' => $cf_named_height,
			'cf_named_size'   => $cf_named_size,

            'nick_modify' => $nick_modify,
			'open_modify' => $open_modify,
			'cf_use_nick'	 => $this->config->item('cf_use_nick'),
			'cf_nick_modify' => $this->config->item('cf_nick_modify'),
			'cf_open_modify' => $this->config->item('cf_open_modify'),

			'token' => $token
		), $member);

		widget::run('head', $head);
		$this->load->view('member/modify', $data);
		widget::run('tail');
	}

	function update() {
		check_token('member/confirm/qry/member.modify');
		check_wrkey();

		$member = unserialize(MEMBER);

		$this->load->helper('chkstr');
		$config = array(
			array('field'=>'mb_password_q', 'label'=>'비밀번호 분실시 질문', 'rules'=>'trim|required|max_length[50]'),
			array('field'=>'mb_password_a', 'label'=>'비밀번호 분실시 답변', 'rules'=>'trim|required|max_length[50]'),
			array('field'=>'mb_email', 'label'=>'이메일', 'rules'=>'trim|required|max_length[50]|valid_email|callback_mb_email_check'),
            array('field'=>'mb_birth', 'label'=>'생일', 'rules'=>'trim|exact_length[10]'),
			array('field'=>'mb_sex', 'label'=>'성별', 'rules'=>'trim|exact_length[1]'),
			array('field'=>'wr_key', 'label'=>'자동등록방지', 'rules'=>'trim|required')
		);
		if ($this->config->item('cf_use_nick'))
			$config[] = array('field'=>'mb_nick', 'label'=>'별명', 'rules'=>'trim|required|max_length[20]|callback_mb_nick_check');

		$this->form_validation->set_rules($config);
		if ($this->form_validation->run() == FALSE) {
			$this->_form($member);
		}
		else {
			if (!IS_MEMBER)
				alert("로그인 되어 있지 않습니다.");
				
			$mb_id = $this->input->post('mb_id');

			if ($member['mb_id'] != $mb_id)
				alert("로그인된 정보와 수정하려는 정보가 틀리므로 수정할 수 없습니다.\\n\\n만약 올바르지 않은 방법을 사용하신다면 바로 중지하여 주십시오.");
            
			$mb_dir   = DATA_PATH.'/member/'.substr($mb_id,0,2);
			$mb_icon  = $mb_dir.'/'.$mb_id.'.gif';
            $mb_named = $mb_dir.'/n_'.$mb_id.'.gif';

			// 아이콘 삭제
			if ($this->input->post('del_mb_icon'))
				@unlink($mb_icon);
                
            // 이미지이름 삭제
			if ($this->input->post('del_mb_named'))
				@unlink($mb_named);

            $this->load->library('upload');
			if (is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
				@mkdir($mb_dir, 0707);
				@chmod($mb_dir, 0707);

				// 확장자가 대문자
				$_FILES['mb_icon']['name'] = str_replace('.GIF', '.gif', $_FILES['mb_icon']['name']);

				$config['upload_path']   = $mb_dir;
				$config['allowed_types'] = 'gif';
				$config['max_size']		 = $this->config->item('cf_icon_size');
				$config['max_width']	 = $this->config->item('cf_icon_width');
				$config['max_height']	 = $this->config->item('cf_icon_height');
				$config['overwrite']	 = TRUE;
				$config['file_name']	 = $mb_id.'.gif';

                $this->upload->initialize($config);
				if ($this->upload->do_upload('mb_icon'))
					chmod($mb_icon, 0606);
			}
			if (is_uploaded_file($_FILES['mb_named']['tmp_name'])) {
				@mkdir($mb_dir, 0707);
				@chmod($mb_dir, 0707);

				$_FILES['mb_named']['name'] = str_replace('.GIF', '.gif', $_FILES['mb_named']['name']);

				$config['upload_path']   = $mb_dir;
				$config['allowed_types'] = 'gif';
				$config['max_size']		 = $this->config->item('cf_named_size');
				$config['max_width']	 = $this->config->item('cf_named_width');
				$config['max_height']	 = $this->config->item('cf_named_height');
				$config['overwrite']	 = TRUE;
				$config['file_name']	 = 'n_'.$mb_id.'.gif';

                $this->upload->initialize($config);
				if ($this->upload->do_upload('mb_named'))
					chmod($mb_named, 0606);
			}
            
			$this->Member_infor_model->update();

			// 인증메일 발송
			if ($this->input->post('old_email') != $this->input->post('mb_email') && $this->config->item('cf_use_email_certify')) {

				$mb_md5 = md5($mb_id.$this->input->post('mb_email').$member['mb_datetime']);
				$certify_href = $this->config->item('base_url').'/member/certify/email/'.$mb_id.'/'.$mb_md5;

				$data = array(
					'mb_name' => $this->input->post('mb_name'),
					'certify_href' => $certify_href
				);
				$content = $this->load->view('mail/join_certify', $data, TRUE);

				$admin = $this->Basic_model->get_member(ADMIN, 'mb_nick, mb_email');

				$this->load->library('email');

				$this->email->clear();
				$this->email->from($admin['mb_email'], $admin['mb_nick']);
				$this->email->to($this->input->post('mb_email'));
				$this->email->subject('인증확인 메일입니다.');
				$this->email->message($content);
				$this->email->send();
			}

			if ($this->input->post('old_email') != $this->input->post('mb_email') && $this->config->item('cf_use_email_certify')) {
				$this->session->unset_userdata('ss_mb_id');
				alert("회원 정보가 수정 되었습니다.\\n\\nE-mail 주소가 변경되었으므로 다시 인증하셔야 합니다.", "/");
			}
			else {
				echo "<html>
						<head>
							<title>회원정보수정</title>
							<meta http-equiv=\"content-type\" content=\"text/html; charset=".$this->config->item('charset')."\">
						</head>
						<body>
							<form name='fupdate' method='post' action='".RT_PATH."/member/modify'>
								<input type='hidden' name='mb_id' value='".$mb_id."'>
								<input type='hidden' name='mb_password' value='".$this->session->userdata('ss_tmp_password')."'>
								<input type='hidden' name='token' value='".get_token()."'>
							</form>
							<script language='JavaScript'>
								alert('회원 정보가 수정 되었습니다.');
								document.fupdate.submit();
							</script>
						</body>
					</html>";
			}
		}
	}

	function mb_nick_check($str) {
		if (!check_string($str, _RT_HANGUL_ + _RT_ALPHABETIC_ + _RT_NUMERIC_)) {
			$this->form_validation->set_message('mb_nick_check', '별명은 공백없이 한글, 영문, 숫자만 입력 가능합니다.');
			return FALSE;
		}

		if (preg_match("/[\,]?".$str."/i", $this->config->item('cf_prohibit_id'))) {
			$this->form_validation->set_message('mb_nick_check', $str.' 은(는) 예약어로 사용하실 수 없는 별명입니다.');
			return FALSE;
		}

		if ($this->input->post('mb_nick_default') != $this->input->post('mb_nick')) {
			$row = $this->Register_model->is('mb_nick', $str);
			if ($row != 0) {
				$this->form_validation->set_message('mb_nick_check', $str.' 은(는) 이미 다른분이 사용중인 별명이므로 사용이 불가합니다.');
				return FALSE;
			}
		}
		return TRUE;
	}

	function mb_email_check($str) {
		if ($this->input->post('old_email') != $this->input->post('mb_email')) {
			$row = $this->Register_model->is('mb_email', $str);
			if ($row != 0) {
				$this->form_validation->set_message('mb_email_check', $str.' 은(는) 이미 다른분이 사용중인 E-mail이므로 사용이 불가합니다.');
				return FALSE;
			}
		}
		return TRUE;
	}

	function password() {
		if (!IS_MEMBER)
			alert('로그인 후 이용하여 주십시오.');

		if (SU_ADMIN)
			alert('관리자 아이디는 접근 불가합니다.');
			
		$member = unserialize(MEMBER);
		if ($this->encrypt->decode($member['mb_password']) != $this->session->userdata('ss_tmp_password'))
			goto_url('/');

		$config = array(
			array('field'=>'mb_id', 'label'=>'아이디', 'rules'=>'trim|required|xss_clean'),
			array('field'=>'old_password', 'label'=>'현재 비밀번호', 'rules'=>'trim|required|min_length[3]|md5'),
			array('field'=>'new_password', 'label'=>'새 비밀번호', 'rules'=>'trim|required|min_length[3]|md5'),
			array('field'=>'new_password_re', 'label'=>'새 비밀번호 확인', 'rules'=>'trim|required|min_length[3]|matches[new_password]|md5'),
			array('field'=>'wr_key', 'label'=>'자동등록방지', 'rules'=>'trim|required')
		);

		$this->form_validation->set_rules($config);
		if ($this->form_validation->run() == FALSE) {
			$head = array('title' => '비밀번호 변경');
			$data = array(
				'mb_id' => $member['mb_id']
			);

			widget::run('head', $head);
			$this->load->view('member/modify_password', $data);
			widget::run('tail');
		}
		else {
			check_wrkey();

			if ($member['mb_id'] != $this->input->post('mb_id'))
				alert("로그인된 회원과 넘어온 정보가 서로 다릅니다.");

			if (!($this->encrypt->decode($member['mb_password']) == $this->input->post('old_password') && $this->input->post('old_password')))
				alert("현재 비밀번호가 맞지 않습니다.");

			$this->Member_infor_model->update_pwd();

			$this->session->unset_userdata('ss_mb_id');
			alert('비밀번호가 변경 되었으므로 다시 로그인하여 주시기 바랍니다.', '/');
		}
	}
}
?>