<?php
class Categoryform_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	function list_result($type) {
		$this->db->select("REPLACE(ca_code,'.','-') as code, ca_name", FALSE);
		$this->db->order_by('ca_code - 0', 'asc');
		$qry = $this->db->get_where('ki_category', array('ca_type' => $type));
		return $qry->result_array();
	}

	function insert($ca_code, $ca_name) {
		$sql = array(
			'ca_type' => $this->input->post('type'),
			'ca_name' => $ca_name,
			'ca_code' => $ca_code
		);
		$this->db->insert('ki_category', $sql);
	}

	function update($ca_code, $ca_name) {
		$sql = array('ca_name' => $ca_name);
		$this->db->update('ki_category', $sql, array(
			'ca_type' => $this->input->post('type'),
			'ca_code' => $ca_code
		));
	}

	function delete($ca_code, $limit_code) {
		$this->db->delete('ki_category', array(
			'ca_type' => $this->input->post('type'),
			'ca_code >=' => (float)$ca_code,
			'ca_code <' => (float)$limit_code
		));
	}
}
?>