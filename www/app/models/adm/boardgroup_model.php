<?php
class Boardgroup_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function list_result($sst, $sod, $sfl, $stx, $limit, $offset) {
        $this->db->start_cache();
        if ($stx) {
            switch ($sfl) {
                case "gr_id" :
                case "gr_admin" :
                    $this->db->where('a.'.$sfl, $stx);
                break;
                default :
                    $this->db->like('a.'.$sfl, $stx, 'both');
                break;
            }
        }
        $this->db->stop_cache();

        $result['total_cnt'] = $this->db->count_all_results('ki_board_group a');

        $this->db->join('ki_board b', 'a.gr_id = b.gr_id', 'left');
        $this->db->select('a.gr_id,a.gr_subject,a.gr_admin, count(b.bo_table) as bo_cnt');
        $this->db->order_by($sst, $sod);
        $this->db->group_by('gr_id');
        $qry = $this->db->get('ki_board_group a', $limit, $offset);
        $result['qry'] = $qry->result_array();

        $this->db->flush_cache();

        return $result;
    }

    function get_group($gr_id) {
        $this->db->select('gr_id,gr_subject,gr_admin');
        $query = $this->db->get_where('ki_board_group', array('gr_id' => $gr_id));
        return $query->row_array();
    }

    function insert() {
        $sql = array(
            'gr_id' => $this->input->post('gr_id'),
            'gr_subject' => $this->input->post('gr_subject'),
            'gr_admin' => $this->input->post('gr_admin')
        );
        $this->db->insert('ki_board_group', $sql);
    }

    function update() {
        $sql = array(
            'gr_subject' => $this->input->post('gr_subject'),
            'gr_admin' => $this->input->post('gr_admin')
        );
        $this->db->update('ki_board_group', $sql, array('gr_id' => $this->input->post('gr_id')));
    }

    function list_update($gr_id, $gr_subject, $gr_admin) {
        $sql = array(
            'gr_subject' => $gr_subject,
            'gr_admin' => $gr_admin
        );
        $this->db->update('ki_board_group', $sql, array('gr_id' => $gr_id));
    }

    function delete() {
        $this->db->where('gr_id', $this->input->post('gr_id', TRUE));
        $row = $this->db->count_all_results('ki_board');
        if ($row > 0)
            return FALSE;
        else
            return $this->db->delete('ki_board_group', array('gr_id' => $this->input->post('gr_id', TRUE)));
    }
}
?>