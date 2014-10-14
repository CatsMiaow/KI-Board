<?php
class Comment extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('Board_comt_model');
        $this->load->helper(array('board', 'textual'));
        // $this->output->enable_profiler(TRUE);
        // JSON or XML?
    }

    function index() {
        $bo_table = $this->input->post('bo_table');
        $wr_id    = $this->input->post('wr_id');
        $qstr     = $this->input->post('qstr');
        $page     = $this->input->post('page');

        if (!$bo_table || !$wr_id) return FALSE;

        $board = $this->Basic_model->get_board($bo_table, 'bo_admin,bo_comment_level,bo_use_comment,bo_use_sideview,bo_use_ip_view,bo_skin,bo_page_rows_comt', TRUE);
        $write = $this->Basic_model->get_write($bo_table, $wr_id, 'wr_comment,wr_option,mb_id,wr_ip');
        $member = unserialize(MEMBER);

        if (!$board['bo_use_comment'] || strpos($write['wr_option'], 'nocomt') !== FALSE)
            return FALSE;

        define('IS_ADMIN', is_admin($member, $board));
        $limit = $board['bo_page_rows_comt'];

        // 페이지가 없다면 마지막 페이지
        if ($page < 1)
            $page = $write['wr_comment'] ? ceil($write['wr_comment'] / $limit) : 1;

        $this->load->library('pagination', array(
            'base_url'   => RT_PATH.'/board/'.$bo_table.'/view/wr_id/'.$wr_id.'/',
            'per_page'   => $limit,
            'total_rows' => $write['wr_comment'],
            'cur_page'   => $page,
            'onclick'    => 'comment_list(%n);'
        ));

        // IP 표시
        $is_ip_view = $board['bo_use_ip_view'];
        if (IS_ADMIN) {
            $is_ip_view = TRUE;
            $ip = $write['wr_ip'];
        }
        else // 관리자가 아니라면 IP 주소를 감춘후 보여줍니다.
            $ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", "\\1.♡.\\3.\\4", $write['wr_ip']);

        // 사이드 뷰
        if ($board['bo_use_sideview'])
            $this->load->helper('sideview');
        
        $is_comment_write = FALSE;
        if ($member['mb_level'] >= $board['bo_comment_level'])
            $is_comment_write = TRUE;

        $offset = ($page - 1) * $limit;
        $result = $this->Board_comt_model->list_result($bo_table, $wr_id, $limit, $offset);

        $list = array();
        foreach ($result as $i => $row) {
            $list[$i] = new stdClass();
            
            // 댓글ID 약어
            $list[$i]->comment_id = $row['co_id'];
            $list[$i]->co_reply = $row['co_reply'];
            // 답변 깊이;
            $list[$i]->co_reply_len = strlen($row['co_reply']) * 25;

            $tmp_name = cut_str(get_text($row['co_name']), 10);
            
            $list[$i]->name = $board['bo_use_sideview'] ? get_sideview($row['mb_id'], $tmp_name) : "<span class='".($row['mb_id']?'member':'guest')."'>".$tmp_name."</span>";

            if (strpos($row['co_option'], 'secret') === FALSE || IS_ADMIN ||
                (IS_MEMBER && $write['mb_id'] == $member['mb_id']) ||
                (IS_MEMBER && $row['mb_id'] == $member['mb_id'])) {
                $list[$i]->secret = FALSE;
                $list[$i]->content_s = get_text($row['co_content']);

                $list[$i]->content = conv_content($row['co_content'], FALSE);
            }
            else if (strpos($row['co_option'], 'secret') !== FALSE) {
                $list[$i]->secret = TRUE;
                $list[$i]->content = "<span style='color:#ff6600;'>* 비밀글 입니다.</span>";
                $list[$i]->content_s = '비밀글 입니다.';
            }

            $list[$i]->datetime = substr($row['co_datetime'],2,14);

            // IP 표시
            $list[$i]->ip = '';
            if (IS_ADMIN)
                $list[$i]->ip = $row['co_ip'];
            else if ($is_ip_view && !IS_ADMIN)
                $list[$i]->ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", "\\1.♡.\\3.\\4", $row['co_ip']);
                
            $is_reply = $is_edit = $is_del = FALSE;
            if ($is_comment_write || IS_ADMIN) {
                if (IS_MEMBER) {
                    if ($row['mb_id'] == $member['mb_id'] || IS_ADMIN) {
                        $del_link = "<a href='javascript:;' onclick=\"comment_del('".$row['co_id']."');\">";
                        $is_edit = TRUE;
                        $is_del = TRUE;
                    }
                }
                else {
                    if (!$row['mb_id']) {
                        $del_link = "<a href='javascript:;' onclick=\"del('board/".$bo_table."/password/w/x/comment_id/".$row['co_id'].$qstr."')\">";
                        $is_del = TRUE;
                    }
                }

                if (strlen($row['co_reply']) < 10)
                    $is_reply = TRUE;
            }

            // 답변있는 댓글는 수정, 삭제 불가
            if ($i > 0 && !IS_ADMIN) {
                if ($row['co_reply']) {
                    $tmp_comment_reply = substr($row['co_reply'], 0, strlen($row['co_reply']) - 1);
                    if ($tmp_comment_reply == $be_comment_reply) {
                        $list[$i-1]->btn_edit = FALSE;
                        $list[$i-1]->btn_del = FALSE;
                    }
                }
            }
            // 전 댓글
            $be_comment_reply = $row['co_reply'];

            $list[$i]->btn_reply = !$is_reply ? ''
                : "<a href='javascript:;' onclick=\"comment_box('".$row['co_id']."', 'c');\"><span class='glyphicon glyphicon-comment text-primary' title='답변'></span></a>";
            $list[$i]->btn_edit = !$is_edit ? ''
                : "<a href='javascript:;' onclick=\"comment_box('".$row['co_id']."', 'cu');\"><span class='glyphicon glyphicon-edit text-info' title='수정'></span></a>";
            $list[$i]->btn_del = !$is_del ? ''
                : $del_link."<span class='glyphicon glyphicon-remove text-danger' title='삭제'></span></a>";
        }
        
        $data = array(
            'list'             => $list,
            'bo_table'         => $bo_table,
            'wr_id'            => $wr_id,
            'qstr'             => $qstr,
            'is_comment_write' => $is_comment_write,
            'paging'           => $this->pagination->create_links()
        );
        
        $this->load->view('board/'.$board['bo_skin'].'/view_comment', $data);
    }
}