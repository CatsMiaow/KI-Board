<?php
class Member_point_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	function total_cnt($mb_id) {
		$this->db->where(array('mb_id' => $mb_id));
		$this->db->from('ki_point');
		return $this->db->count_all_results();
	}
	
	function list_result($mb_id, $limit, $offset) {
		$this->db->select('po_point, po_datetime, po_content');
		$this->db->order_by('po_id', 'desc'); 
		$qry = $this->db->get_where('ki_point', array('mb_id' => $mb_id), $limit, $offset);
		return $qry->result_array();
	}
}
?>