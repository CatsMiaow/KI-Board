<?php
class Point_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	function insert($mb_id, $point, $content='', $rel_table='', $rel_id='', $rel_action='') {
		// 포인트 사용을 하지 않는다면 return
		if (!$this->config->item('cf_use_point')) { return 0; }

		// 포인트가 없다면 업데이트 할 필요 없음
		if ($point == 0) { return 0; }

		// 회원아이디가 없다면 업데이트 할 필요 없음
		if ($mb_id == "") { return 0; }
		$mb = $this->Basic_model->get_member($mb_id, 'mb_id');
		if (!isset($mb['mb_id'])) { return 0; }

		// 이미 등록된 내역이라면 건너뜀
		if ($rel_table || $rel_id || $rel_action) {
			if ($rel_table == "@login") {
			} else { // 로그인테이블의 경우에는 등록된 내역 확인을 생략
				$this->db->where(array(
					'mb_id' => $mb_id,
					'po_rel_table' => $rel_table,
					'po_rel_id' => $rel_id,
					'po_rel_action' => $rel_action
				));
				$this->db->from('ki_point');
				$cnt = $this->db->count_all_results();
				if ($cnt)
					return -1;
			}
		}

		// 포인트 건별 생성
		$this->db->insert('ki_point', array(
			'mb_id' => $mb_id,
			'po_datetime' => TIME_YMDHIS,
			'po_content' => addslashes($content),
			'po_point' => $point,
			'po_rel_table' => $rel_table,
			'po_rel_id' => $rel_id,
			'po_rel_action' => $rel_action
		));

		// 포인트 내역의 합을 구하고
		$this->db->select_sum('po_point', 'sum_po_point');
		$query = $this->db->get_where('ki_point', array('mb_id'=>$mb_id));
		$row = $query->row_array();
		$sum_point = $row['sum_po_point'];

		// 포인트 UPDATE
		$this->db->update('ki_member', array('mb_point'=>$sum_point), array('mb_id' => $mb_id));

		return 1;
	}

	function delete($mb_id, $rel_table, $rel_id, $rel_action) {
		$result = FALSE;

		if ($rel_table || $rel_id || $rel_action) {
			$this->db->delete('ki_point', array(
				'mb_id' => $mb_id,
				'po_rel_table' => $rel_table,
				'po_rel_id' => $rel_id,
				'po_rel_action' => $rel_action
			));

			// 포인트 내역의 합을 구하고
			$this->db->select_sum('po_point', 'sum_po_point');
			$query = $this->db->get_where('ki_point', array('mb_id'=>$mb_id));
			$row = $query->row_array();
			$sum_point = $row['sum_po_point'];

			// 포인트 UPDATE
			$result = $this->db->update('ki_member', array('mb_point'=>$sum_point), array('mb_id' => $mb_id));
		}

		return $result;
	}
}
?>