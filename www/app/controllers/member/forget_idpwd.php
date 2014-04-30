<?php
class Forget_idpwd extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->config->load('cf_register');
		$this->load->model('Member_forget_model');
		define('WIDGET_SKIN', 'main');
	}

	function index() {
		if (IS_MEMBER)
			alert('이미 로그인 중입니다.');

		$head = array('title' => '회원아이디 / 비밀번호 찾기');
		$data = array();

		widget::run('head', $head);
		$this->load->view('member/forget_idpwd', $data);
		widget::run('tail');
	}

	function step2() {
		if (!$this->input->post('w'))
			goto_url('/');
				
		$not_mb_id = $this->input->post('not_mb_id');

		if ($not_mb_id)
			$title = '회원아이디 찾기 결과';
		else if (!$not_mb_id || $this->session->flashdata('mb_idpwd'))
			$title = '비밀번호 찾기 2단계';

		$mb = $this->Member_forget_model->check();
		if (isset($mb['mb_id'])) {
			if ($mb['mb_id'] == ADMIN)
		    	alert('관리자 아이디는 접근 불가합니다.');
		}
		else
		    alert('입력하신 내용으로는 회원정보가 존재하지 않습니다.', 'member/forget_idpwd');

		$this->load->helper('textual');
		$mb['mb_password_q'] = get_text($mb['mb_password_q']);

		$head = array('title' => $title);
		$data = array(
			'time' => time(),
			'mb_id' => $mb['mb_id'],
			'mb_password_q' => $mb['mb_password_q']
		);
		widget::run('head', $head);
		$this->load->view('member/forget_'.(($not_mb_id) ? 'id' : 'pwd'), $data);
		widget::run('tail');
	}

	function step3() {
		check_wrkey();

		$this->session->set_flashdata('mb_idpwd', $this->input->post('mb_id'));

		$mb = $this->Basic_model->get_member($this->input->post('mb_id'), 'mb_id, mb_password_a');
		if (!isset($mb['mb_id']))
		    alert('존재하지 않는 회원입니다.', '/');
		else if ($mb['mb_id'] == ADMIN)
		    alert('관리자 아이디는 접근 불가합니다.', '/');
		else if ($this->input->post('mb_password_a') !== $mb['mb_password_a'])
		    alert('비밀번호 분실시 답변이 틀립니다.');

		// 난수 발생
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float)$sec + ((float)$usec * 100000);
		srand($seed);
		$change_pwd = substr(md5($seed), 0, rand(4, 6));

		$this->Member_forget_model->new_pwd($change_pwd);

		$head = array('title' => '비밀번호 찾기 결과');
		$data = array(
			'mb_id' => $mb['mb_id'],
			'change_pwd' => $change_pwd
		);

		widget::run('head', $head);
		$this->load->view('member/forget_pwd2', $data);
		widget::run('tail');
	}
}
?>