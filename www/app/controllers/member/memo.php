<?php
class Memo extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Member_memo_model');
		$this->load->helper('textual');
	}

	function _remap($m) {
		if (!IS_MEMBER)
			alert_close('회원만 이용하실 수 있습니다.');

		$seg1 = $this->uri->segment(4);
		$seg2 = $this->uri->segment(5);
		switch ($m) {
			case 'lists' : $this->lists($seg1, $seg2); break;
			case 'view'  : $this->view($seg1, $seg2); break;
			case 'write' : $this->write($seg1, $seg2); break;
			default:
				alert_close('잘못된 접근입니다.');
			break;
		}
	}

	function lists($flag, $page) {
		$this->load->helper('sideview');
		$this->load->library('pagination');
		$member = unserialize(MEMBER);

		if (!$flag) $flag = 'R';
		if (!$page) $page = 1;

		// 설정일이 지난 메모 삭제
		$this->Member_memo_model->cf_delete($member['mb_id']);

		if ($member['mb_memo_call'])
			$this->Member_memo_model->call_delete($member['mb_id']);

		$config['base_url'] = RT_PATH.'/member/memo/lists/'.$flag.'/';
		$total_cnt = $this->Member_memo_model->total_cnt($flag, $member['mb_id']);
		$config['total_rows'] = $total_cnt;
		$config['per_page'] = 10;
		$this->pagination->initialize($config);

		$offset = ($page - 1) * $config['per_page'];
		$result = $this->Member_memo_model->list_result($flag, $member['mb_id'], $config['per_page'], $offset);

		$list = array();
		$token = get_token();
		foreach ($result as $i => $row) {
			$name = ($this->config->item('cf_use_nick') && $row['mb_nick']) ? $row['mb_nick'] : $row['mb_name'];
			
			$check = 'X';
			$check_time = '아직 읽지 않음';
			if (substr($row['me_check'],0,1) != '0') {
				$check = 'O';
				$check_time = substr($row['me_check'], 2, 14);
			}

			$list[$i] = new stdClass();
			$list[$i]->me_no = $row['me_no'];
			$list[$i]->check = $check;
			$list[$i]->check_time = $check_time;
		    $list[$i]->name = get_sideview($row['me_mb_id'], $name);
		    $list[$i]->datetime = substr($row['me_datetime'], 2, 14);
			$list[$i]->content = cut_str(get_text($row['me_content']), 40);
		    $list[$i]->view_href = RT_PATH.'/member/memo/view/'.$flag.'/'.$row['me_no'];
		    $list[$i]->del_href = '_trans/member/memo_delete';
			$list[$i]->del_parm = "{flag:'".$flag."',me_no:'".$row['me_no']."',token:'".$token."'}";
		}

		if ($flag == 'R') {
    		$flag_title = '받은';
			$me_subject = '보낸이';
		}
		else if ($flag == 'S') {
    		$flag_title = '보낸';
			$me_subject = '받는이';
		}

		$head = array('title' => "내 쪽지함");
		$data = array(
			'token' => $token,
			'path' => RT_PATH.'/member',
			'flag_title' => $flag_title,
			'total_cnt' => number_format($total_cnt),
			'memo_del_day' => $this->config->item('cf_memo_del'),
			'me_subject' => $me_subject,
			'list' => $list,
			'flag' => $flag,
			'paging' => $this->pagination->create_links()
		);

		widget::run('head', $head);
		$this->load->view('member/memo_list', $data);
		widget::run('tail');
	}
	
	function view($flag, $me_no) {
		if (!$flag || !$me_no)
			alert_close('잘못된 접근입니다.');

		$member = unserialize(MEMBER);
		
		$memo = $this->Member_memo_model->get_memo($me_no, $flag, $member['mb_id']);
		if (!$memo)
			alert('존재하지 않는 쪽지입니다.');

		if ($flag == 'R') {
			$t = '받은';
			if ($memo['me_check'] == '0000-00-00 00:00:00') {
				$this->Member_memo_model->read_check($me_no);
				$this->Member_memo_model->memo_count($member['mb_id']);
			}
		}
		else if ($flag == 'S')
			$t = '보낸';
		else
			alert_close('잘못된 접근입니다.');

		$prev = $this->Member_memo_model->memo_link($me_no, $flag, $member['mb_id'], 'prev');
		if ($prev && $prev['me_no'])
			$prev_link = RT_PATH.'/member/memo/view/'.$flag.'/'.$prev['me_no'];
		else
			$prev_link = "javascript:alert('쪽지의 처음입니다.');";

		$next = $this->Member_memo_model->memo_link($me_no, $flag, $member['mb_id'], 'next');
		if ($next && $next['me_no'])
			$next_link = RT_PATH.'/member/memo/view/'.$flag.'/'.$next['me_no'];
		else
			$next_link = "javascript:alert('쪽지의 마지막입니다.');";

		$mb = $this->Basic_model->get_member($memo['me_mb_id'], ' mb_id, mb_nick, mb_name ');
		$nick = ($mb['mb_nick']) ? $mb['mb_nick'] : $mb['mb_name'];
		
		$btn_reply = '';
		if ($flag == 'R') {
			$memo_msg = '<b>'.$nick.'</b> 님께서 '.$memo['me_datetime'].' 에 보내온 쪽지의 내용입니다.';
			$btn_reply = "<a href='".RT_PATH."/member/memo/write/".$mb['mb_id']."/".$memo['me_no']."' class='btn btn-info'>답장하기</a>&nbsp;&nbsp;&nbsp;";
		}
		else if ($flag == 'S')
			$memo_msg = '<b>'.$nick.'</b> 님께 '.$memo['me_datetime'].' 에 보낸 쪽지의 내용입니다.';

		$head = array('title' => $t." 쪽지 보기");
		$data = array(
			'path' => RT_PATH.'/member',
			'memo_msg' => $memo_msg,
			'prev_link' => $prev_link,
			'next_link' => $next_link,
			'btn_reply' => $btn_reply,
			'flag' => $flag,
			'content' => conv_content($memo['me_content'], FALSE)
		);

		widget::run('head', $head);
		$this->load->view('member/memo_view', $data);
		widget::run('tail');
	}
	
	function write($recv_mb_id=FALSE, $me_no=FALSE) {
		$member = unserialize(MEMBER);
		
		if (!$member['mb_open'] && !SU_ADMIN && $member['mb_id'] != $recv_mb_id)
			alert_close('자신의 정보를 공개하지 않으면 다른분에게 쪽지를 보낼 수 없습니다.\\n\\n정보공개 설정은 회원정보수정에서 하실 수 있습니다.');

		$this->load->library('form_validation');
		
		$config = array(
			array('field'=>'recv_mb_id', 'label'=>'받는 아이디', 'rules'=>'trim|required|xss_clean'),
			array('field'=>'me_content', 'label'=>'내용', 'rules'=>'trim|required|xss_clean')
		);
		$this->form_validation->set_rules($config);

		if ($this->form_validation->run() == FALSE) {
			$content = FALSE;
			// 탈퇴한 회원에게 쪽지 보낼 수 없음
			if ($recv_mb_id) {
				$mb = $this->Basic_model->get_member($recv_mb_id, 'mb_id, mb_open');
				if (!isset($mb['mb_id']))
					alert_close('회원정보가 존재하지 않습니다.\\n\\n탈퇴하였을 수 있습니다.');

				if (!$mb['mb_open'] && !SU_ADMIN)
					alert_close('정보공개를 하지 않았습니다.');
			}

			$head = array('title' => '쪽지 보내기');
			$data = array(
				'path' => RT_PATH.'/member',
				'recv_mb_id' => $recv_mb_id
			);

			widget::run('head', $head);
			$this->load->view('member/memo_write', $data);
			widget::run('tail');
		}
		else {
			$tmp_list = array_unique(explode(',', $this->input->post('recv_mb_id')));

			$recv_mb_id_list = $msg = $comma1 = $comma2 = FALSE;
			$mb_list = $mb_array = array();

			for ($i=0; $i<count($tmp_list); $i++) {
				$row = $this->Basic_model->get_member($tmp_list[$i], 'mb_id, mb_name, mb_nick, mb_open, mb_leave_date');
				if (!$row || (!$row['mb_id'] || !$row['mb_open'] || $row['mb_leave_date'])) {
					$msg .= $comma1.$tmp_list[$i];
					$comma1 = ',';
				}
				else {
					if ($this->config->item('cf_use_nick'))
						$recv_mb_id_list .= $comma2.$row['mb_nick'];
					else
						$recv_mb_id_list .= $comma2.$row['mb_name'];

					$mb_list[] = $tmp_list[$i];
					$mb_array[] = $row;
					$comma2 = ',';
				}
			}

			if ($msg)
				alert("회원아이디 \'".$msg."\' 은(는) 존재(또는 정보공개)하지 않은 혹은 탈퇴한 회원아이디 입니다.\\n\\n쪽지를 발송하지 않았습니다.");

			for ($i=0; $i<count($mb_list); $i++) {
				if (trim($mb_list[$i]))
					$this->Member_memo_model->insert($member['mb_id'], $mb_list[$i], $this->input->post('me_content'));
			}

			alert("\'".$recv_mb_id_list."\' 님께 쪽지를 전달하였습니다.", 'member/memo/lists/S');
		}
	}
}
?>