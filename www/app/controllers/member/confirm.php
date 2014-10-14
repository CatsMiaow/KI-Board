<?php
class Confirm extends CI_Controller {
    function __construct() {
        parent::__construct();
        define('WIDGET_SKIN', 'main');
    }

    function qry($aurl) {
        if (!IS_MEMBER)
            alert('로그인 한 회원만 접근하실 수 있습니다.', '/');

        $member = unserialize(MEMBER);

        $this->session->unset_userdata('ss_tmp_password');
        $head = array('title' => '회원 비밀번호 확인');
        $data = array(
            'token'  => get_token(),
            'mb_id'  => $member['mb_id'],
            'action' => RT_PATH.'/'.str_replace('.', '/', $aurl)
        );

        widget::run('head', $head);
        $this->load->view('member/confirm', $data);
        widget::run('tail');
    }
}
?>