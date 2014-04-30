<?php
class Board_password extends CI_Controller {
	function __construct() {
		parent::__construct();
	}	
	
	function check() {
		if ($this->input->post('w') == 's') {
			$bo_table = $this->input->post('bo_table');
			
		    $wr = $this->Basic_model->get_write($bo_table, $this->input->post('wr_id'), 'wr_num, wr_password');
		
			$this->load->library('encrypt');
        	if (md5($this->input->post('password')) != $this->encrypt->decode($wr['wr_password']))
		        alert("비밀번호가 맞지 않습니다.");
		
		    // 세션에 아래 정보를 저장. 하위번호는 패스워드없이 보아야 하기 때문
		    $ss_name = "ss_secret_".$bo_table."_".$wr['wr_num'];		
			$this->session->set_userdata($ss_name, TRUE);
		}
		else
		    alert("잘못된 접근입니다.");
		
		goto_url('board/'.$bo_table.'/view'.$this->input->post('qstr'));
	}
}