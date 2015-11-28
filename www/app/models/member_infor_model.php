<?php
class Member_infor_model extends CI_Model {
    function __construct() {
        parent::__construct();
    }

    function insert($mb_nick) {
        $sql = array(
            'mb_id'          => $this->input->post('mb_id'),
            'mb_password'    => $this->encrypt->encode($this->input->post('mb_password')),
            'mb_name'        => $this->input->post('mb_name'),
            'mb_sex'         => $this->input->post('mb_sex'),
            'mb_nick'        => $mb_nick,
            'mb_nick_date'   => TIME_YMD,
            'mb_password_q'  => $this->input->post('mb_password_q'),
            'mb_password_a'  => $this->input->post('mb_password_a'),
            'mb_email'       => $this->input->post('mb_email'),
            'mb_homepage'    => $this->input->post('mb_homepage'),
            'mb_tel'         => $this->input->post('mb_tel'),
            'mb_hp'          => $this->input->post('mb_hp'),
            'mb_zip'         => $this->input->post('mb_zip1').'-'.$this->input->post('mb_zip2'),
            'mb_addr1'       => $this->input->post('mb_addr1'),
            'mb_addr2'       => $this->input->post('mb_addr2'),
            'mb_profile'     => $this->input->post('mb_profile', TRUE),
            'mb_today_login' => TIME_YMDHIS,
            'mb_datetime'    => TIME_YMDHIS,
            'mb_ip'          => $this->input->server('REMOTE_ADDR'),
            'mb_level'       => $this->config->item('cf_register_level'),
            'mb_login_ip'    => $this->input->server('REMOTE_ADDR'),
            'mb_mailling'    => $this->input->post('mb_mailling'),
            'mb_open'        => $this->input->post('mb_open'),
            'mb_open_date'   => TIME_YMD
        );

        if ($this->input->post('mb_birth'))
            $sql['mb_birth'] = $this->input->post('mb_birth');

        // 이메일 인증을 사용하지 않는다면 이메일 인증시간을 바로 넣는다
        if (!$this->config->item('cf_use_email_certify'))
            $sql['mb_email_certify'] = TIME_YMDHIS;

        $this->db->insert('ki_member', $sql);
    }

    function update() {
        $sql = array(
            'mb_password_q' => $this->input->post('mb_password_q'),
            'mb_password_a' => $this->input->post('mb_password_a'),
            'mb_mailling'   => $this->input->post('mb_mailling'),
            'mb_open'       => $this->input->post('mb_open'),
            'mb_email'      => $this->input->post('mb_email'),
            'mb_homepage'   => $this->input->post('mb_homepage'),
            'mb_tel'        => $this->input->post('mb_tel'),
            'mb_hp'         => $this->input->post('mb_hp'),
            'mb_zip'        => $this->input->post('mb_zip1').'-'.$this->input->post('mb_zip2'),
            'mb_addr1'      => $this->input->post('mb_addr1'),
            'mb_addr2'      => $this->input->post('mb_addr2'),
            'mb_profile'    => $this->input->post('mb_profile', TRUE)
        );
        if ($this->config->item('cf_use_nick'))
            $sql['mb_nick'] = $this->input->post('mb_nick');
        
        if ($this->input->post('mb_nick_default') != $this->input->post('mb_nick'))
            $sql['mb_nick_date'] = TIME_YMD;

        if ($this->input->post('mb_open_default') != $this->input->post('mb_open'))
            $sql['mb_open_date'] = TIME_YMD;

        // 이전 메일주소와 수정한 메일주소가 틀리다면 인증을 다시 해야하므로 값을 삭제
        if ($this->input->post('old_email') != $this->input->post('mb_email') && $this->config->item('cf_use_email_certify'))
            $sql['mb_email_certify'] = '';
            
        // 성별 & 생일
        if ($this->input->post('mb_sex'))   $sql['mb_sex']   = $this->input->post('mb_sex');
        if ($this->input->post('mb_birth')) $sql['mb_birth'] = $this->input->post('mb_birth');
   
        $this->db->where('mb_id', $this->input->post('mb_id'));
        $this->db->update('ki_member', $sql);
    }

    function update_pwd() {
        $sql = array('mb_password' => $this->encrypt->encode($this->input->post('new_password')));
        $this->db->where('mb_id', $this->input->post('mb_id'));
        $this->db->update('ki_member', $sql);
    }
}
?>