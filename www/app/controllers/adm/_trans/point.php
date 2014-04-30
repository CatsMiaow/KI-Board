<?php
class Point extends CI_Controller {
	function __construct() {
		parent::__construct();
		check_token(ADM_F.'/point/lists');
		$this->load->model(ADM_F.'/Point_model');
	}

	function delete() {
		if ($this->input->post('chk')) {
			$po_ids = $this->input->post('chk');
			$mb_ids = array_unique($this->input->post('mb_ids'));
		}
		else
			alert('잘못된 접근입니다.');

		$this->Point_model->point_delete($po_ids);					

		foreach ($mb_ids as $mb_id) {
			$this->Point_model->point_reset($mb_id);
		}
		
		goto_url(URL);
	}
}
?>