<?php
class Board extends CI_Controller {
    public $board;  // 게시판 정보
    public $member; // 회원 정보
    public $write;  // 게시물 정보
    public $seg;    // Segment 정보
    public $param;  // Querystring 정보

    function __construct() {
        parent::__construct();
        $this->load->model('Board_model');
        $this->load->helper(array('board', 'textual'));
        $this->load->config('cf_board');
        // $this->output->enable_profiler(TRUE);
    }

    function _remap($bo_table) {
        if ($bo_table == 'index')
            goto_url('/');
            
        $wr_field = $css_skin = '';
        $bo_style = $this->uri->segment(3, 'lists');
        switch ($bo_style) {
            case 'lists':
                $bo_field = ' bo_table, bo_subject, bo_admin, bo_list_level, bo_write_level,
                              bo_use_private, bo_use_rss, bo_use_category, bo_use_sideview, bo_subject_len,
                              bo_page_rows, bo_new, bo_hot, bo_skin, bo_sort_field, bo_head, bo_tail,
                              bo_use_extra, bo_count_write, bo_notice, bo_min_wr_num ';
                $li_field = ' wr_id, wr_reply, wr_comment, ca_code, wr_option, wr_subject, wr_content,
                              wr_hit, mb_id, wr_name, wr_datetime, wr_last, wr_count_file, wr_count_image ';
            break;
            case 'view':
                $bo_field = ' bo_table, bo_subject, bo_admin,
                              bo_list_level, bo_read_level, bo_write_level, bo_reply_level, bo_comment_level,
                              bo_use_private, bo_use_sns, bo_use_comment, bo_use_sideview, bo_use_ip_view, bo_use_list_view,
                              bo_use_extra, bo_use_syntax, bo_new, bo_hot, bo_image_width, bo_skin, bo_head, bo_tail ';
                $wr_field = ' wr_id, wr_num, wr_reply, wr_comment, wr_option, wr_subject, wr_content, wr_hit,
                              mb_id, wr_name, wr_datetime, wr_last, wr_ip, wr_count_file, wr_count_image ';
                // list_view
                $bo_field .= ', bo_use_rss, bo_use_category, bo_subject_len, bo_page_rows,
                                bo_count_write, bo_notice, bo_sort_field, bo_min_wr_num ';
                $li_field = ' wr_id, wr_reply, wr_comment, ca_code, wr_option, wr_subject, wr_content, wr_hit,
                              mb_id, wr_name, wr_datetime, wr_last, wr_count_file, wr_count_image ';
            break;
            case 'write':
                $css_skin = 'editor';
                $bo_field = ' bo_table, bo_subject, bo_admin,
                              bo_write_level, bo_reply_level, bo_upload_level, bo_count_modify,
                              bo_use_private, bo_use_comment, bo_use_category, bo_use_secret, bo_use_editor, bo_use_email,
                              bo_use_extra, bo_use_syntax, bo_image_width, bo_skin, bo_head, bo_tail,
                              bo_reply_order, bo_upload_ext, bo_upload_size, bo_insert_content, bo_notice ';
                $wr_field = ' wr_id, wr_num, wr_reply, ca_code, wr_option, wr_subject, wr_content,
                              mb_id, wr_password, wr_name, wr_email, wr_count_file, wr_count_image ';
            break;
            case 'editor':
                $css_skin = 'swfupload';
                $bo_field = ' bo_table, bo_admin, bo_upload_level, bo_upload_ext, bo_upload_size ';
            break;
            case 'password':
                $bo_field = ' bo_table, bo_admin, bo_head, bo_tail ';
            break;
            case 'download':
                $bo_field = ' bo_table, bo_admin, bo_download_level ';
            break;
            case 'rss':
                $bo_field = 'bo_table, bo_subject, bo_admin, bo_read_level, bo_use_rss, bo_use_category ';
            break;
            default:
                alert('잘못된 접근입니다.', '/');
            break;
        }
        
        $board = $this->Basic_model->get_board($bo_table, $bo_field, TRUE);
        if (!isset($board['bo_table']))
           alert('존재하지 않는 게시판입니다.', '/');

        define('BO_TABLE', $board['bo_table']);
        define('BO_HEAD', isset($board['bo_head']) ? $board['bo_head'] : FALSE);
        define('BO_TAIL', isset($board['bo_tail']) ? $board['bo_tail'] : FALSE);

        $this->load->library('segment', NULL, 'seg'); // 세그먼트 주소
        $this->load->library('querystring', NULL, 'param'); // 쿼리스트링 주소
        
        $this->member = unserialize(MEMBER);
        $this->board  = $board;
        $this->write  = ($wr_field) ? $this->Basic_model->get_write(BO_TABLE, $this->seg->get('wr_id'), $wr_field) : FALSE;
        if (isset($li_field))
            $this->wr_field = $li_field;

        if ($bo_style == 'view' && $board['bo_use_syntax'])
            $css_skin .= 'syntax';

        if ($css_skin) define('CSS_SKIN', $css_skin);
        define('IS_ADMIN', is_admin($this->member, $board));
        widget::run('_board/'.$bo_style);
    }
}