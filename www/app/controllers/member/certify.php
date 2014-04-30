<?php
class Certify extends CI_Controller {
	function __construct() {
		parent::__construct();
	}

	function email($mb_id, $mb_md5) {
		$row = $this->Basic_model->get_member($mb_id, 'mb_id, mb_email, mb_datetime');
		if (!isset($row['mb_id']))
			alert('존재하는 회원이 아닙니다.', '/');

		if ($mb_md5) {
			$tmp_md5 = md5($row['mb_id'].$row['mb_email'].$row['mb_datetime']);
			if ($mb_md5 == $tmp_md5) {
				$this->load->model('Register_model');
				$this->Register_model->email($row['mb_id']);

				alert('이메일 인증 처리를 완료 하였습니다.', '/');
			}
		}

		alert('제대로 된 값이 넘어오지 않았습니다.', '/');
	}
}
?>