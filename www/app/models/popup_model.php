<?php
class Popup_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	function get($pu_id, $fields='*') {
		if (!$pu_id)
			return FALSE;
		
		return $this->db->select($fields)->get_where('ki_popup', array('pu_id' => $pu_id))->row_array();
	}

    function output() {
		if ($this->config->item('cf_use_popup')) {
			return $this->db->select('pu_id,pu_file,pu_type,pu_width,pu_height,pu_x,pu_y')->get_where('ki_popup', array(
				'pu_sdate <=' => TIME_YMDHIS,
				'pu_edate >=' => TIME_YMDHIS,
				'pu_use' => 1
			))->result_array();
		}

		return array();
	}
}