<?php
class Basic_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	// 회원 정보
	function get_member($mb_id, $fields='*') {
		if (!$mb_id) return FALSE;
		
		$this->db->select($fields);
		return $this->db->get_where('ki_member', array('mb_id' => $mb_id))->row_array();
	}

	// 게시판 정보
	function get_board($bo_table, $fields='*', $gr_join='') {
        if (!$bo_table) return FALSE;
        
		$gr_field = '';
		if ($gr_join) {
			$gr_join = 'a';
			$this->db->join('ki_board_group b', 'a.gr_id = b.gr_id');
			$gr_field = ', b.gr_id, b.gr_subject, b.gr_admin ';
		}

		$this->db->select($fields.$gr_field);
		return $this->db->get_where('ki_board '.$gr_join, array('bo_table' => $bo_table))->row_array();
	}
	
	// 게시글 정보
	function get_write($bo_table, $wr_ids, $fields) {
		if (!$wr_ids) return FALSE;

		$this->db->select($fields);
		$this->db->where('bo_table', $bo_table);
		if (is_array($wr_ids)) {
			$this->db->where_in('wr_id', $wr_ids);
			return $this->db->get('ki_write')->result_array();
		}
		else {
			return $this->db->get_where('ki_write', array(
				'wr_id' => $wr_ids
			))->row_array();
		}
	}
}
?>