<?php
class Category extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Categoryform_model');
	}

	function update() {
		if (!IS_MEMBER || !$this->input->post('type'))
			return FALSE;

		// 게시판 관리자 검증 필요
		// if (!IS_ADMIN) { ... }

		$w = $this->input->post('w');
		$ca_code = str_replace('-', '.', $this->input->post('ca_code'));
		if ($this->input->post('ca_name'))
			$ca_name = $this->input->post('ca_name');

		if ($w == '')
			$this->Categoryform_model->insert($ca_code, $ca_name);
		else if ($w == 'u')
			$this->Categoryform_model->update($ca_code, $ca_name);
		else if ($w == 'd') {
			$code_exp = explode('.', $ca_code);
			if (!isset($code_exp[1]))
				$limit_code = $ca_code + 1;
			else {
				$code_ori = substr($code_exp[1], 0, -3);
				$code_num = substr($code_exp[1], -3) + 1;
				$code_plus = repeater('0', 3-strlen($code_num)).$code_num;
				$limit_code = $code_exp[0].'.'.$code_ori.$code_plus;
			}
			$this->Categoryform_model->delete($ca_code, $limit_code);
		}
		else
			exit('Access Error');
		
		echo 'TRUE';
	}
}