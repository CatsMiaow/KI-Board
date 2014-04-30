<?php
class Latest_model extends CI_Model {
	function __construct() {
		parent::__construct();
		/* cache delete
		/app/controllers/_trans/board_write.php, board_comment.php
		$this->db->cache_delete('default', 'index'); */
	}

	// 게시글
    function write($bo_table, $limit=5, $cut=50) {
        $this->db->cache_on();

		if (is_array($bo_table))
			$this->db->where_in('bo_table', $bo_table);
		else
			$this->db->where('bo_table', $bo_table);
		
		$this->db->select('bo_table, wr_id, wr_comment, ca_code, wr_subject, wr_option');
		$result = $this->db->order_by('wr_datetime', 'desc')->get('ki_write', $limit)->result_array();

        $this->db->cache_off();
        
        $list = array();
		$this->load->helper('textual');
		foreach($result as $i => $row) {
			if (strpos($row['wr_option'], 'secret') !== FALSE) // DB 쿼리로 교체 요망
				continue;
				
			$list[$i] = new stdClass();
			$list[$i]->href = RT_PATH.'/board/'.$row['bo_table'].'/view/wr_id/'.$row['wr_id'].($row['ca_code'] ? '?sca='.$row['ca_code'] : '');
			$list[$i]->subject = cut_str(get_text($row['wr_subject']), $cut);
			$list[$i]->comt_cnt = ($row['wr_comment']) ? '('.$row['wr_comment'].')' : '';
		}

		return $list;
	}
    
    // 댓글
    function comment($limit=5, $cut=50) {
        $this->db->cache_on();
        
		$this->db->select('bo_table, wr_id, co_id, co_option, co_content');
		$this->db->order_by('co_datetime', 'desc');
		$result = $this->db->get('ki_comment', $limit)->result_array();
		
        $this->db->cache_off();
        
		$list = array();
		$this->load->helper('textual');
		foreach($result as $i => $row) {
            if (strpos($row['co_option'], 'secret') !== FALSE) // DB 쿼리로 교체 요망
				continue;

			$list[$i] = new stdClass();
			$list[$i]->href = RT_PATH.'/board/'.$row['bo_table'].'/view/wr_id/'.$row['wr_id'].'#c_'.$row['co_id'];
			$list[$i]->content = cut_str(get_text($row['co_content']), $cut); 
		}

		return $list;
	}    
}