<?php
class Board_comt_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    // 댓글 리스트
    function list_result($bo_table, $wr_id, $limit, $offset) {
        $this->db->select('co_id, mb_id, co_option, co_content, co_name, co_datetime, co_ip, co_reply');
        $this->db->order_by('co_num, co_reply asc');
        return $this->db->get_where('ki_comment', array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id
        ), $limit, $offset)->result_array();
    }

    // 댓글 정보
    function get_comment($bo_table, $wr_id, $co_id, $field) {
        if (!$co_id) return FALSE;

        $this->db->select($field);
        return $this->db->get_where('ki_comment', array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'co_id' => $co_id
        ))->row_array();
    }

    // 동일내용 연속 등록 불가
    function same_comment($bo_table, $wr_id, $commend_id) {
        $this->db->select('MD5(CONCAT(co_ip, co_content)) as prev_md5', FALSE);
        $this->db->where(array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id
        ));
        if ($this->input->post('w') == 'cu')
            $this->db->where('co_id <>', $commend_id);

        return $this->db->order_by('co_id', 'desc')->get('ki_comment', 1)->row_array();
    }

    // 관련 답변댓글 존재 여부
    function is_comment_reply($bo_table, $wr_id, $comment_id, $tmp_num, $tmp_reply) {
        $len = strlen($tmp_reply);
        $len = ($len < 0) ? 0 : $len;
        $comment_reply = substr($tmp_reply, 0, $len);

        $this->db->where(array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'co_id <>' => $comment_id,
            'co_num' => $tmp_num
        ));
        $this->db->like('co_reply', $comment_reply, 'after');

        if ($this->db->count_all_results('ki_comment') > 0)
            return TRUE;

        return FALSE;
    }

    // 댓글 답변 단계 얻기
    function get_reply_step($bo_table, $wr_id, $tmp_num, $bo_reply_order, $co_reply) {
        $reply_len = strlen($co_reply) + 1;

        if ($bo_reply_order) {
            $begin_reply_char = 'A';
            $end_reply_char = 'Z';
            $reply_number = +1;

            $this->db->select_max(' SUBSTRING(co_reply, '.$reply_len.', 1) ', 'reply');
        }
        else {
            $begin_reply_char = 'Z';
            $end_reply_char = 'A';
            $reply_number = -1;

            $this->db->select_min(' SUBSTRING(co_reply, '.$reply_len.', 1) ', 'reply');
        }
        
        $this->db->where(array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'co_num' => $tmp_num,
            'SUBSTRING(co_reply, '.$reply_len.', 1) <>' => ''
        ));

        if ($co_reply)
            $this->db->like('co_reply', $co_reply, 'after');

        $row = $this->db->get('ki_comment')->row_array();

        if (!isset($row['reply']))
            $reply_char = $begin_reply_char;
        else if ($row['reply'] == $end_reply_char) // A~Z은 26 입니다.
            alert("더 이상 답변하실 수 없습니다.\\n\\n답변은 26개 까지만 가능합니다.");
        else
            $reply_char = chr(ord($row['reply']) + $reply_number);

        return $co_reply.$reply_char;
    }

    // 댓글의 최대 co_num을 얻는다.
    function get_max_num($bo_table, $wr_id) {
        $this->db->select_max('co_num', 'max_co_num');
        $row = $this->db->get_where('ki_comment', array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id
        ))->row_array();
        
        return $row['max_co_num'];
    }

    // 댓글 등록
    function comment_insert($bo_table, $wr_id, $tmp_num, $tmp_reply, $ca_code, $mb) {
        $sql = array(
			'bo_table'    => $bo_table,
			'wr_id'       => $wr_id,
			'co_num'      => $tmp_num,
			'co_reply'    => $tmp_reply,
			'ca_code'     => $ca_code,
			'co_option'   => $this->input->post('co_secret'),
			'co_content'  => $this->input->post('co_content'),
			'mb_id'       => $mb['mb_id'],
			'co_password' => $mb['co_password'],
			'co_name'     => $mb['co_name'],
			'co_datetime' => TIME_YMDHIS,
			'co_last'     => TIME_YMDHIS,
			'co_ip'       => $this->input->server('REMOTE_ADDR')
        );
        $this->db->insert('ki_comment', $sql);

        $comment_id = $this->db->insert_id();

        // 원글에 댓글수 증가
        $this->db->set('wr_comment', 'wr_comment + 1', FALSE);
        $this->db->update('ki_write', null, array('bo_table' => $bo_table, 'wr_id' => $wr_id));

        // 댓글 1 증가
        $this->db->set('bo_count_comment', 'bo_count_comment + 1', FALSE);
        $this->db->update('ki_board', null, array('bo_table' => $bo_table));
        
        return $comment_id;
    }

    // 댓글 수정
    function comment_update($bo_table, $wr_id, $comment_id) {
        $sql = array('co_content' => $this->input->post('co_content'));

        if (!IS_ADMIN) {
            $sql['co_last'] = TIME_YMDHIS;
            $sql['co_ip'] = $this->input->server('REMOTE_ADDR');
        }

        if ($this->input->post('co_secret'))
            $sql['co_option'] = $this->input->post('co_secret');

        $this->db->update('ki_comment', $sql, array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'co_id' => $comment_id
        ));
    }
    
    // 댓글 삭제
    function comment_delete($bo_table, $wr_id, $comment_id) {
        if (!$comment_id) return FALSE;
        
        // 댓글 삭제
        $this->db->delete('ki_comment', array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'co_id' => $comment_id
        ));
        
        // 원글의 댓글 숫자를 감소
        $this->db->set('wr_comment', 'wr_comment - 1', FALSE);
        $this->db->update('ki_write', null, array('bo_table' => $bo_table, 'wr_id' => $wr_id));
        
        // 댓글 숫자 감소
        $this->db->set('bo_count_comment', 'bo_count_comment - 1', FALSE);
        $this->db->update('ki_board', null, array('bo_table' => $bo_table));
    }
}