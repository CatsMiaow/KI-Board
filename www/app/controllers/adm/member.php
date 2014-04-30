<?php
class Member extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model(ADM_F.'/Member_model');
		define('WIDGET_SKIN', 'admin');
		define('CSS_SKIN', 'jquery');
	}

	function lists() {
		$this->load->library(array('pagination', 'querystring'));
		$this->load->helper(array('admin', 'sideview'));

 		$param =& $this->querystring;
		$page = $this->uri->segment(5, 1);
		$sst = $param->get('sst', 'mb_datetime');
		$sod = $param->get('sod', 'desc');
		$sfl = $param->get('sfl');
		$stx = $param->get('stx');
		
		$config['suffix'] = $param->output();
		$config['base_url'] = RT_PATH.'/'.ADM_F.'/member/lists/page/';
		$config['per_page'] = 15;

		$offset = ($page - 1) * $config['per_page'];
		$result = $this->Member_model->list_result($sst, $sod, $sfl, $stx, $config['per_page'], $offset);

		$config['total_rows'] = $result['total_cnt'];
		$this->pagination->initialize($config);

		$list = array();
		$token = get_token();
		foreach ($result['qry'] as $i => $row) {
			$list[$i] = new stdClass();
			
			if ($this->config->item('cf_use_nick'))
				$list[$i]->nick = $row['mb_nick'];

			if (!$row['mb_leave_date'])
				$mb_id_s = get_sideview($row['mb_id'], $row['mb_id']);
			else
				$mb_id_s = '<font color="crimson">'.$row['mb_id'].'</font>';

			$list[$i]->id = $row['mb_id'];
			$list[$i]->name = $row['mb_name'];
			$list[$i]->id_s = $mb_id_s;
			$list[$i]->level_select = get_mb_level_select("mb_levels[".$row['mb_id']."]", $row['mb_level'], TRUE);
			$list[$i]->point = number_format($row['mb_point']);
			$list[$i]->today_login = substr($row['mb_today_login'], 2, 8);
			$list[$i]->mailling_chk = $row['mb_mailling'] ? '&radic;' : '&nbsp;';
			$list[$i]->open_chk = $row['mb_open'] ? '&radic;' : '&nbsp;';
			$list[$i]->s_mod = icon('수정', 'member/form/u/'.$row['mb_id']);
			$list[$i]->s_del = icon('삭제', "javascript:post_send('".ADM_F."/_trans/member/delete', {mb_id:'".$row['mb_id']."', token:'".$token."'}, true);");
			$list[$i]->email_certify = $row['mb_email_certify'];
			$list[$i]->mail_certify_chk = preg_match('/[1-9]/', $row['mb_email_certify']) ? '&radic;' : '&nbsp;';
		}

		$head = array('title' => '회원관리');
		$data = array(
			'token' => $token,

			'list' => $list,
			's_add' => icon('작성', 'member/form'),
			'use_nick' => $this->config->item('cf_use_nick'),

			'sfl' => $sfl,
			'stx' => $stx,

			'total_cnt' => number_format($result['total_cnt']),
			'leave_cnt' => number_format($result['leave_cnt']),
			'paging' => $this->pagination->create_links(),

			'sort_mb_id' => $param->sort('mb_id'),
			'sort_mb_name' => $param->sort('mb_name'),
			'sort_mb_nick' => $param->sort('mb_nick'),
			'sort_mb_level' => $param->sort('mb_level', 'desc'),
			'sort_mb_point' => $param->sort('mb_point', 'desc'),
			'sort_mb_today_login' => $param->sort('mb_today_login', 'desc'),
			'sort_mb_mailling' => $param->sort('mb_mailling', 'desc'),
			'sort_mb_open' => $param->sort('mb_open', 'desc'),
			'sort_mb_email_certify' => $param->sort('mb_email_certify', 'desc')
		);

		widget::run('head', $head);
		$this->load->view(ADM_F.'/member_list', $data);
		widget::run('tail');
	}
	
	function form($w='', $mb_id='') {
		$this->load->config('cf_register');
		$this->load->config('cf_icon');
		$this->load->model('Register_model');
		$this->load->library('form_validation');
		$this->load->helper(array('admin', 'chkstr'));

		$config = array(
			array('field'=>'mb_name', 'label'=>'이름', 'rules'=>'trim|required|max_length[10]'),
			array('field'=>'mb_email', 'label'=>'이메일', 'rules'=>'trim|required|max_length[50]|valid_email|callback_mb_email_check'),
			array('field'=>'mb_sex', 'label'=>'성별', 'rules'=>'trim|exact_length[1]'),
			array('field'=>'mb_birth', 'label'=>'생일', 'rules'=>'trim|exact_length[10]')
		);

		$pwd_req = ''; 
		if (!$this->input->post('w')) {
			$config[] = array('field'=>'mb_id', 'label'=>'아이디', 'rules'=>'trim|required|min_length[3]|max_length[20]|alpha_dash|xss_clean|callback_mb_id_check');
			$pwd_req = 'required|';
		}
		
		$config[] = array('field'=>'mb_password', 'label'=>'비밀번호', 'rules'=>'trim|'.$pwd_req.'min_length[3]|max_length[20]|md5');

		if ($this->config->item('cf_use_nick'))
			$config[] = array('field'=>'mb_nick', 'label'=>'별명', 'rules'=>'trim|required|max_length[20]|callback_mb_nick_check');

		$this->form_validation->set_rules($config);
		if ($this->form_validation->run() == FALSE) {
			$data = array();

			if ($w == '') {
				$mb = array_false(unserialize(MEMBER), TRUE);

                $mb['mb_zip1'] = $mb['mb_zip2'] = '';
				$mb['mb_mailling'] = 1;
				$mb['mb_open'] = 1;
				$mb['mb_level'] = $this->config->item('cf_register_level');

				$title = '등록';
			}
			else if ($w == 'u') {
				$mb = $this->Basic_model->get_member($mb_id);
				if (!isset($mb['mb_id']))
					alert('존재하지 않는 회원자료입니다.');

				list($mb['mb_zip1'], $mb['mb_zip2']) = explode('-', $mb['mb_zip']);

				if ($this->config->item('cf_use_point'))
					$mb['mb_point'] = number_format($mb['mb_point']);

				if ($this->config->item('cf_use_email_certify')) {
					$data['passive_certify'] = FALSE;
					if ($mb['mb_email_certify'] == '0000-00-00 00:00:00')
						$data['passive_certify'] = "<input type='checkbox' name='passive_certify'> 수동인증";
				}
				$data['use_email_certify'] = $this->config->item('cf_use_email_certify');

				$title = '수정';
			}
			else
				alert('잘못된 접근입니다.');

            if ($this->config->item('cf_use_icon')) {
                $mb_path = '/member/'.substr($mb['mb_id'],0,2).'/';
                
				$icon_path = $mb_path.$mb['mb_id'].'.gif';
				$icon_file = DATA_DIR.$icon_path;
				if (!file_exists(DATA_PATH.$icon_path))
					$icon_file = FALSE;
                    
                $data['icon_file'] = $icon_file;
				$data['icon_width'] = $this->config->item('cf_icon_width');
				$data['icon_height'] = $this->config->item('cf_icon_height');
				$data['icon_size'] = $this->config->item('cf_icon_size');
                    
                $named_path = $mb_path.'n_'.$mb['mb_id'].'.gif';
				$named_file = DATA_DIR.$named_path;
				if (!file_exists(DATA_PATH.$named_path))
					$named_file = FALSE;
                    
                $data['named_file'] = $named_file;
				$data['named_width'] = $this->config->item('cf_named_width');
				$data['named_height'] = $this->config->item('cf_named_height');
				$data['named_size'] = $this->config->item('cf_named_size');
			}
			
			$head = array('title' => '회원관리 '.$title);
			$data = array_merge(array(
				'w' => $w,
				'token' => get_token(),
				'cf_use_nick' => $this->config->item('cf_use_nick'),
				'cf_use_icon' => ($w) ? $this->config->item('cf_use_icon') : FALSE,
				
				'mailling_chk' => ($mb['mb_mailling']) ? "checked='checked'" : FALSE,
				'open_chk' => ($mb['mb_open']) ? "checked='checked'" : FALSE,

				'mb_level_select' => get_mb_level_select('mb_level', $mb['mb_level'])
			), $data, $mb);

			widget::run('head', $head);
			$this->load->view(ADM_F.'/member_form', $data);
			widget::run('tail');
		}
		else {
			check_token();

			$w = $this->input->post('w');
			$mb_id = $this->input->post('mb_id');

			if ($mb_id == ADMIN) {
				$member = unserialize(MEMBER);
				if ($member['mb_id'] != $mb_id)
					alert('최고관리자는 수정할 수 없습니다.');
			}

			if (!$w) {
				$mb = $this->Basic_model->get_member($mb_id, 'mb_id,mb_name,mb_nick,mb_email');
				if (isset($mb['mb_id']))
					alert("이미 존재하는 회원입니다.\\n\\nＩＤ : ".$mb['mb_id']."\\n\\n이름 : ".$mb['mb_name']."\\n\\n별명 : ".$mb['mb_nick']."\\n\\n메일 : ".$mb['mb_email']);

				$this->Member_model->insert();
			}
			else if ($w == 'u') {
				$mb = $this->Basic_model->get_member($mb_id, 'mb_id');
				if (!isset($mb['mb_id']))
					alert('존재하지 않는 회원자료입니다.');

                $mb_dir   = DATA_PATH.'/member/'.substr($mb_id,0,2);
    			$mb_icon  = $mb_dir.'/'.$mb_id.'.gif';
                $mb_named = $mb_dir.'/n_'.$mb_id.'.gif';

                // 아이콘 삭제
    			if ($this->input->post('del_mb_icon'))
    				@unlink($mb_icon);
                    
                // 이미지이름 삭제
    			if ($this->input->post('del_mb_named'))
    				@unlink($mb_named);
				
				if ($_FILES) {
					$this->load->library('upload');
					if (is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
						@mkdir($mb_dir, 0707);
						@chmod($mb_dir, 0707);

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
				}

				$this->Member_model->update();
			}
			else
				alert('잘못된 접근입니다.');

			goto_url(ADM_F.'/member/form/u/'.$mb_id);
		}
	}

	function mb_id_check($str) {
		if (preg_match("/[\,]?".$str."/i", $this->config->item('cf_prohibit_id'))) {
			$this->form_validation->set_message('mb_id_check', $str.' 은(는) 예약어로 사용하실 수 없는 회원아이디입니다.');
			return FALSE;
		}

		$row = $this->Register_model->is('mb_id', $str);
		if ($row != 0) {
			$this->form_validation->set_message('mb_id_check', $str.' 은(는) 이미 다른분이 사용중인 회원아이디이므로 사용이 불가합니다.');
			return FALSE;
		}
		return TRUE;
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

		if (!$this->input->post('w') || $this->input->post('mb_nick_default') != $this->input->post('mb_nick')) {
			$row = $this->Register_model->is('mb_nick', $str);
			if ($row != 0) {
				$this->form_validation->set_message('mb_nick_check', $str.' 은(는) 이미 다른분이 사용중인 별명이므로 사용이 불가합니다.');
				return FALSE;
			}
		}
		return TRUE;
	}

	function mb_email_check($str) {
		if (!$this->input->post('w') || $this->input->post('old_email') != $this->input->post('mb_email')) {
			$row = $this->Register_model->is('mb_email', $str);
			if ($row != 0) {
				$this->form_validation->set_message('mb_email_check', $str.' 은(는) 이미 다른분이 사용중인 E-mail이므로 사용이 불가합니다.');
				return FALSE;
			}
		}
		return TRUE;
	}
}
?>