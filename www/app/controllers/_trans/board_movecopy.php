<?php
class Board_movecopy extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('Board_mvcp_model');
        $this->load->config('cf_board');
        $this->load->helper('board');
        // $this->output->enable_profiler(TRUE);
    }    
    
    function update() {
          $bo_table = $this->input->post('bo_table'); // 원본 게시판
          $bo_tables = $this->input->post('bo_tables'); // 대상 게시판
        $wr_ids = unserialize($this->input->post('wr_ids'));
        $sw = $this->input->post('sw');

        $board = $this->Basic_model->get_board($bo_table, 'bo_subject, bo_admin', TRUE);
        $member = unserialize(MEMBER);

        define('IS_ADMIN', is_admin($member, $board));

        // 게시판 관리자 이상 복사, 이동 가능
        if (!IS_ADMIN)
             show_404();

        if (!$wr_ids)
            alert_close('잘못된 접근입니다.');

        switch ($sw) {
            case 'move' : $act = '이동'; break;
            case 'copy' : $act = '복사'; break;
            default: alert_close('잘못된 접근입니다.'); break;
        }

        $base_url = $this->config->item('base_url');
        
        // 원본 파일 디렉토리
        $ori_dir = DATA_PATH.'/file/'.$bo_table;
        
        $save = array(); // 이동시 삭제를 위한

        // 해당 게시물의 답글까지 모두 가져와
        $wr_nums = $this->Board_mvcp_model->get_dist_num($bo_table, $wr_ids);
        $result =  $this->Board_mvcp_model->get_write_num($bo_table, $wr_nums);
        
        foreach ($bo_tables as $move_bo_table) {
            $sub_dir = DATA_PATH.'/file/'.$move_bo_table;
            
            $count_write = 0;
            $count_comment = 0;
            
            $next_wr_num = $this->Board_mvcp_model->get_min_num($move_bo_table);

            $tmp_num = FALSE; $wr_ids = array();
            foreach ($result as $cnt => $wr) { // 불필요하게 게시판별로 for문을 돔
                if ($this->config->item('cf_use_mvcp_log'))
                    $wr['wr_content'] .= (strpos($wr['wr_option'], 'editor') !== FALSE ? '<br/>' : '\n').'[ 이 게시물은 '.$member['mb_nick'].'님에 의해 '.TIME_YMDHIS.' '.$board['bo_subject'].'에서 ' .$act.' 됨]';
          
                  // 다음 wr_num 마다 -1 씩 증가
                  if ($tmp_num != $wr['wr_num'])
                      $next_wr_num = (int)($next_wr_num - 1);
                  
                  $tmp_num = $wr['wr_num'];
                  
                // 해당 게시판으로 글 Insert                    
                  $insert_id = $this->Board_mvcp_model->write_insert($move_bo_table, $next_wr_num, $wr);

                  $save[$cnt]['bf_file'] = array();
                    
                // 파일이 있다면
                if ($wr['wr_count_image'] > 0 || $wr['wr_count_file'] > 0) {
                    // 해당 게시물의 파일 정보를 가져와
                    $result3 = $this->Board_mvcp_model->get_write_file($bo_table, $wr['wr_id']);
                    foreach ($result3 as $k => $bf) {
                        if ($bf['bf_file']) {
                            // 원본파일을 복사하고 퍼미션을 변경
                            @copy($ori_dir.'/'.$bf['bf_file'], $sub_dir.'/'.$bf['bf_file']);
                            @chmod($sub_dir.'/'.$bf['bf_file'], 0606);
                        }
                        
                        // 해당 게시판으로 파일 Insert
                        $this->Board_mvcp_model->write_file_insert($move_bo_table, $insert_id, $bf);
                        
                        // 이동시 삭제를 위한 파일 정보 저장
                        if ($sw == 'move' && $bf['bf_file'])
                            $save[$cnt]['bf_file'][$k] = $ori_dir.'/'.$bf['bf_file'];
                    }

                    $this->Board_mvcp_model->content_update($move_bo_table, $insert_id, str_replace(
                        array($base_url.'/data/file/'.$bo_table,
                              $base_url.'/board/'.$bo_table.'/download/wr_id/'.$wr['wr_id']),
                        array($base_url.'/data/file/'.$move_bo_table,
                              $base_url.'/board/'.$move_bo_table.'/download/wr_id/'.$insert_id),
                    $wr['wr_content']));
                }

                $count_write++;
                $count_comment += $wr['wr_comment'];

                // 이동시 삭제와 댓글을 위한 원글 저장
                // 처음 넘겨온 글배열과 다를 수 있음 (답글을 추가로 얻어오므로))
                $wr_ids[$insert_id] = $wr['wr_id'];
            }

            // 댓글 처리
            foreach ($wr_ids as $new_id => $old_id) {
                $result2 = $this->Board_mvcp_model->get_comment($bo_table, $old_id);

                $tmp_num = FALSE; $next_co_num = 0;
                foreach ($result2 as $co) {
                    // 다음 co_num 마다 +1 씩 증가
                    if ($tmp_num != $co['co_num'])
                        $next_co_num = (int)($next_co_num + 1);
                    
                    $tmp_num = $co['co_num'];
                    
                    // 해당 게시판으로 댓글 Insert
                    $this->Board_mvcp_model->comment_insert($move_bo_table, $new_id, $next_co_num, $co);
                }
            }

            // 카운트, wr_num
            $this->Board_mvcp_model->bo_count_update($move_bo_table, $count_write, $count_comment, $next_wr_num, '+');
        }
        
        if ($sw == 'move') {
            // SAVE 처리
            foreach ($save as $row) {
                foreach ($row['bf_file'] as $bf_file) {
                    @unlink($bf_file);
                }
            }
            $this->Board_mvcp_model->write_delete($bo_table, $wr_ids);
            
            $bo_min_wr_num = $this->Board_mvcp_model->get_min_num($bo_table);
            $this->Board_mvcp_model->bo_count_update($bo_table, $count_write, $count_comment, $bo_min_wr_num, '-');
        }
        
        $msg = '해당 게시물을 선택한 게시판으로 '.$act.' 하였습니다.';
        $opener_href = RT_PATH.'/board/'.$bo_table;
        
        echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=".$this->config->item('charset')."\">";
        echo "<script type='text/javascript'>alert('".$msg."'); opener.document.location.href='".$opener_href."'; window.close();</script>";
    }
}