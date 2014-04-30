<?php
class Register_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	// 회원 여부
	function is($fld, $str) {
		$this->db->where(array($fld => $str));
		$this->db->from('ki_member');
		return $this->db->count_all_results();
	}

	// 메일 인증
	function email($mb_id) {
		$this->db->where('mb_id', $mb_id);
		$this->db->update('ki_member', array('mb_email_certify' => TIME_YMDHIS));
	}
}