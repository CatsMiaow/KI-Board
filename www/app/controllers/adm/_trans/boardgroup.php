<?php
class Boardgroup extends CI_Controller {
	function __construct() {
		parent::__construct();
		check_token(ADM_F.'/boardgroup/lists');
		$this->load->model(ADM_F.'/Boardgroup_model');
	}

	function delete() {
		if (!$this->input->post('gr_id'))
			alert("잘못된 접근입니다.");

		if (!$this->Boardgroup_model->delete())
		    alert('이 그룹에 속한 게시판이 존재하여 게시판 그룹을 삭제할 수 없습니다.\\n\\n이 그룹에 속한 게시판을 먼저 삭제하여 주십시오.', URL);

		goto_url(URL);
	}

	function update() {
		if ($this->input->post('chk')) {
			$gr_ids = $this->input->post('chk');
			$gr_subjects = $this->input->post('gr_subject');
			$gr_admins = $this->input->post('gr_admin');
		}
		else
			alert("잘못된 접근입니다.");

		foreach ($gr_ids as $gr_id) {
			$this->Boardgroup_model->list_update($gr_id, $gr_subjects[$gr_id], $gr_admins[$gr_id]);
		}
		
		goto_url(URL);
	}
}
?>