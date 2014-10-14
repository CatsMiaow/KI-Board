<?php
class Popup_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function list_result($sst, $sod, $sfl, $stx, $limit, $offset) {
        $this->db->start_cache();
        if ($stx) {
            switch ($sfl) {
                default :
                    $this->db->like($sfl, $stx, 'both');
                break;
            }
        }
        $this->db->stop_cache();

        $result['total_cnt'] = $this->db->count_all_results('ki_popup');

        $this->db->select('pu_id,pu_name,pu_use,pu_type,pu_sdate,pu_edate,pu_width,pu_height,pu_x,pu_y,pu_datetime');
        $this->db->order_by($sst, $sod);
        $qry = $this->db->get('ki_popup', $limit, $offset);
        $result['qry'] = $qry->result_array();

        $this->db->flush_cache();

        return $result;
    }

    function get_popup($pu_id, $fields='*') {
        if (!$pu_id)
            return FALSE;
        
        return $this->db->select($fields)->get_where('ki_popup', array('pu_id' => $pu_id))->row_array();
    }

    function record($w='') {
        $sql = array(
            'pu_name' => $this->input->post('pu_name'),
            'pu_file' => $this->input->post('pu_file'),
            'pu_use' => $this->input->post('pu_use'),
            'pu_type' => $this->input->post('pu_type'),
            'pu_sdate' => $this->input->post('sdate').' '.$this->input->post('stime_h').':'.$this->input->post('stime_i').':'.$this->input->post('stime_s'),
            'pu_edate' => $this->input->post('edate').' '.$this->input->post('etime_h').':'.$this->input->post('etime_i').':'.$this->input->post('etime_s'),
            'pu_width' => $this->input->post('pu_width'),
            'pu_height' => $this->input->post('pu_height'),
            'pu_x' => $this->input->post('pu_x'),
            'pu_y' => $this->input->post('pu_y')
        );

        if ($w == '') {
            $sql['pu_datetime'] = TIME_YMDHIS;
            $this->db->insert('ki_popup', $sql);
            return $this->db->insert_id();
        }
        else {
            $this->db->update('ki_popup', $sql, array('pu_id' => $this->input->post('pu_id')));
            return $this->input->post('pu_id');
        }
    }
    
    function list_update($pu_ids, $pu_names, $pu_uses) {
        $batch = array();
        foreach ($pu_ids as $pu_id) {
            $batch[] = array(
                'pu_id' => $pu_id,
                'pu_name' => $pu_names[$pu_id],
                'pu_use' => (isset($pu_uses[$pu_id])) ? $pu_uses[$pu_id] : ''
            );
        }

        $this->db->update_batch('ki_popup', $batch, 'pu_id');
    }

    function delete($pu_ids) {
        $this->db->where_in('pu_id', $pu_ids);
        $this->db->delete('ki_popup');
    }
}
?>