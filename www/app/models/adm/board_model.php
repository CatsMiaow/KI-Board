<?php
class Board_model extends CI_Model {
	function __construct() {
		parent::__construct();
	}

	function list_result($sst, $sod, $sfl, $stx, $limit, $offset) {
		$this->db->start_cache();
		if ($stx) {
			switch ($sfl) {
				case 'bo_table' :
					$this->db->like($sfl, $stx, 'after');
				break;
				case 'gr_id' :
					$this->db->where($sfl, $stx);
				break;
				default :
					$this->db->like($sfl, $stx, 'both');
				break;
			}
		}
		$this->db->stop_cache();

		$result['total_cnt'] = $this->db->count_all_results('ki_board');

		$this->db->select('bo_table,bo_subject,gr_id,bo_skin,bo_use_search,bo_order_search');
		$this->db->order_by($sst, $sod);
		$qry = $this->db->get('ki_board', $limit, $offset);
		$result['qry'] = $qry->result_array();

		$this->db->flush_cache();

		return $result;
	}

	function is_group() {
		$row = $this->db->count_all_results('ki_board_group');
		return ($row < 1) ? FALSE : TRUE;
	}

	function insert() {
		$sql = array(
			'bo_table'			=> $this->input->post('bo_table'),
			'gr_id'             => $this->input->post('gr_id'),
			'bo_subject'        => $this->input->post('bo_subject'),
			'bo_admin'          => $this->input->post('bo_admin'),
			'bo_list_level'     => $this->input->post('bo_list_level'),
			'bo_read_level'     => $this->input->post('bo_read_level'),
			'bo_write_level'    => $this->input->post('bo_write_level'),
			'bo_reply_level'    => $this->input->post('bo_reply_level'),
			'bo_comment_level'  => $this->input->post('bo_comment_level'),
			'bo_upload_level'   => $this->input->post('bo_upload_level'),
			'bo_download_level' => $this->input->post('bo_download_level'),
			'bo_count_modify'   => $this->input->post('bo_count_modify'),
			'bo_count_delete'   => $this->input->post('bo_count_delete'),
			'bo_use_private'    => $this->input->post('bo_use_private'),
			'bo_use_rss'        => $this->input->post('bo_use_rss'),
			'bo_use_sns'        => $this->input->post('bo_use_sns'),
			'bo_use_comment'    => $this->input->post('bo_use_comment'),
			'bo_use_category'   => $this->input->post('bo_use_category'),
			'bo_use_sideview'   => $this->input->post('bo_use_sideview'),
			'bo_use_secret'     => $this->input->post('bo_use_secret'),
			'bo_use_editor'		=> $this->input->post('bo_use_editor'),
			'bo_use_name'       => $this->input->post('bo_use_name'),
			'bo_use_ip_view'    => $this->input->post('bo_use_ip_view'),
			'bo_use_list_view'  => $this->input->post('bo_use_list_view'),
			'bo_use_email'      => $this->input->post('bo_use_email'),
			'bo_use_extra'		=> $this->input->post('bo_use_extra'),
			'bo_use_syntax'		=> $this->input->post('bo_use_syntax'),
			'bo_subject_len'    => $this->input->post('bo_subject_len'),
			'bo_page_rows'      => $this->input->post('bo_page_rows'),
			'bo_page_rows_comt' => $this->input->post('bo_page_rows_comt'),
			'bo_new'            => $this->input->post('bo_new'),
			'bo_hot'            => $this->input->post('bo_hot'),
			'bo_image_width'    => $this->input->post('bo_image_width'),
			'bo_skin'           => $this->input->post('bo_skin'),
			'bo_reply_order'    => $this->input->post('bo_reply_order'),
			'bo_sort_field'     => $this->input->post('bo_sort_field'),
			'bo_upload_ext'     => $this->input->post('bo_upload_ext'),
			'bo_upload_size'    => $this->input->post('bo_upload_size'),
			'bo_head'			=> $this->input->post('bo_head'),
			'bo_tail'			=> $this->input->post('bo_tail'),
			'bo_insert_content' => $this->input->post('bo_insert_content'),
			'bo_use_search'     => $this->input->post('bo_use_search'),
			'bo_order_search'   => $this->input->post('bo_order_search'),
			'bo_count_write'	=> 0,
            'bo_count_comment'  => 0
		);

		$this->db->insert('ki_board', $sql);
	}

