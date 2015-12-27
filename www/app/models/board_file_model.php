<?php
class Board_file_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    // 파일 Insert 및 Count++
    function file_insert($bo_table, $wr_id, $values, $editor=0) {
        if (!$values) return FALSE;
        
        $this->db->insert_batch('ki_board_file', $values);

        $cnt = $this->db->affected_rows();
        if ($cnt > 0)
            $this->file_count_update($bo_table, $wr_id, '+ '.$cnt, $editor);
    }
    
    // 개별파일 삭제
    function file_delete($bo_table, $wr_id, $bf_nos, $editor=0) {
        if (!$bf_nos) return FALSE;
        
        $this->db->where(array('bo_table' => $bo_table, 'wr_id' => $wr_id, 'bf_editor' => $editor));
        $this->db->where_in('bf_no', $bf_nos);
        $this->db->delete('ki_board_file');
        
        $cnt = $this->db->affected_rows();
        if ($cnt > 0)
            $this->file_count_update($bo_table, $wr_id, '- '.$cnt, $editor);
    }
    
    function file_count_update($bo_table, $wr_id, $cnt, $editor) {
        if ($editor) 
            $this->db->set('wr_count_image', 'wr_count_image '.$cnt, FALSE);
        else
            $this->db->set('wr_count_file', 'wr_count_file '.$cnt, FALSE);
            
        $this->db->update('ki_write', null, array('bo_table' => $bo_table, 'wr_id' => $wr_id));
    }
    
    // 게시물 파일정보
    function get_files($bo_table, $wr_id, $field='*', $editor=0) {
        $sql = array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'bf_editor' => $editor
        );
        if (is_string($editor) && $editor == 'all') {
            $order = 'bf_editor';
            unset($sql['bf_editor']);
        }
        else
            $order = 'bf_no';
        
        $this->db->select($field);
        $this->db->order_by($order);    
        $qry = $this->db->get_where('ki_board_file', $sql);
        return $qry->result_array();
    }
    
    // 개별 파일정보
    function get_file($bo_table, $wr_id, $bf_no) {
        $this->db->select('bf_source, bf_file');
        $qry = $this->db->get_where('ki_board_file', array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'bf_editor' => 0,
            'bf_no' => $bf_no
        ));
        return $qry->row_array();
    }
    
    // 다운로드 카운트++
    function file_down_update($bo_table, $wr_id, $bf_no) {
        $this->db->set('bf_download', 'bf_download + 1', FALSE);
        $this->db->update('ki_board_file', null, array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id,
            'bf_editor' => 0,
            'bf_no' => $bf_no
        ));
    }
}
?>