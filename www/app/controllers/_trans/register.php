<?php
class Register extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Register_model');
		$this->config->load('cf_register');
	}

	function id() {
		$reg_mb_id = $this->input->post('reg_mb_id');
		$TRUE = $FALSE = FALSE;

		if (preg_match("/[^0-9a-z_]+/i", $reg_mb_id))
			$FALSE = '영문자, 숫자, _ 만 입력하세요.';
		else if (strlen($reg_mb_id) < 3)
			$FALSE = '최소 3자이상 입력하세요.';
		else {
			$row = $this->Register_model->is('mb_id', $reg_mb_id);
			if ($row != 0)
				$FALSE = '이미 사용중인 아이디 입니다.';
			else {
				if (preg_match("/[\,]?".$reg_mb_id."/i", $this->config->item('cf_prohibit_id')))
					$FALSE = '예약어로 사용할 수 없는 아이디 입니다.';
				else
					$TRUE = '사용하셔도 좋은 아이디 입니다.';
			}
		}

		if ($TRUE)
			echo '<span class="text-success">'.$TRUE.'</span>';
		else if ($FALSE)
			echo '<span class="text-danger">'.$FALSE.'</span>';
	}

	function nick() {
		$reg_mb_nick = $this->input->post('reg_mb_nick');
		$TRUE = $FALSE = FALSE;

		// 별명은 한글, 영문, 숫자만 가능
		$this->load->helper('chkstr');
		if (!check_string($reg_mb_nick, _RT_HANGUL_ + _RT_ALPHABETIC_ + _RT_NUMERIC_))
			$FALSE = '별명은 공백없이 한글, 영문, 숫자만 입력 가능합니다.';
		else if (strlen($reg_mb_nick) < 4)
			$FALSE = '한글 2글자, 영문 4글자 이상 입력 가능합니다.';
		else {
			$row = $this->Register_model->is('mb_nick', $reg_mb_nick);
			if ($row != 0)
				$FALSE = '이미 존재하는 별명입니다.';
			else {
				if (preg_match("/[\,]?".$reg_mb_nick."/i", $this->config->item('cf_prohibit_id')))
					$FALSE = '예약어로 사용할 수 없는 별명 입니다.';
				else
					$TRUE = '사용하셔도 좋은 별명 입니다.';
			}
		}

		if ($TRUE)
			echo '<span class="text-success">'.$TRUE.'</span>';
		else if ($FALSE)
			echo '<span class="text-danger">'.$FALSE.'</span>';
	}

	function email() {
		$reg_mb_email = $this->input->post('reg_mb_email');
		$TRUE = $FALSE = FALSE;

		if (trim($reg_mb_email) == '')
			$FALSE = '이메일 주소를 입력하세요.';
		else if (!preg_match("/^[0-9a-zA-Z_-]+@[a-zA-Z]+(\.[a-zA-Z]+){1,2}$/", $reg_mb_email))
			$FALSE = '이메일 주소가 형식에 맞지 않습니다.';
		else {
			$row = $this->Register_model->is('mb_email', $reg_mb_email);
			if ($row != 0)
				$FALSE = '이미 존재하는 이메일 주소입니다.';
			else
				$TRUE = '사용하셔도 좋은 이메일 주소입니다.';
		}

		if ($TRUE)
			echo "<span class='text-success'>".$TRUE.'</span>';
		else if ($FALSE)
			echo '<span class="text-danger">'.$FALSE.'</span>';
	}
}
?>