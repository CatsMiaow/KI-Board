<?php
class Visit_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function list_result($fr_date, $to_date, $limit, $offset) {
        $this->db->start_cache();
        $this->db->where("vi_date between '".$fr_date."' and '".$to_date."'");
        $this->db->stop_cache();

        $result['total_cnt'] = $this->db->count_all_results('ki_visit');

        $this->db->select('vi_ip, vi_date, vi_time, vi_referer, vi_agent');
        $this->db->order_by('vi_id', 'desc');
        $qry = $this->db->get('ki_visit', $limit, $offset);
        $result['qry'] = $qry->result_array();

        $this->db->flush_cache();

        return $result;
    }
}
?>