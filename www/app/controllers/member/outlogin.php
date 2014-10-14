<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class Outlogin extends Widget {
    function index() {
        if (IS_MEMBER) {
            $member = unserialize(MEMBER);
            
            return $this->load->view('outlogin/logout', array(
                'mb_nick' => $member['mb_nick'],
                'mb_point' => $member['mb_point'],
                'mb_memo_cnt' => $member['mb_memo_cnt'],
                'mb_memo_call' => $member['mb_memo_call']
            ), TRUE);
        }
        else {
            return $this->load->view('outlogin/login', array(), TRUE);
        }
    }
}