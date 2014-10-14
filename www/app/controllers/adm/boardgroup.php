<?php
class Boardgroup extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model(ADM_F.'/Boardgroup_model');
        define('WIDGET_SKIN', 'admin');
    }

    function lists() {
        $this->load->library(array('pagination', 'querystring'));
        $this->load->helper('admin');

         $param =& $this->querystring;
        $page = $this->uri->segment(5, 1);
        $sst  = $param->get('sst', 'gr_id');
        $sod  = $param->get('sod', 'asc');
        $sfl  = $param->get('sfl');
        $stx  = $param->get('stx');

        $config['suffix'] = $param->output();
        $config['base_url'] = RT_PATH.'/'.ADM_F.'/boardgroup/lists/page/';
        $config['per_page'] = 15;

        $offset = ($page - 1) * $config['per_page'];
        $result = $this->Boardgroup_model->list_result($sst, $sod, $sfl, $stx, $config['per_page'], $offset);

        $config['total_rows'] = $result['total_cnt'];
        $this->pagination->initialize($config);

        $list = array();
        $token = get_token();
        foreach ($result['qry'] as $i => $row) {
            $list[$i] = new stdClass();
            $list[$i]->bo_cnt = $row['bo_cnt'];
            $list[$i]->id = $row['gr_id'];
            $list[$i]->subject = $row['gr_subject'];
            $list[$i]->admin = $row['gr_admin'];
            $list[$i]->s_mod = icon('수정', 'boardgroup/form/u/'.$row['gr_id']);
            $list[$i]->s_del = icon('삭제', "javascript:post_send('".ADM_F."/_trans/boardgroup/delete', {gr_id:'".$row['gr_id']."', token:'".$token."'}, true);");
        }

        $head = array('title' => '게시판그룹관리');
        $data = array(
            'token' => $token,

            'list' => $list,
            's_add' => icon('작성', 'boardgroup/form'),

            'sfl' => $sfl,
            'stx' => $stx,

            'total_cnt' => number_format($result['total_cnt']),
            'paging' => $this->pagination->create_links(),

            'sort_gr_id' => $param->sort('gr_id', 'desc'),
            'sort_gr_subject' => $param->sort('gr_subject'),
            'sort_gr_admin' => $param->sort('gr_admin')
        );

        widget::run('head', $head);
        $this->load->view(ADM_F.'/boardgroup_list', $data);
        widget::run('tail');
    }
    
    function form($w='', $gr_id='') {
        $this->load->library('form_validation');

        $config = array(
            array('field'=>'gr_id', 'label'=>'아이디', 'rules'=>'trim|required|min_length[3]|max_length[20]|alpha_dash|xss_clean'),
            array('field'=>'gr_subject', 'label'=>'제목', 'rules'=>'trim|required|max_length[20]'),
            array('field'=>'gr_admin', 'label'=>'그룹 관리자', 'rules'=>'trim|min_length[3]|max_length[20]|alpha_dash')
        );

        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            if ($w == '') {
                $title = '생성';

                $gr = FALSE;
            } 
            else if ($w == 'u') {
                $gr = $this->Boardgroup_model->get_group($gr_id);
                if (!isset($gr['gr_id']))
                    alert('존재하지 않는 그룹 ID 입니다.');

                $title = '수정';
            } 
            else
                alert('잘못된 접근입니다.');
            
            $head = array('title' => '게시판그룹'.$title);
            $data = array(
                'w' => $w,
                'token' => get_token(),
                'gr_id' => $gr['gr_id'],
                'gr_subject' => $gr['gr_subject'],
                'gr_admin' => $gr['gr_admin']
            );

            widget::run('head', $head);
            $this->load->view(ADM_F.'/boardgroup_form', $data);
            widget::run('tail');
        }
        else {
            check_token();

            $w = $this->input->post('w');
            $gr_id = $this->input->post('gr_id');

            if (!$w) {
                $gr = $this->Boardgroup_model->get_group($gr_id);
                if (isset($gr['gr_id']))
                    alert("이미 존재하는 그룹 ID 입니다.");

                $this->Boardgroup_model->insert();
            }
            else if ($w == 'u') {
                $this->Boardgroup_model->update();
            }
            else
                alert('잘못된 접근입니다.');

            // goto_url(ADM_F.'/boardgroup/form/u/'.$gr_id);
            goto_url(ADM_F.'/boardgroup/lists');
        }
    }
}
?>