	function update($is_notice='') {
		$sql = array(
			'gr_id'             => $this->input->post('gr_id'),
			'bo_subject'        => $this->input->post('bo_subject'),
			'bo_admin'          => $this->input->post('bo_admin'),
			'bo_list_level'     => $this->input->post('bo_list_level'),
			'bo_read_level'     => $this->input->post('bo_read_level'),
			'bo_write_level'    => $this->input->post('bo_write_level'),
			'bo_reply_level'    => $this->input->post('bo_reply_level'),
			'bo_comment_level'  => $this->input->post('bo_comment_level'),
			'bo_upload_level'   => $this->input->post('bo_upload_level'),
			'bo_download_level' => $this->input->post('bo_download_level'),
			'bo_count_modify'   => $this->input->post('bo_count_modify'),
			'bo_count_delete'   => $this->input->post('bo_count_delete'),
			'bo_use_private'    => $this->input->post('bo_use_private'),
			'bo_use_rss'        => $this->input->post('bo_use_rss'),
			'bo_use_sns'        => $this->input->post('bo_use_sns'),
			'bo_use_category'   => $this->input->post('bo_use_category'),
			'bo_use_comment'    => $this->input->post('bo_use_comment'),
			'bo_use_sideview'   => $this->input->post('bo_use_sideview'),
			'bo_use_secret'     => $this->input->post('bo_use_secret'),
			'bo_use_editor'		=> $this->input->post('bo_use_editor'),
			'bo_use_name'       => $this->input->post('bo_use_name'),
			'bo_use_ip_view'    => $this->input->post('bo_use_ip_view'),
			'bo_use_list_view'  => $this->input->post('bo_use_list_view'),
			'bo_use_email'      => $this->input->post('bo_use_email'),
			'bo_use_extra'		=> $this->input->post('bo_use_extra'),
			'bo_use_syntax'		=> $this->input->post('bo_use_syntax'),
			'bo_subject_len'    => $this->input->post('bo_subject_len'),
			'bo_page_rows'      => $this->input->post('bo_page_rows'),
			'bo_page_rows_comt' => $this->input->post('bo_page_rows_comt'),
			'bo_new'            => $this->input->post('bo_new'),
			'bo_hot'            => $this->input->post('bo_hot'),
			'bo_image_width'    => $this->input->post('bo_image_width'),
			'bo_skin'           => $this->input->post('bo_skin'),
			'bo_reply_order'    => $this->input->post('bo_reply_order'),
			'bo_sort_field'     => $this->input->post('bo_sort_field'),
			'bo_upload_ext'     => $this->input->post('bo_upload_ext'),
			'bo_upload_size'    => $this->input->post('bo_upload_size'),
			'bo_head'			=> $this->input->post('bo_head'),
			'bo_tail'			=> $this->input->post('bo_tail'),
			'bo_insert_content' => $this->input->post('bo_insert_content'),
			'bo_use_search'     => $this->input->post('bo_use_search'),
			'bo_order_search'   => $this->input->post('bo_order_search')
		);

		$this->db->where('bo_table', $this->input->post('bo_table'));
		$sql['bo_count_write'] = $this->db->count_all_results('ki_write');

		$this->db->where('bo_table', $this->input->post('bo_table'));
		$sql['bo_count_comment'] = $this->db->count_all_results('ki_comment');

		// 공지사항에는 등록되어 있지만 실제 존재하지 않는 글 아이디는 삭제합니다.
		if ($is_notice) {
			$notice = explode(',', $is_notice);
			$this->db->select('wr_id');
			$this->db->where('bo_table', $this->input->post('bo_table'));
			$this->db->where_in('wr_id', $notice);
			$query = $this->db->get('ki_write');
			$result = $query->result_array();

			$bo_notice = $lf = '';
			foreach($result as $row) {
				$bo_notice .= $lf.$row['wr_id'];
				$lf = '\n';
			}

			$sql['bo_notice'] = $bo_notice;
		}

		$this->db->update('ki_board', $sql, array('bo_table' => $this->input->post('bo_table')));
	}

	function group_update() {
		$sql = array();
		$chk = $this->input->post('chk');
		foreach($chk as $key => $val) {
			$sql[$key] = $this->input->post($key);
		}

		$this->db->update('ki_board', $sql, array('gr_id' => $this->input->post('gr_id')));
	}

	// 글수 조정
	function proc_count() {
		$this->db->query("
			UPDATE ki_write a JOIN (
				SELECT bo_table, wr_id, COUNT(co_id) AS cnt FROM ki_comment WHERE bo_table = '".$this->input->post('bo_table')."' GROUP BY wr_id
			) b ON a.bo_table = b.bo_table AND a.wr_id = b.wr_id SET a.wr_comment = b.cnt
		");
	}

	function list_update($bo_table, $bo_subject, $gr_id, $bo_skin, $bo_use_search, $bo_order_search) {
		$this->db->update('ki_board', array(
			'bo_subject' => $bo_subject,
			'gr_id' => $gr_id,
			'bo_skin' => $bo_skin,
			'bo_use_search' => $bo_use_search,
			'bo_order_search' => $bo_order_search
		), array('bo_table' => $bo_table));
	}

	function delete($bo_tables, $ca_types) {
		$this->db->where_in('bo_table', $bo_tables);
		$this->db->delete(array('ki_board', 'ki_board_file', 'ki_write'));

		$this->db->where_in('ca_type', $ca_types);
		$this->db->delete('ki_category');
	}
}
?>