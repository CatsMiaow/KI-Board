<?php
class Phpinfo extends CI_Controller {
	function __construct() {
		parent::__construct();
	}

	function index() {
		if (SU_ADMIN != ADMIN) {
			alert_close('최고관리자만 접근할 수 있습니다.');
			return false;
		}

		phpinfo();
	}
}
?>