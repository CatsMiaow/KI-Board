<?php
class Category_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function get_category($type, $select=FALSE) {
        if (!$type) return FALSE;
        
        if (!$select)
            $this->db->select('ca_code, ca_name');
        else {
            $this->db->select("REPLACE(ca_code,'.','-') as code, ca_name", FALSE);
            $this->db->order_by('ca_code', 'asc');
        }
        
        $qry = $this->db->get_where('ki_category', array('ca_type' => $type));
        $result = $qry->result_array();
                
        if ($select)
            return $result;
        else {                        
            $list = array();
            foreach ($result as $row) {
                $list[$row['ca_code']] = $row['ca_name'];
            }
            return $list;
        }         
    }
}
?>