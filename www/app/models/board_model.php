<?php
class Board_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function list_result($bo_table, $spt, $sca, $sst, $sod, $sfl, $stx, $limit, $offset, $wr_field) {
        $this->db->select($wr_field)->where('bo_table', $bo_table);
        $this->_get_search_cache($sca, $sfl, $stx, $spt);

        if ($sst && $sod)
            $this->db->order_by($sst, $sod);

        return $this->db->get('ki_write', $limit, $offset)->result_array();
    }

    function list_count($bo_table, $spt, $sca, $sfl, $stx) {
        // 인기검색어
        if (trim($stx)) {
            $this->db->simple_query("
                INSERT INTO ki_popular SET
                    pp_word = '".$this->security->xss_clean($stx)."',
                    pp_date = '".TIME_YMD."',
                    pp_ip = '".$this->input->server('REMOTE_ADDR')."'
            ");
        }

        $this->db->where('bo_table', $bo_table);
        $this->_get_search_cache($sca, $sfl, $stx, $spt);

        return $this->db->count_all_results('ki_write');
    }

    // 공지사항
    function list_notice($bo_table, $notice, $wr_field) {
        $key = count($notice)-1;
        if ($notice[$key] == '')
            unset($notice[$key]);

        $this->db->select($wr_field);
        $this->db->where('bo_table', $bo_table)->where_in('wr_id', $notice);
        return $this->db->get('ki_write')->result_array();
    }

    // 검색 구문을 얻는다.
    function _get_search_cache($search_ca_code, $search_field, $search_text, $spt=FALSE) {
        if ($spt)
            $this->db->where("wr_num between '".$spt."' and '".($spt + $this->config->item('cf_search_part'))."'");

        if ($search_ca_code) {
            $code_exp = explode('.', $search_ca_code);
            if (!isset($code_exp[1]))
                $limit_code = $search_ca_code + 1;
            else {
                $code_ori = substr($code_exp[1], 0, -3);
                $code_num = substr($code_exp[1], -3) + 1;
                $code_plus = repeater('0', 3-strlen($code_num)).$code_num;
                $limit_code = $code_exp[0].'.'.$code_ori.$code_plus;
            }
            $this->db->where(array(
                'ca_code >=' => $search_ca_code,
                'ca_code <' => $limit_code
            ));
        }

        if (!$search_field || !$search_text)
            return FALSE;

        // 검색어를 구분자로 나눈다. 여기서는 공백
        $s = explode(' ', $search_text);

        // 검색필드를 구분자로 나눈다.
        $field = explode('.', trim($search_field));

        $opt = '';
        $where = ' ( ';
        foreach ($s as $sval) {
            // 검색어
            $search_str = trim($sval);
            if ($search_str == '')
                continue;
            
            $opt2 = '';
            $where .= $opt.'(';
            foreach ($field as $fval) {  // 필드의 수만큼 다중 필드 검색 (필드1+필드2...)
                $where .= $opt2;
                switch ($fval) {
                    case 'mb_id' :
                    case 'wr_name' :
                        $where .= $this->db->protect_identifiers($fval).' = '.$this->db->escape($search_str);
                    break;
                    // LIKE 보다 INSTR 속도가 빠름 (누가 그래?)
                    default :
                        if (preg_match('/[a-zA-Z]/', $search_str))
                            $where .= 'INSTR(LOWER('.$this->db->protect_identifiers($fval).'), LOWER('.$this->db->escape($search_str).'))';
                        else
                            $where .= 'INSTR('.$this->db->protect_identifiers($fval).', '.$this->db->escape($search_str).')';
                    break;
                }
                $opt2 = ' or ';
            }
            $where .= ')';
            $opt = ' and ';
        }
        $where .= ' ) ';
        $this->db->where($where, null, FALSE);
    }

    // 원본글 작성자 인가. 비밀글시.
    function is_owner($bo_table, $wr_num) {
        $this->db->select('mb_id');
        return $this->db->get_where('ki_write', array(
            'bo_table' => $bo_table,
            'wr_num' => $wr_num,
            'wr_reply' => ''
        ))->row_array();
    }

    // 조회수 증가
    function hit_update($bo_table, $wr_id) {
        $this->db->set('wr_hit', 'wr_hit + 1', FALSE);
        $this->db->update('ki_write', null, array('bo_table' => $bo_table, 'wr_id' => $wr_id));
    }

    // 다음, 이전 글 링크
    function prev_next_link($bo_table, $wr_num, $wr_reply, $sca, $sfl, $stx) {
        $this->db->start_cache(); // S
        $this->db->select('wr_id, wr_subject')->where('bo_table', $bo_table);
        $this->db->stop_cache(); // E

        // 이전글 답변글
        $this->db->where(array('wr_num' => $wr_num, 'wr_reply <' => $wr_reply));
        $this->_get_search_cache($sca, $sfl, $stx);
        $this->db->order_by('wr_num desc, wr_reply desc');
        $prev = $this->db->get('ki_write', 1)->row_array();

        // 이전 답변글이 없다면 이전글
        if (!isset($prev['wr_id'])) {
            $this->db->where('wr_num <', $wr_num);
            $this->_get_search_cache($sca, $sfl, $stx);
            $this->db->order_by('wr_num desc, wr_reply desc');
            $prev = $this->db->get('ki_write', 1)->row_array();
        }
        
        // 다음글 답변글
        $this->db->where(array('wr_num' => $wr_num, 'wr_reply >' => $wr_reply));
        $this->_get_search_cache($sca, $sfl, $stx);
        $this->db->order_by('wr_num desc, wr_reply desc');
        $next = $this->db->get('ki_write', 1)->row_array();

        // 다음 답변글이 없다면 다음글
        if (!isset($next['wr_id'])) {
            $this->db->where('wr_num >', $wr_num);
            $this->_get_search_cache($sca, $sfl, $stx);
            $this->db->order_by('wr_num, wr_reply');
            $next = $this->db->get('ki_write', 1)->row_array();
        }

        $this->db->flush_cache();

        $result['prev'] = ($prev) ? $prev : FALSE;
        $result['next'] = ($next) ? $next : FALSE;

        return $result;
    }
    
    // 관련 답변 존재 여부
    function is_reply($bo_table, $wr_id, $wr_num, $wr_reply) {
        $len = strlen($wr_reply);
        $len = ($len < 0) ? 0 : $len;
        $reply = substr($wr_reply, 0, $len);

        $this->db->where('bo_table', $bo_table)->like('wr_reply', $reply, 'after');
        $this->db->where(array(
            'wr_id <>' => $wr_id,
            'wr_num' => $wr_num
        ));

        return ($this->db->count_all_results('ki_write') > 0) ? TRUE : FALSE;
    }
    
    // 관련 댓글 존재 여부
    function is_comment($bo_table, $wr_id, $mb_id) {
        $where = array(
            'bo_table' => $bo_table,
            'wr_id' => $wr_id
        );
        if ($mb_id)
            $where['mb_id <>'] = $mb_id;

        $this->db->where($where);
        return $this->db->count_all_results('ki_comment');
    }
    
    // 답변 단계 얻기
    function get_reply_step($bo_table, $wr_num, $bo_reply_order, $wr_reply) {
        $reply_len = strlen($wr_reply) + 1;

        if ($bo_reply_order) {
            $begin_reply_char = 'A';
            $end_reply_char = 'Z';
            $reply_number = +1;
    
            $this->db->select_max(' SUBSTRING(wr_reply, '.$reply_len.', 1) ', 'reply');
        }
        else {
            $begin_reply_char = 'Z';
            $end_reply_char = 'A';
            $reply_number = -1;

            $this->db->select_min(' SUBSTRING(wr_reply, '.$reply_len.', 1) ', 'reply');
        }

        $this->db->where(array(
            'bo_table' => $bo_table,
            'wr_num' => $wr_num,
            'SUBSTRING(wr_reply, '.$reply_len.', 1) <>' => ''
        ));

        if ($wr_reply)
            $this->db->like('wr_reply', $wr_reply, 'after');

        $row = $this->db->get('ki_write')->row_array();

        if (!isset($row['reply']))
            $reply_char = $begin_reply_char;
        else if ($row['reply'] == $end_reply_char) // A~Z은 26 입니다.
            alert("더 이상 답변하실 수 없습니다.\\n\\n답변은 26개 까지만 가능합니다.");
        else
            $reply_char = chr(ord($row['reply']) + $reply_number);

        return $wr_reply.$reply_char;
    }

    // 동일내용 연속 등록 불가
    function same_write($bo_table) {
        $this->db->select('MD5(CONCAT(wr_ip, wr_subject, wr_content)) as prev_md5', FALSE);
        $this->db->order_by('wr_id', 'desc');
        return $this->db->get_where('ki_write', array(
            'bo_table' => $bo_table
        ), 1)->row_array();
    }
    
    // 게시판의 최소 wr_num을 얻는다.
    function get_min_num($bo_table) {
        // 가장 작은 번호를 얻어
        $this->db->select_min('wr_num', 'min_wr_num');
        $row = $this->db->get_where('ki_write', array(
            'bo_table' => $bo_table
        ))->row_array();
        
        return $row['min_wr_num'];
    }
    
    function write_insert($bo_table, $wr_content, $wr_num, $wr_reply, $mb, $bo_notice) {
        $wr_option = array($this->input->post('editor'),$this->input->post('secret'),$this->input->post('mail'),$this->input->post('nocomt'));

        $this->db->insert('ki_write', array(
			'bo_table'    => $bo_table,
			'wr_num'      => $wr_num,
			'wr_reply'    => $wr_reply,
			'wr_comment'  => '0',
			'ca_code'     => ($this->input->post('ca_code')) ? str_replace('-', '.', $this->input->post('ca_code')) : '',
			'wr_option'   => implode(',', array_filter($wr_option)),
			'wr_subject'  => $this->input->post('wr_subject'),
			'wr_content'  => $wr_content,
			'wr_hit'      => '0',
			'mb_id'       => $mb['mb_id'],
			'wr_password' => $mb['wr_password'],
			'wr_name'     => $mb['wr_name'],
			'wr_email'    => $mb['wr_email'],
			'wr_datetime' => TIME_YMDHIS,
			'wr_last'     => TIME_YMDHIS,
			'wr_ip'       => $this->input->server('REMOTE_ADDR')
        ));
        
        $wr_id = $this->db->insert_id();
        
        // 게시글 1 증가
        $this->db->set('bo_count_write', 'bo_count_write + 1', FALSE);

        $sql = array();
        if ($this->input->post('w') == '') {
            if ($this->input->post('notice'))
                $sql['bo_notice'] = trim($wr_id.','.$bo_notice);

            $sql['bo_min_wr_num'] = $wr_num;
        }

        $this->db->update('ki_board', $sql, array('bo_table' => $bo_table));
        
        return $wr_id;
    }
    
    function write_update($bo_table, $wr_content, $wr_id, $mb, $bo_notice) {
        $ca_code = ($this->input->post('ca_code')) ? str_replace('-', '.', $this->input->post('ca_code')) : '';
        $wr_option = array($this->input->post('editor'),$this->input->post('secret'),$this->input->post('mail'),$this->input->post('nocomt'));
        
        $sql = array(
            'ca_code'    => $ca_code,
            'wr_option'  => implode(',', array_filter($wr_option)),
            'wr_subject' => $this->input->post('wr_subject'),
            'wr_content' => $wr_content,
            'mb_id'      => $mb['mb_id'],
            'wr_name'    => $mb['wr_name'],
            'wr_email'   => $mb['wr_email']
        );
        
        if ($this->input->post('wr_password')) {
            $this->load->library('encrypt');            
            $sql['wr_password'] = $this->encrypt->encode($this->input->post('wr_password'));
        }
        
        if (!IS_ADMIN) {
            $sql['wr_last'] = TIME_YMDHIS;
            $sql['wr_ip']    = $this->input->server('REMOTE_ADDR');
        }
    
        $this->db->update('ki_write', $sql, array('bo_table' => $bo_table, 'wr_id' => $wr_id));
    
        // 분류가 수정되는 경우 해당되는 댓글의 분류명도 모두 수정함
        // 댓글의 분류를 수정하지 않으면 검색이 제대로 되지 않는다.
        if ($ca_code) 
            $this->db->update('ki_comment', array('ca_code' => $ca_code), array('bo_table' => $bo_table, 'wr_id' => $wr_id));
        
        // 공지사항
        if (IS_ADMIN)
            $this->db->update('ki_board', array('bo_notice' => trim($bo_notice)), array('bo_table' => $bo_table));
    }
    
    function write_delete($bo_table, $wr_ids, $bo_notice, $bo_min_wr_num, $bo_extra) {
        if (!$wr_ids) return FALSE;
        
        // 게시물 파일 삭제    
        $this->db->select('bf_file');
        $this->db->where('bo_table', $bo_table)->where_in('wr_id', $wr_ids);
        $result = $this->db->get('ki_board_file')->result_array();
        
        foreach ($result as $row) {
            @unlink(DATA_PATH.'/file/'.$bo_table.'/'.$row['bf_file']);
        }
        
        // 게시물, 파일테이블 삭제
        $this->db->where('bo_table', $bo_table)->where_in('wr_id', $wr_ids);
        $this->db->delete(array('ki_write', 'ki_board_file'));
        
        // 댓글 삭제
        $this->db->where('bo_table', $bo_table)->where_in('wr_id', $wr_ids);
        $this->db->delete('ki_comment');

        $count_write = count($wr_ids);
        $count_comment = $this->db->affected_rows(); // 마지막 쿼리(댓글)의 결과 행 개수
        
        // 글숫자 감소
        if ($count_write > 0 || $count_comment > 0) {
            $this->db->set('bo_count_write', 'bo_count_write - '.$count_write, FALSE);
            $this->db->set('bo_count_comment', 'bo_count_comment - '.$count_comment, FALSE);
        }
        
        $sql = array();
        if (IS_ADMIN)
            $sql['bo_notice'] = trim($bo_notice);
        
        // min_wr_num 업데이트
        $min_wr_num = $this->get_min_num($bo_table);
        if ($min_wr_num != $bo_min_wr_num)
            $sql['bo_min_wr_num'] = $min_wr_num;
        
        $this->db->update('ki_board', $sql, array('bo_table' => $bo_table));

        // 확장테이블
        if ($bo_extra) {
            $this->db->where_in('wr_id', $wr_ids);
            $this->db->delete('ki_extra_'.$bo_table);
        }
    }

    function content_update($bo_table, $wr_id, $content) {
        $this->db->update('ki_write', array(
            'wr_content' => $content
        ), array('bo_table' => $bo_table, 'wr_id' => $wr_id));
    }

    function get_extra($bo_table, $wr_id) {
        if (!is_array($wr_id)) {
            return $this->db->get_where('ki_extra_'.$bo_table, array(
                'wr_id' => $wr_id
            ))->row_array();
        }
        else {
            $this->db->where_in('wr_id', $wr_id);
            return $this->db->get('ki_extra_'.$bo_table)->result_array();
        }
    }

    function extra_update($w, $bo_table, $wr_id, $sql) {
        if ($w == 'u')
            $this->db->update('ki_extra_'.$bo_table, $sql, array('wr_id' => $wr_id));
        else {
            $sql['wr_id'] = $wr_id;
            $this->db->insert('ki_extra_'.$bo_table, $sql);
        }
    }
}
?>