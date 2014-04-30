<?php
class Make extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('encrypt');
	}

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
}
?>