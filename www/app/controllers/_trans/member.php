<?php
class Member extends CI_Controller {
	function __construct() {
		parent::__construct();
	}

	function memo_delete() {
		$me_no = $this->input->post('me_no');		
		$flag = $this->input->post('flag');
		
		check_token('member/memo/lists/'.$flag);
		if (!IS_MEMBER)
			 alert_close("회원만 이용하실 수 있습니다.");

		if (!($flag && $me_no))
			alert_close("잘못된 접근입니다.");

		$member = unserialize(MEMBER);
		$this->load->model('Member_memo_model');

		if ($flag == 'R') {
			$result = $this->Member_memo_model->get_del_memo($me_no, $flag, $member['mb_id']);
			
			$cnt = 0;
			foreach ($result as $row) {
				if ($row['me_check'] == '0000-00-00 00:00:00')
					$cnt++;					
			}
			if ($cnt > 0)
				$this->Member_memo_model->memo_count($member['mb_id'], $cnt);
		}
		$this->Member_memo_model->memo_delete($me_no, $flag, $member['mb_id']);

		goto_url('member/memo/lists/'.$flag);
	}
}
?>