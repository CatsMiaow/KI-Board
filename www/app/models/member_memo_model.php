<?php
class Member_memo_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function cf_delete($mb_id) {
        $this->db->delete('ki_memo', array(
            'mb_id' => $mb_id,
            'me_datetime <' => date("Y-m-d H:i:s", time() - (86400 * $this->config->item('cf_memo_del'))),
            'me_check !=' => '0000-00-00 00:00:00'
        ));
    }

    function total_cnt($flag, $mb_id) {
        $this->db->where(array('me_flag' => $flag, 'mb_id' => $mb_id));
        $this->db->from('ki_memo');
        return $this->db->count_all_results();
    }

    function list_result($flag, $mb_id, $limit, $offset) {
        $this->db->select('a.me_no, a.me_mb_id, a.me_content, a.me_datetime, a.me_check, b.mb_name, b.mb_nick');
        $this->db->join('ki_member b', 'a.me_mb_id = b.mb_id');
        $this->db->order_by('me_no', 'desc');
        $qry = $this->db->get_where('ki_memo a', array('a.me_flag' => $flag, 'a.mb_id' => $mb_id), $limit, $offset);
        return $qry->result_array();
    }

    function get_memo($me_no, $flag, $mb_id) {
        $this->db->select('me_no, me_mb_id, me_content, me_datetime, me_check');
        $query = $this->db->get_where('ki_memo', array(
            'me_no' => $me_no,
            'me_flag' => $flag,
            'mb_id' => $mb_id
        ));
        return $query->row_array();
    }
    
    function get_del_memo($me_no, $flag, $mb_id) {
        $this->db->select('me_check');
        $this->db->where_in('me_no', $me_no);
        $query = $this->db->get_where('ki_memo', array(
            'me_flag' => $flag,
            'mb_id' => $mb_id
        ));
        return $query->result_array();
    }

    // Multi Insert 개선 여부... insert into values... $this->db->insert_batch();
    function insert($mb_id, $me_mb_id, $me_content) {
        $this->db->select_max('me_no', 'max_me_no');
        $query = $this->db->get('ki_memo');
        $row = $query->row_array();

        $me_no = $row['max_me_no'] + 1;
        $me_child = $me_no + 1;

        // 보내는 놈
        $data = array(
            'me_no' => $me_no,
            'me_parent' => $me_child,
            'me_flag' => 'S',
            'mb_id' => $mb_id,
            'me_mb_id' => $me_mb_id,
            'me_content' => $me_content,
            'me_datetime' => TIME_YMDHIS,
            'me_check' => '0000-00-00 00:00:00'
        );
        $this->db->insert('ki_memo', $data);

        // 받는 놈
        $data = array(
            'me_no' => $me_child,
            'me_parent' => $me_child,
            'me_flag' => 'R',
            'mb_id' => $me_mb_id,
            'me_mb_id' => $mb_id,
            'me_content' => $me_content,
            'me_datetime' => TIME_YMDHIS,
            'me_check' => '0000-00-00 00:00:00'
        );
        $this->db->insert('ki_memo', $data);

        $this->db->set('mb_memo_cnt', 'mb_memo_cnt + 1', FALSE);
        $this->db->update('ki_member', array('mb_memo_call' => $mb_id), array('mb_id' => $me_mb_id));
    }

    function memo_link($me_no, $flag, $mb_id, $link) {
        if ($link == 'prev') {
            $link = '>'; $order = 'asc';
        } else if ($link == 'next') {
            $link = '<'; $order = 'desc';
        }

        $this->db->select('me_no');
        $this->db->order_by('me_no', $order);
        $query = $this->db->get_where('ki_memo', array(
            'me_no '.$link => $me_no,
            'me_flag' => $flag,
            'mb_id' => $mb_id
        ), 1);
        return $query->row_array();
    }

    function read_check($me_no) {
        $this->db->update('ki_memo', array(
            'me_check' => TIME_YMDHIS
        ), array(
            'me_parent' => $me_no,
            'me_check' => '0000-00-00 00:00:00'
        ));
    }

    function memo_delete($me_no, $flag, $mb_id) {
        $this->db->where_in('me_no', $me_no);
        $this->db->delete('ki_memo', array(
            'me_flag' => $flag,
            'mb_id' => $mb_id
        ));
    }

    function call_delete($mb_id) {
        $this->db->update('ki_member', array('mb_memo_call' => ''), array('mb_id' => $mb_id));
    }
    
    function memo_count($mb_id, $cnt='1') {
        $this->db->set('mb_memo_cnt', 'mb_memo_cnt - '.$cnt, FALSE);
        $this->db->update('ki_member', null, array('mb_id' => $mb_id));
    }
}
?>