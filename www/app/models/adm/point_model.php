<?php
class Point_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	function list_result($sst, $sod, $sfl, $stx, $limit, $offset) {
		$this->db->start_cache();
		if ($stx) {
			switch ($sfl) {
				case 'mb_id' :
					$this->db->where('a.'.$sfl, $stx);
				break;
				default :
					$this->db->like('a.'.$sfl, $stx, 'both');
				break;
			}
		}
		$this->db->stop_cache();

		$result['total_cnt'] = $this->db->count_all_results('ki_point a');

		$this->db->join('ki_member b', 'a.mb_id = b.mb_id');
		$this->db->select('a.po_id,a.mb_id,a.po_datetime,a.po_content,a.po_point,a.po_rel_table,a.po_rel_id , b.mb_name,b.mb_nick,b.mb_point');
		$this->db->order_by($sst, $sod);
		$qry = $this->db->get('ki_point a', $limit, $offset);
		$result['qry'] = $qry->result_array();

		$this->db->select_sum('a.po_point');
		$query = $this->db->get('ki_point a');
		$row = $query->row_array();
		$result['total_pnt'] = $row['po_point'];

		$this->db->flush_cache();

		return $result;
	}

	function point_delete($po_ids) {
		$this->db->where_in('po_id', $po_ids);
		$this->db->delete('ki_point');
	}

	function point_reset($mb_id) {
		$this->db->select_sum('po_point');
		$qry = $this->db->get_where('ki_point', array('mb_id' => $mb_id));
		$row = $qry->row_array();

		$this->db->update('ki_member', array('mb_point' => $row['po_point']), array('mb_id' => $mb_id));
	}
}
?>