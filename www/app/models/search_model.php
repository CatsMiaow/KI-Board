<?php
class Search_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}
	
	function search_board($mb_level, $stx) {
		// 인기검색어
        $this->db->simple_query(" insert into ki_popular set pp_word = '".$stx."', pp_date = '".TIME_YMD."', pp_ip = '".$this->input->server('REMOTE_ADDR')."' ");

		$this->db->select('gr_id, bo_table, bo_read_level');
		$this->db->order_by('bo_order_search, gr_id, bo_table');
		$qry = $this->db->get_where('ki_board', array(
			'bo_use_search' => 1,
			'bo_list_level <=' => $mb_level
		));
		return $qry->result_array();
	}

	// UNION ALL로 합쳐야 제대로 나옴
	function list_result($type, $stx, $limit, $offset, $boards) {
		if (!$boards)
			return FALSE;
			
		if ($type == 'write') {
			//<!-- 게시글 검색
			$where = $this->_search_where($stx, 'wr_subject.wr_content');

			$this->db->start_cache();
			$this->db->where_in('bo_table', $boards);
			$this->db->where('wr_option !=', 2)->where($where, NULL, FALSE);
			$this->db->stop_cache();
			
			$result['total_count'] = $this->db->count_all_results('ki_write');
			
			
			$this->db->select('bo_table, wr_id, wr_subject, wr_content, wr_name, wr_datetime');
			$this->db->order_by('wr_datetime desc');
			$result['qry'] = $this->db->get('ki_write', $limit, $offset)->result_array();
			
			$this->db->flush_cache();
			//-->
		}
		else {
			//<!-- 댓글 검색
			$where = $this->_search_where($stx, 'co_content');

			$this->db->start_cache();
			$this->db->where_in('bo_table', $boards);
			$this->db->where('co_option !=', 2)->where($where, NULL, FALSE);
			$this->db->stop_cache();

			$result['total_count'] = $this->db->count_all_results('ki_comment');

			$this->db->select('bo_table, wr_id, co_id, co_content, co_name, co_datetime');
			$this->db->order_by('co_datetime desc');
			$result['qry'] = $this->db->get('ki_comment', $limit, $offset)->result_array();
			
			$this->db->flush_cache();
			//-->
		}

		return $result;
	}

	// WHERE 가공
	function _search_where($stx, $sfl) {
		$s = explode(' ', $stx);
		$field = explode('.', trim($sfl));
		
		$opt = '';
		$where = ' ( ';
		foreach ($s as $sval) {
			// 검색어
			$search_str = trim($sval);
			if ($search_str == '')
				continue;
			
			$opt2 = '';
			$where .= $opt.'(';
			foreach ($field as $fval) {
				$where .= $opt2;
				
				if (preg_match('/[a-zA-Z]/', $search_str))
					$where .= 'INSTR(LOWER('.$this->db->protect_identifiers($fval).'), LOWER('.$this->db->escape($search_str).'))';
				else
					$where .= 'INSTR('.$this->db->protect_identifiers($fval).', '.$this->db->escape($search_str).')';
							
				$opt2 = ' or ';
			}
			$where .= ')';
			$opt = ' and ';
		}
		$where .= ' ) ';

		return $where;
	}
}