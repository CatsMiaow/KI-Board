<?php
class Make extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('encrypt');
	}

	// 초기 관리자 생성
    function index() {
		if ($this->db->count_all_results('ki_member') > 0)
			alert('이미 회원이 존재합니다.');

		$this->db->insert('ki_member', array(
			'mb_id'			   => 'admin',
			'mb_password'	   => $this->encrypt->encode(md5('password')),
			'mb_name'		   => '관리자',
			'mb_nick'		   => '관리자',
			'mb_nick_date'	   => TIME_YMD,
			'mb_email'		   => 'admin@test.com',
			'mb_homepage'	   => '',
			'mb_password_q'	   => '',
			'mb_password_a'    => '',
			'mb_level'		   => 10,
			'mb_sex'		   => 'M',
			'mb_birth'		   => TIME_YMD,
			'mb_tel'		   => '',
			'mb_hp'			   => '',
			'mb_zip'		   => '-',
			'mb_addr1'		   => '',
			'mb_addr2'		   => '',
			'mb_point'		   => 0,
			'mb_today_login'   => TIME_YMDHIS,
			'mb_login_ip'	   => $this->input->server('REMOTE_ADDR'),
			'mb_datetime'	   => TIME_YMDHIS,
			'mb_ip'			   => $this->input->server('REMOTE_ADDR'),
			'mb_leave_date'    => '',
			'mb_email_certify' => TIME_YMDHIS,			
			'mb_mailling'	   => '0',
			'mb_open'		   => '0',
			'mb_open_date'	   => TIME_YMD,
			'mb_profile'	   => '',
			'mb_memo_call'	   => '',
			'mb_memo_cnt'	   => 0
		));

		goto_url('/');
	}

	// 2.2.0에서 Mcrypt를 사용하지 않는 암호 재가공
	function password() {
		$this->db->select('mb_id, mb_password');
		$result = $this->db->get_where('ki_member', array(
			'mb_level >=' => 2
		))->result_array();

		$key = md5($this->config->item('encryption_key'));

		$data = array();
		foreach ($result as $row) {
			$password = $this->encrypt->_xor_decode(base64_decode($row['mb_password']), $key);
			if (strlen($password) != 32) exit('구 버전 암호가 아닐 수 있습니다.');

			$data[] = array(
				'mb_id' => $row['mb_id'],
				'mb_password' => $this->encrypt->encode($password)
			);
		}

		$this->db->update_batch('ki_member', $data, 'mb_id');

		goto_url('/');
	}
}