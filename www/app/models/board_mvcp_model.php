<?php
class Board_mvcp_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	
	// 이동, 복사 게시판 리스트
	function list_move_copy($bo_table, $mb_id) {
		$this->db->join('ki_board_group b', 'a.gr_id = b.gr_id');
		$this->db->select('a.bo_table,a.bo_subject, b.gr_subject');
		$this->db->where('bo_table <>', $bo_table);
		if (IS_ADMIN == 'group')
			$this->db->where('b.gr_admin', $mb_id);
		else if (IS_ADMIN == 'board')
			$this->db->where('a.bo_admin', $mb_id);
		
		$this->db->order_by('a.gr_id, a.bo_table');
		return $this->db->get('ki_board a')->result_array();
	}
	
	// 게시판의 최소 wr_num을 얻는다.
	function get_min_num($bo_table) {
	    // 가장 작은 번호를 얻어
	    $this->db->select_min('wr_num', 'min_wr_num');
	    $row = $this->db->get_where('ki_write', array('bo_table' => $bo_table))->row_array();
	    
	    return $row['min_wr_num'];
	}
	
	// 대상 게시물의 중복되지 않는 wr_num 추출 ( 답변까지 대상에 포함)
	function get_dist_num($bo_table, $wr_ids) {
		$this->db->distinct()->select('wr_num');
		$this->db->where('bo_table', $bo_table)->where_in('wr_id', $wr_ids);
		$this->db->order_by('wr_id');
		$result = $this->db->get('ki_write')->result_array();
		
		$wr_nums = array();
		foreach ($result as $row) {
			$wr_nums[] = $row['wr_num'];
		}
		
		return $wr_nums;
	}
	
	// 해당 wr_num 에 관련된 게시물 정보
	function get_write_num($bo_table, $wr_nums) {
		if (!$wr_nums) return FALSE;

		$this->db->select('wr_id,wr_num,wr_reply,ca_code,wr_comment,wr_option,wr_subject,wr_content,wr_hit,mb_id,wr_password,wr_name,wr_email,wr_datetime,wr_last,wr_ip,wr_count_file,wr_count_image');
		$this->db->order_by('wr_num desc, wr_id');
		$this->db->where('bo_table', $bo_table)->where_in('wr_num', $wr_nums);
		return $this->db->get('ki_write')->result_array();
	}
	
	// 게시물 Insert
	function write_insert($bo_table, $wr_num, $wr) {
		$sql = array(
  			'bo_table'    	   => $bo_table,
			'wr_num'           => $wr_num,
			'wr_reply'         => $wr['wr_reply'],
			'ca_code'          => $wr['ca_code'],
			'wr_comment'       => $wr['wr_comment'],
            'wr_option'        => $wr['wr_option'],
            'wr_subject'       => $wr['wr_subject'],
            'wr_content'       => $wr['wr_content'],
            'wr_hit'           => $wr['wr_hit'],
            'mb_id'            => $wr['mb_id'],
            'wr_password'      => $wr['wr_password'],
            'wr_name'          => $wr['wr_name'],
            'wr_email'         => $wr['wr_email'],
            'wr_datetime'      => $wr['wr_datetime'],
            'wr_last'          => $wr['wr_last'],
            'wr_ip'            => $wr['wr_ip'],
			'wr_count_file'    => $wr['wr_count_file'],
			'wr_count_image'   => $wr['wr_count_image']
		);
  		$this->db->insert('ki_write', $sql);
  		
  		return $this->db->insert_id();
	}
	
	// 게시물 파일 정보
	function get_write_file($bo_table, $wr_id) {
		$this->db->select('bf_editor,bf_no,bf_source,bf_file,bf_download,bf_filesize,bf_width,bf_height,bf_type,bf_datetime');
    	$this->db->order_by('bf_no');
    	return $this->db->get_where('ki_board_file', array(
    		'bo_table' => $bo_table,
    		'wr_id' => $wr_id
		))->result_array();
	}
	
	// 게시물 파일 Insert
	function write_file_insert($bo_table, $wr_id, $bf) {
		$sql = array(
        	'bo_table'    => $bo_table,
            'wr_id'       => $wr_id,
			'bf_editor'	  => $bf['bf_editor'],
            'bf_no'       => $bf['bf_no'],
            'bf_source'   => $bf['bf_source'],
            'bf_file'     => $bf['bf_file'],
            'bf_download' => $bf['bf_download'],
            'bf_filesize' => $bf['bf_filesize'],
            'bf_width'    => $bf['bf_width'],
            'bf_height'   => $bf['bf_height'],
            'bf_type'     => $bf['bf_type'],
            'bf_datetime' => $bf['bf_datetime']
		);
		$this->db->insert('ki_board_file', $sql);
	}
	
	// 게시판 글/댓글/wr_num Update
	function bo_count_update($bo_table, $count_write, $count_comment, $min_wr_num, $op) {
		$this->db->set('bo_count_write', 'bo_count_write '.$op.' '.$count_write, FALSE);
		$this->db->set('bo_count_comment', 'bo_count_comment '.$op.' '.$count_comment, FALSE);
		$this->db->update('ki_board', array('bo_min_wr_num' => $min_wr_num), array('bo_table' => $bo_table)); 
	}
	
	// 이동시 원글/파일 삭제
	function write_delete($bo_table, $wr_ids) {
		if (!$wr_ids) return FALSE;
		
		// 게시글, 파일테이블, 댓글 삭제
		$this->db->where('bo_table', $bo_table)->where_in('wr_id', $wr_ids);
		$this->db->delete(array('ki_write', 'ki_board_file', 'ki_comment'));
	}

	// 첨부파일로 인한 본문 업데이트
	function content_update($bo_table, $wr_id, $content) {
        $this->db->update('ki_write', array(
            'wr_content' => $content
        ), array('bo_table' => $bo_table, 'wr_id' => $wr_id));
    }

	// 댓글 정보
	function get_comment($bo_table, $wr_id) {
		$this->db->select('co_num,co_reply,ca_code,co_option,co_content,mb_id,co_password,co_name,co_datetime,co_last,co_ip');
		$this->db->order_by('co_num, co_id');
		return $this->db->get_where('ki_comment', array(
			'bo_table' => $bo_table,
			'wr_id' => $wr_id
		))->result_array();
	}

	// 댓글 등록
	function comment_insert($bo_table, $wr_id, $co_num, $co) {
		$sql = array(
			'bo_table'	  => $bo_table,
			'wr_id'		  => $wr_id,
			'co_num'	  => $co_num,
			'co_reply'	  => $co['co_reply'],
			'ca_code'	  => $co['ca_code'],
			'co_option'   => $co['co_option'],
			'co_content'  => $co['co_content'],
			'mb_id'		  => $co['mb_id'],
			'co_password' => $co['co_password'],
			'co_name'	  => $co['co_name'],
			'co_datetime' => $co['co_datetime'],
			'co_last'	  => $co['co_last'],
			'co_ip'		  => $co['co_ip']
		);
		$this->db->insert('ki_comment', $sql);
	}
}