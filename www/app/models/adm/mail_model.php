<?php
class Mail_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function list_result($limit, $offset) {
        $result['total_cnt'] = $this->db->count_all_results('ki_mail');

        $this->db->select('ma_id,ma_subject,ma_content,ma_time');
        $this->db->order_by('ma_id', 'desc');
        $qry = $this->db->get('ki_mail', $limit, $offset);
        $result['qry'] = $qry->result_array();

        return $result;
    }

    function get_mail($ma_id, $fields='*') {
        if (!$ma_id)
            return FALSE;
        
        $this->db->select($fields);
        $qry = $this->db->get_where('ki_mail', array('ma_id' => $ma_id));
        return $qry->row_array();
    }

    function insert() {
        $sql = array(
            'ma_subject' => $this->input->post('ma_subject'),
            'ma_content' => $this->input->post('ma_content', TRUE),
            'ma_time' => TIME_YMDHIS,
            'ma_ip' => $this->input->server('REMOTE_ADDR')
        );
        $this->db->insert('ki_mail', $sql);
    }

    function update() {
        $sql = array(
            'ma_subject' => $this->input->post('ma_subject'),
            'ma_content' => $this->input->post('ma_content', TRUE),
            'ma_time' => TIME_YMDHIS,
            'ma_ip' => $this->input->server('REMOTE_ADDR')
        );
        $this->db->update('ki_mail', $sql, array('ma_id' => $this->input->post('ma_id')));
    }

    function delete() {
        $this->db->delete('ki_mail', array('ma_id' => $this->input->post('ma_id'))); 
    }

    function member_cnt() {
        $result['total_cnt'] = $this->db->count_all_results('ki_member');

        $this->db->where('mb_leave_date <>', '');
        $result['leave_cnt'] = $this->db->count_all_results('ki_member');
    
        $result['member_cnt'] = $result['total_cnt'] - $result['leave_cnt'];
        return $result;
    }

    function select_list() {
        $this->db->start_cache();

        if ($this->input->post('mb_email'))
            $this->db->like('mb_email', $this->input->post('mb_email'), 'both');

        if ($this->input->post('mb_birth_from') && $this->input->post('mb_birth_to'))
            $this->db->where("SUBSTRING(REPLACE(mb_birth,'-',''),5,4) BETWEEN ".$this->input->post('mb_birth_from')." AND ".$this->input->post('mb_birth_to'));

        if ($this->input->post('mb_area'))
            $this->db->like('mb_addr1', $this->input->post('mb_area'), 'after');

        if ($this->input->post('mb_mailling'))
            $this->db->where('mb_mailling', $this->input->post('mb_mailling'));

        $this->db->where('mb_level BETWEEN '.$this->input->post('mb_level_from').' AND '.$this->input->post('mb_level_to'));
        $this->db->where(array(
            'mb_id !=' => ADMIN,
            'mb_leave_date' => ''
        ));

        $this->db->stop_cache();

        $result['select_cnt'] = $this->db->count_all_results('ki_member');

        $this->db->select('mb_id,mb_name,mb_nick,mb_email,mb_birth,mb_datetime');
        $this->db->order_by('mb_id', 'asc');
        $qry = $this->db->get('ki_member');
        $result['qry'] = $qry->result_array();

        $this->db->flush_cache();

        return $result;
    }

    function option_update() {
        $ma_last_option = "mb_email=".$this->input->post('mb_email');
        $ma_last_option .= "||mb_birth_from=".$this->input->post('mb_birth_from');
        $ma_last_option .= "||mb_birth_to=".$this->input->post('mb_birth_to');
        $ma_last_option .= "||mb_area=".$this->input->post('mb_area');
        $ma_last_option .= "||mb_mailling=".$this->input->post('mb_mailling');
        $ma_last_option .= "||mb_level_from=".$this->input->post('mb_level_from');
        $ma_last_option .= "||mb_level_to=".$this->input->post('mb_level_to');

        $this->db->update('ki_mail', array('ma_last_option' => $ma_last_option), array('ma_id' => $this->input->post('ma_id')));
    }
}
?>