<?php
class Sendmail_test extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('email');
        define('WIDGET_SKIN', 'admin');
    }

    function index() {
        $member = unserialize(MEMBER);
        if (!$member['mb_email'])
            alert('관리자 E-mail이 존재하지 않습니다.');

        $mail_addr = $mail_msg = FALSE;

        if ($this->input->post('mail_addr')) {
            check_token();
            
            $mail_addr = $this->input->post('mail_addr');
            $subject = '[메일검사] 제목';
            $content = '[메일검사] 내용<br />이 내용이 제대로 보인다면 보내는 메일 서버에는 이상이 없는것입니다.<br />발송시간 : '.date('Y-m-d H:i:s').'<br />이 메일 주소로는 회신되지 않습니다.';

            $this->email->clear();
            $this->email->from($member['mb_email'], '메일검사');
            $this->email->to($mail_addr);
            $this->email->subject($subject);
            $this->email->message($content);
            if (!$this->email->send())
                $mail_msg = '<strong>※ 메일전송 오류</strong><br/>'.$this->email->print_debugger();
            else
                $mail_msg = '<strong>'.$mail_addr.'</strong> (으)로 메일을 발송 하였습니다.
                    <br/>해당 주소로 메일이 왔는지 확인하세요.
                    <br/>메일이 오지 않는다면 프로그램의 오류가 아닌
                    <br/>메일 서버(sendmail)의 오류일 가능성이 있습니다.
                    <br/>이런 경우에는 웹 서버관리자에게 문의하세요.';
        }

        $head = array('title' => '메일전송 테스트');
        $data = array(
            'token' => get_token(),
            'mail_addr' => $mail_addr,
            'mail_msg' => $mail_msg
        );

        widget::run('head', $head);
        $this->load->view(ADM_F.'/sendmail_test', $data);
        widget::run('tail');
    }
}
?>