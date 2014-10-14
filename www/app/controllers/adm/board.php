<?php
class Board extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model(ADM_F.'/Board_model');
        $this->load->helper('admin');
        define('WIDGET_SKIN', 'admin');
    }
    
    function lists() {
        $this->load->library(array('pagination', 'querystring'));

         $param =& $this->querystring;
        $page = $this->uri->segment(5, 1);
        $sst  = $param->get('sst', 'gr_id');
        $sod  = $param->get('sod', 'asc');
        $sfl  = $param->get('sfl');
        $stx  = $param->get('stx');

        $config['suffix'] = $param->output();
        $config['base_url'] = RT_PATH.'/'.ADM_F.'/board/lists/page/';
        $config['per_page'] = 15;

        $offset = ($page - 1) * $config['per_page'];            
        $result = $this->Board_model->list_result($sst, $sod, $sfl, $stx, $config['per_page'], $offset);

        $config['total_rows'] = $result['total_cnt'];
        $this->pagination->initialize($config);

        $list = array();
        $token = get_token();
        foreach ($result['qry'] as $i => $row) {
            $list[$i] = new stdClass();
            $list[$i]->table = $row['bo_table'];
            $list[$i]->subject = $row['bo_subject'];
            $list[$i]->order_search = $row['bo_order_search'];
            $list[$i]->use_chk = ($row['bo_use_search']) ? "checked='checked'" : '';
            $list[$i]->group = get_group_select("gr_id[".$row['bo_table']."]", $row['gr_id'], TRUE);
            $list[$i]->skin = get_skin_dir('board', "bo_skin[".$row['bo_table']."]", $row['bo_skin'], TRUE);
            $list[$i]->s_mod = icon('수정', 'board/form/u/'.$row['bo_table']);
            $list[$i]->s_del = icon('삭제', "javascript:post_send('".ADM_F."/_trans/board/delete', {bo_table:'".$row['bo_table']."', token:'".$token."'}, true);");
        }

        $head = array('title' => '게시판관리');
        $data = array(
            'token' => $token,

            'list' => $list,
            's_add' => icon('작성', 'board/form'),

            'sfl' => $sfl,
            'stx' => $stx,        

            'total_cnt' => number_format($result['total_cnt']),
            'paging' => $this->pagination->create_links(),

            'sort_bo_table' => $param->sort('bo_table', 'desc'),
            'sort_bo_subject' => $param->sort('bo_subject'),
            'sort_bo_use_search' => $param->sort('bo_use_search'),
            'sort_bo_order_search' => $param->sort('bo_order_search'),
            'sort_gr_id' => $param->sort('gr_id'),
            'sort_bo_skin' => $param->sort('bo_skin', 'desc')
        );

        widget::run('head', $head);
        $this->load->view(ADM_F.'/board_list', $data);
        widget::run('tail');
    }
    
    function form($w='', $bo_table='') {
        $this->load->library('form_validation');

        $config = array(
            array('field'=>'bo_table', 'label'=>'TABLE', 'rules'=>'trim|required|min_length[3]|max_length[20]|alpha_dash|xss_clean'),
            array('field'=>'gr_id', 'label'=>'게시판 그룹', 'rules'=>'trim|required|min_length[3]|max_length[20]|alpha_dash'),
            array('field'=>'bo_subject', 'label'=>'게시판 제목', 'rules'=>'trim|required|max_length[20]'),
            array('field'=>'bo_admin', 'label'=>'게시판 관리자', 'rules'=>'trim|min_length[3]|max_length[20]|alpha_dash')
        );

        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            if (!$this->Board_model->is_group())
                alert('게시판그룹이 한개 이상 생성되어야 합니다.', ADM_F.'/boardgroup/form');

            if ($w == '' || $w != 'u') {
                $title = '생성';
                $board = array_false(array(
                    'bo_table', 'bo_subject', 'bo_admin', 'bo_head', 'bo_tail', 'bo_sort_field', 'bo_insert_content',
                    'bo_use_private', 'bo_use_rss', 'bo_use_sns', 'bo_use_category', 'bo_use_name', 'bo_use_ip_view',
                    'bo_use_list_view', 'bo_use_email', 'bo_use_extra', 'bo_use_syntax', 'bo_order_search'
                ));

                $board['bo_count_delete']   = 0;
                $board['bo_count_modify']   = 0;
                $board['bo_use_secret']     = 0;
                $board['bo_page_rows']      = 15;
                $board['bo_page_rows_comt'] = 50;
                $board['bo_subject_len']    = 75;
                $board['bo_new']            = 24;
                $board['bo_hot']            = 100;
                $board['bo_image_width']    = 800;
                $board['bo_upload_ext']     = 'zip|swf';
                $board['bo_upload_size']    = 2048;
                $board['bo_reply_order']    = 1;
                $board['bo_use_comment']    = 1;
                $board['bo_use_sideview']   = 1;
                $board['bo_use_editor']     = 1;
                $board['bo_use_search']     = 1;
                $board['bo_skin']           = 'basic';
                $board['gr_id']             = $w;
            }
            else if ($w == 'u') {
                $title = '수정';

                $board = $this->Basic_model->get_board($bo_table);
                if (!isset($board['bo_table']))
                    alert('존재하지 않은 게시판 입니다.');
            }

            $upload_max_size = ini_get('upload_max_filesize');
            if (!preg_match("/([m|M])$/", $upload_max_size))
                $upload_max_size = (int)($upload_max_size / 1048576);

            $head = array('title' => '게시판'.$title);
            $data = array(
                'w' => $w,
                'token' => get_token(),
                
                'bo_table'          => $board['bo_table'],
                'bo_subject'        => $board['bo_subject'],
                'bo_admin'          => $board['bo_admin'],
                'bo_head'           => $board['bo_head'],
                'bo_tail'           => $board['bo_tail'],
                'bo_insert_content' => $board['bo_insert_content'],
                'bo_order_search'   => $board['bo_order_search'],
                'bo_sort_field'     => $board['bo_sort_field'],
                
                'bo_count_write'   => (isset($board['bo_count_write'])) ? number_format($board['bo_count_write']) : FALSE,
                'bo_count_comment' => (isset($board['bo_count_comment'])) ? number_format($board['bo_count_comment']) : FALSE,
                'upload_max_size'  => $upload_max_size,

                'bo_skin'           => $board['bo_skin'],
                'gr_id'             => $board['gr_id'],
                'bo_count_delete'   => $board['bo_count_delete'],
                'bo_count_modify'   => $board['bo_count_modify'],
                'bo_use_secret'     => $board['bo_use_secret'],
                'bo_page_rows'      => $board['bo_page_rows'],
                'bo_page_rows_comt' => $board['bo_page_rows_comt'],
                'bo_subject_len'    => $board['bo_subject_len'],
                'bo_new'            => $board['bo_new'],
                'bo_hot'            => $board['bo_hot'],
                'bo_image_width'    => $board['bo_image_width'],
                'bo_upload_ext'     => $board['bo_upload_ext'],
                'bo_upload_size'    => $board['bo_upload_size'],
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
                'use_extra_chk'     => ($board['bo_use_extra']) ? "checked='checked'" : '',
                'use_syntax_chk'    => ($board['bo_use_syntax']) ? "checked='checked'" : '',
                'use_search_chk'    => ($board['bo_use_search']) ? "checked='checked'" : '',

                'group_select'      => get_group_select('gr_id', $board['gr_id']),
                'skin_select'       => get_skin_dir('board', 'bo_skin', $board['bo_skin']),
                'bo_list_level'     => get_mb_level_select('bo_list_level', (isset($board['bo_list_level'])) ? $board['bo_list_level'] : 1),
                'bo_read_level'     => get_mb_level_select('bo_read_level', (isset($board['bo_read_level'])) ? $board['bo_read_level'] : 1),
                'bo_write_level'    => get_mb_level_select('bo_write_level', (isset($board['bo_write_level'])) ? $board['bo_write_level'] : 2),
                'bo_reply_level'    => get_mb_level_select('bo_reply_level', (isset($board['bo_reply_level'])) ? $board['bo_reply_level'] : 2),
                'bo_comment_level'  => get_mb_level_select('bo_comment_level', (isset($board['bo_comment_level'])) ? $board['bo_comment_level'] : 2),
                'bo_upload_level'   => get_mb_level_select('bo_upload_level', (isset($board['bo_upload_level'])) ? $board['bo_upload_level'] : 2),
                'bo_download_level' => get_mb_level_select('bo_download_level', (isset($board['bo_download_level'])) ? $board['bo_download_level'] : 2)
            );

            widget::run('head', $head);
            $this->load->view(ADM_F.'/board_form', $data);
            widget::run('tail');
        }
        else {
            check_token();
            
            $w = $this->input->post('w');
            $bo_table = $this->input->post('bo_table');

            if (!$w) {
                $bo = $this->Basic_model->get_board($bo_table, 'bo_table');
                if (isset($bo['bo_table']))
                    alert($bo['bo_table'].'은(는) 이미 존재하는 TABLE 입니다.');
                
                $board_path = DATA_PATH.'/file/'.$bo_table;

                // 게시판 디렉토리 생성
                mkdir($board_path, 0707);
                chmod($board_path, 0707);
                // 게시판 썸네일 디렉토리 생성
                mkdir($board_path.'/thumb', 0707);
                chmod($board_path.'/thumb', 0707);
                
                $this->load->helper('file');
                $board_index = $board_path.'/index.html';
                write_file($board_index, '');
                chmod($board_index, 0606);

                $this->Board_model->insert();
            }
            else if ($w == 'u') {
                // 글수 조정
                if ($this->input->post('proc_count'))
                    $this->Board_model->proc_count();

                // 공지 가져오기
                $is_notice = '';
                $bo = $this->Basic_model->get_board($bo_table, 'bo_notice');
                if (isset($bo['bo_notice']))
                    $is_notice = $bo['bo_notice'];

                $this->Board_model->update($is_notice);
            }
            else
                alert('잘못된 접근입니다.');

            if ($this->input->post('chk'))
                $this->Board_model->group_update();

            goto_url(ADM_F.'/board/form/u/'.$bo_table);
        }
    }
}
?>