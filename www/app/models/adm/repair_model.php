<?php
class Repair_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	// 설정일이 지난 인기검색어 삭제
	function delete_popular() {
		$this->db->delete('ki_popular', array(
			'pp_date <' => date("Y-m-d", time() - (86400 * $this->config->item('cf_popular_del')))
		));
	}

	// 설정일이 지난 쪽지 삭제
	function delete_memo() {
		$this->db->delete('ki_memo', array(
			'me_datetime <' => date("Y-m-d H:i:s", time() - (86400 * $this->config->item('cf_memo_del'))),
			'me_check !=' => '0000-00-00 00:00:00'
		));
	}
}
?>