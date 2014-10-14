<?php
class Admin extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->helper('admin');
    }
    
    function index() {
        $bo_table = $this->input->post('bo_table');
        if (!IS_MEMBER || !$bo_table)
            show_404();
            
        $board = $this->Basic_model->get_board($bo_table);
        if (!isset($board['bo_table']))
            alert_close('존재하지 않은 게시판입니다.');

        $member = unserialize(MEMBER);
        if ($member['mb_id'] != $board['bo_admin'])
            show_404();
        
        $config = array(
            array('field'=>'bo_table',   'label'=>'TABLE',         'rules'=>'trim|required|min_length[3]|max_length[20]|alpha_dash|xss_clean'),
            array('field'=>'token',      'label'=>'토큰',          'rules'=>'trim|required'),
            array('field'=>'bo_subject', 'label'=>'게시판 제목',   'rules'=>'trim|required|max_length[20]|xss_clean'),
            array('field'=>'bo_admin',   'label'=>'게시판 관리자', 'rules'=>'trim|min_length[3]|max_length[20]|alpha_dash')
        );
        
        $this->load->library('form_validation');
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $head = array('title' => $board['bo_subject']);
            $data = array(
                'token'             => get_token(),
                'bo_table'          => $board['bo_table'],
                'bo_admin'          => $board['bo_admin'],
                'bo_subject'        => $board['bo_subject'],
                'bo_insert_content' => $board['bo_insert_content'],
                'bo_sort_field'     => $board['bo_sort_field'],
                
                'bo_count_write'   => (isset($board['bo_count_write'])) ? number_format($board['bo_count_write']) : FALSE,
                'bo_count_comment' => (isset($board['bo_count_comment'])) ? number_format($board['bo_count_comment']) : FALSE,

                'bo_count_delete'   => $board['bo_count_delete'],
                'bo_count_modify'   => $board['bo_count_modify'],
                'bo_use_secret'     => $board['bo_use_secret'],
                'bo_page_rows'      => $board['bo_page_rows'],
                'bo_page_rows_comt' => $board['bo_page_rows_comt'],
                'bo_subject_len'    => $board['bo_subject_len'],
                'bo_new'            => $board['bo_new'],
                'bo_hot'            => $board['bo_hot'],
                'bo_image_width'    => $board['bo_image_width'],
                'bo_reply_order'    => $board['bo_reply_order'],

                'use_private_chk'   => ($board['bo_use_private']) ? "checked='checked'" : '',
                'use_rss_chk'       => ($board['bo_use_rss']) ? "checked='checked'" : '',
                'use_sns_chk'       => ($board['bo_use_sns']) ? "checked='checked'" : '',
                'use_comment_chk'   => ($board['bo_use_comment']) ? "checked='checked'" : '',
                'use_category_chk'  => ($board['bo_use_category']) ? "checked='checked'" : '',
                'use_sideview_chk'  => ($board['bo_use_sideview']) ? "checked='checked'" : '',
                'use_editor_chk'    => ($board['bo_use_editor']) ? "checked='checked'" : '',
                'use_name_chk'      => ($board['bo_use_name']) ? "checked='checked'" : '',
                'use_ip_view_chk'   => ($board['bo_use_ip_view']) ? "checked='checked'" : '',
                'use_list_view_chk' => ($board['bo_use_list_view']) ? "checked='checked'" : '',
                'use_email_chk'     => ($board['bo_use_email']) ? "checked='checked'" : '',
                'use_syntax_chk'    => ($board['bo_use_syntax']) ? "checked='checked'" : '',
                'use_search_chk'    => ($board['bo_use_search']) ? "checked='checked'" : '',

                'bo_list_level'     => get_mb_level_select('bo_list_level', $board['bo_list_level'], '', $member['mb_level']),
                'bo_read_level'     => get_mb_level_select('bo_read_level', $board['bo_read_level'], '', $member['mb_level']),
                'bo_write_level'    => get_mb_level_select('bo_write_level', $board['bo_write_level'], '', $member['mb_level']),
                'bo_reply_level'    => get_mb_level_select('bo_reply_level', $board['bo_reply_level'], '', $member['mb_level']),
                'bo_comment_level'  => get_mb_level_select('bo_comment_level', $board['bo_comment_level'], '', $member['mb_level']),
                'bo_upload_level'   => get_mb_level_select('bo_upload_level', $board['bo_upload_level'], '', $member['mb_level']),
                'bo_download_level' => get_mb_level_select('bo_download_level', $board['bo_download_level'], '', $member['mb_level'])
            );

            widget::run('head', $head);
            $this->load->view('board/admin', $data);
            widget::run('tail');
        }
        else {
            check_token();

            // 이것을 Model로 해야 하는가 말아야 하는가
            $this->db->update('ki_board', array(
                'bo_subject'        => $this->input->post('bo_subject'),
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
                'bo_use_editor'     => $this->input->post('bo_use_editor'),
                'bo_use_name'       => $this->input->post('bo_use_name'),
                'bo_use_ip_view'    => $this->input->post('bo_use_ip_view'),
                'bo_use_list_view'  => $this->input->post('bo_use_list_view'),
                'bo_use_email'      => $this->input->post('bo_use_email'),
                'bo_use_syntax'     => $this->input->post('bo_use_syntax'),
                'bo_subject_len'    => $this->input->post('bo_subject_len'),
                'bo_page_rows'      => $this->input->post('bo_page_rows'),
                'bo_page_rows_comt' => $this->input->post('bo_page_rows_comt'),
                'bo_new'            => $this->input->post('bo_new'),
                'bo_hot'            => $this->input->post('bo_hot'),
                'bo_image_width'    => $this->input->post('bo_image_width'),
                'bo_reply_order'    => $this->input->post('bo_reply_order'),
                'bo_sort_field'     => $this->input->post('bo_sort_field'),
                'bo_insert_content' => $this->input->post('bo_insert_content'),
                'bo_use_search'     => $this->input->post('bo_use_search')
            ), array('bo_table' => $bo_table));

            alert_close('게시판 설정이 변경되었습니다.');
        }
    }
    
    function category() {
        $bo_table = $this->input->post('bo_table');
        if (!IS_MEMBER || !$bo_table)
            show_404();

        $bo = $this->Basic_model->get_board($bo_table, 'bo_table,bo_admin,bo_subject');
        if (!isset($bo['bo_table']))
            alert_close('존재하지 않는 게시판 입니다.');

        $member = unserialize(MEMBER);
        if ($member['mb_id'] != $bo['bo_admin'])
            show_404();

        define('CSS_SKIN', 'category');

        $type = 'bo_'.$bo_table;
        $this->load->model('Categoryform_model');
        $bc = $this->Categoryform_model->list_result($type);

        $code_html = FALSE;
        if ($bc) {
            $t_code = $s_code = array();
            foreach ($bc as $row) {
                $code_exp = explode('-', $row['code']);
                
                if (!isset($code_exp[1]))
                    $t_code[$code_exp[0]] = $row['ca_name'];
                else
                    $s_code[$code_exp[0]][$code_exp[1]] = $row['ca_name'];
            }
            
            $this->load->helper('categoryform');
            $code_html = get_categoryform($t_code, $s_code);
        }

        $head = array('title' => $bo['bo_subject']);
        $data = array(
            'bo_table'  => $bo_table,
            'type'      => $type,
            'code_html' => $code_html
        );

        widget::run('head', $head);
        $this->load->view('board/admin_category', $data);
        widget::run('tail');
    }
 }
 ?>