<?php
class Login extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->helper('cookie');
        define('WIDGET_SKIN', 'main');
    }

    function index() {
        $this->qry();
    }

    function qry($msg=FALSE) {
        if (IS_MEMBER)
            goto_url(URL);

        if ($this->input->post('url'))
            $url = $this->input->post('url');
        else
            $url = (is_numeric($msg)) ? URL : urldecode(str_replace('.', '%', $msg));

        $reId = get_cookie('ck_mb_id');

        $head = array('title' => '로그인');
        $data = array(
            'url'      => $url,
            'msg'      => ($msg == 1) ? TRUE : FALSE,
            'reId'     => $reId,
            'chk_reId' => $reId ? 1 : 0
        );
        
        widget::run('head', $head);
        $this->load->view('member/login', $data);
        widget::run('tail');
    }

    function in() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules(array(
            array('field'=>'mb_id', 'label'=>'아이디', 'rules'=>'trim|required|min_length[3]|max_length[20]|alpha_dash|xss_clean'),
            array('field'=>'mb_password', 'label'=>'비밀번호', 'rules'=>'trim|required|md5')
        ));

        if ($this->form_validation->run() !== FALSE) {
            $this->load->library('encrypt');
            $mb = $this->Basic_model->get_member($this->input->post('mb_id'), 'mb_id, mb_password, mb_email, mb_leave_date, mb_email_certify');

            if (!$mb || $this->input->post('mb_password') !== $this->encrypt->decode($mb['mb_password']))
                goto_url('member/login/qry/1');

            if ($mb['mb_leave_date'] && $mb['mb_leave_date'] <= date('Ymd', time())) {
                $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1년 \\2월 \\3일", $mb['mb_leave_date']);
                alert("탈퇴한 아이디이므로 접근하실 수 없습니다.\\n\\n탈퇴일 : ".$date);
            }

            if ($this->config->item('cf_use_email_certify') && !preg_match("/[1-9]/", $mb['mb_email_certify']))
                alert("메일인증을 받으셔야 로그인 하실 수 있습니다.\\n\\n회원님의 메일주소는 ".$mb['mb_email']." 입니다.");

            $this->session->set_userdata('ss_mb_id', $mb['mb_id']);
            
            if ($this->input->post('reId')) {
                $cookie = array(
                   'name'   => 'ck_mb_id',
                   'value'  => $mb['mb_id'],
                   'expire' => 86400*30,
                   'domain' => $this->config->item('cookie_domain')
               );
               set_cookie($cookie);
            }
            else if (get_cookie('ck_mb_id'))
                delete_cookie('ck_mb_id');

            goto_url($this->input->post('url'));
        }
        
        goto_url('/');
    }

    function out() {
        if (IS_MEMBER) {
            $this->session->sess_destroy();
            delete_cookie('ck_mb_id');
        }
        goto_url('/');
    }
}
?>