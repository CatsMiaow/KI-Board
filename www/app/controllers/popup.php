<?php
class Popup extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Popup_model');
	}

	function _remap($pu_id) {
		$pu = $this->Popup_model->get($pu_id,'pu_id, pu_name, pu_file');
		if (!isset($pu['pu_id']))
			alert_close('등록된 팝업이 아닙니다.');
		
		if (SU_ADMIN && !file_exists(SKIN_PATH.'popup/'.$pu['pu_file'].'.html'))
			alert_close('팝업 파일이 없습니다.');

		$head = array('title' => $pu['pu_name']);
		$data = array('id' => 'popup'.$pu_id);

		widget::run('head', $head);
		$this->load->view('popup/'.$pu['pu_file'], $data);
		widget::run('tail');
	}
}
?>