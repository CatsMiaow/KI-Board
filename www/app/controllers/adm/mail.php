<?php
class Mail extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model(ADM_F.'/Mail_model');
        define('WIDGET_SKIN', 'admin');
    }

    function lists($page=1) {
        $this->load->library('pagination');
        $this->load->helper('admin');

        $config['base_url'] = RT_PATH.'/'.ADM_F.'/mail/lists/';
        $config['per_page'] = 15;

        $offset = ($page - 1) * $config['per_page'];
        $result = $this->Mail_model->list_result($config['per_page'], $offset);
        $total_cnt = $result['total_cnt'];

        $config['total_rows'] = $total_cnt;
        $config['uri_segment'] = 4;
        $this->pagination->initialize($config);

        $list = array();
        $token = get_token();
        foreach ($result['qry'] as $i => $row) {
            $list[$i] = new stdClass();
            $list[$i]->num = number_format($total_cnt - ($page - 1) * $config['per_page'] - $i);
            $list[$i]->id = $row['ma_id'];
            $list[$i]->subject = $row['ma_subject'];
            $list[$i]->content = $row['ma_content'];
            $list[$i]->time = $row['ma_time'];
            
            $list[$i]->s_mod = icon('수정', 'mail/form/u/'.$row['ma_id']);
            $list[$i]->s_del = icon('삭제', "javascript:post_send('".ADM_F."/_trans/mail/delete', {ma_id:'".$row['ma_id']."', token:'".$token."'}, true);");
            $list[$i]->s_vie = icon('보기', 'mail/preview/'.$row['ma_id'], "_blank");
        }

        $head = array('title' => '회원메일발송');
        $data = array(
            'token' => $token,

            'list' => $list,
            's_add' => icon('작성', 'mail/form'),    

            'total_cnt' => number_format($total_cnt),
            'paging' => $this->pagination->create_links(),
        );

        widget::run('head', $head);
        $this->load->view(ADM_F.'/mail_list', $data);
        widget::run('tail');
    }
    
    function form($w='', $ma_id='') {
        $this->load->library('form_validation');

        $config = array(
            array('field'=>'ma_subject', 'label'=>'제목', 'rules'=>'trim|required'),
            array('field'=>'ma_content', 'label'=>'내용', 'rules'=>'trim|required')
        );

        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            if (!$w) {
                $title = "입력";

                $ma = FALSE;
            }
            else if ($w == 'u') {
                $title = "수정";
                
                $ma = $this->Mail_model->get_mail($ma_id, 'ma_id,ma_subject,ma_content');
                if (!isset($ma['ma_id']))
                    alert("등록된 자료가 없습니다.");
            }
            else 
                alert("잘못된 접근입니다.");

            $head = array('title' => '회원메일 '.$title);
            $data = array(
                'w' => $w,
                'ma_id' => $ma['ma_id'],
                'subject' => $ma['ma_subject'],
                'content' => $ma['ma_content'],
                'token' => get_token()
            );

            widget::run('head', $head);
            $this->load->view(ADM_F.'/mail_form', $data);
            widget::run('tail');
        }
        else {
            check_token();
            $w = $this->input->post('w');

            if (!$w) {
                $this->Mail_model->insert();
            }
            else if ($w == 'u') {
                $this->Mail_model->update();
            }
            else
                alert("잘못된 접근입니다.");

            goto_url(ADM_F.'/mail/lists');
        }
    }
    
    function test($ma_id='') {
        $ma = $this->Mail_model->get_mail($ma_id, 'ma_subject,ma_content');
        if (!isset($ma['ma_subject']))
            alert('등록된 자료가 없습니다.');

        $member = unserialize(MEMBER);
        $birth = (int)substr($member['mb_birth'],4,2).'월 '.(int)substr($member['mb_birth'],6,2).'일';

        $content = str_replace(
            array('[이름]', '[별명]', '[회원아이디]', '[이메일]', '[생일]'),
            array($member['mb_name'], $member['mb_nick'], $member['mb_id'], $member['mb_email'], $birth),
        $ma['ma_content']);

        $this->load->library('email');
        $this->email->clear();
        $this->email->from($member['mb_email'], '메일테스트');
        $this->email->to($member['mb_email']);
        $this->email->subject($ma['ma_subject']);
        $this->email->message($content);
        if (!$this->email->send()) {
            alert('※ 메일전송 오류\\n\\n'.$this->email->print_debugger());
        }
        else {
            alert($member['mb_nick'].'('.$member['mb_email'].')님께 테스트 메일을 발송하였습니다.\\n\\n확인하여 주십시오.');
        }
    }
    
    function preview($ma_id='') {
        $ma = $this->Mail_model->get_mail($ma_id, 'ma_subject,ma_content');
        if (!$ma || !$ma['ma_subject'])
            alert('등록된 자료가 없습니다.');

        echo "<span style='font-size:9pt;'>".$ma['ma_subject']."</span>";
        echo '<hr/>';
        echo $ma['ma_content'];
    }
    
    function select_form($ma_id='') {
        if (!$this->config->item('cf_use_email'))
            alert("환경설정에서 \'메일발송 사용\'에 체크하셔야 메일을 발송할 수 있습니다.");

        $ma = $this->Mail_model->get_mail($ma_id, 'ma_id,ma_last_option');
        if (!isset($ma['ma_id']))
            alert('보내실 내용을 선택하여 주십시오.');

        $result = $this->Mail_model->member_cnt();

        $ma_lopt = array();
        $last_option = explode('||', $ma['ma_last_option']);
        foreach($last_option as $row) {
            $option = explode('=', $row);

            $var = $option[0];
            $$var = (isset($option[1])) ? $option[1] : '';
        }

        $this->load->helper('admin');
        $head = array('title' => '회원메일발송');
        $data = array(
            'token' => get_token(),

            'ma_id' => $ma_id,
            'mb_level_from' => get_mb_level_select('mb_level_from', (isset($mb_level_from)) ? $mb_level_from : 1),
            'mb_level_to'   => get_mb_level_select('mb_level_to', (isset($mb_level_to)) ? $mb_level_to : 10),

            'mb_mailling'   => (isset($mb_mailling))   ? $mb_mailling : 1,
            'mb_area'       => (isset($mb_area))       ? $mb_area : '',
            'mb_birth_from' => (isset($mb_birth_from)) ? $mb_birth_from : '',
            'mb_birth_to'   => (isset($mb_birth_to))   ? $mb_birth_to : '',
            'mb_email'      => (isset($mb_email))       ? $mb_email : '',
            
            'total_cnt'  => number_format($result['total_cnt']),
            'leave_cnt'  => number_format($result['leave_cnt']),
            'member_cnt' => number_format($result['member_cnt'])
        );

        widget::run('head', $head);
        $this->load->view(ADM_F.'/mail_select_form', $data);
        widget::run('tail');
    }
    
    function select_lists() {
        check_token();
        $result = $this->Mail_model->select_list();

        if ($result['select_cnt'] == 0)
            alert('선택하신 내용으로는 해당되는 회원자료가 없습니다.', URL);

        $this->Mail_model->option_update();

        $list = array();
        foreach($result['qry'] as $i => $row) {
            $list[$i] = new stdClass();
            
            if ($this->config->item('cf_use_nick'))
                $list[$i]->mb_nick = $row['mb_nick'];

            $birth = ($row['mb_birth']) ? substr($row['mb_birth'],5,2).'월 '.substr($row['mb_birth'],8,2).'일' : '미입력';
            
            $list[$i]->mb_id = $row['mb_id'];
            $list[$i]->mb_name = $row['mb_name'];
            $list[$i]->mb_birth = $birth;
            $list[$i]->mb_birth_year = ($birth != '미입력') ? substr($row['mb_birth'],0,4).'년 ' : '';
            $list[$i]->mb_email = $row['mb_email'];
        }

        $head = array('title' => '회원메일발송');
        $data = array(
            'token' => get_token(),
            'use_nick' => $this->config->item('cf_use_nick'),
            'ma_id' => $this->input->post('ma_id'),
            'list' => $list,
            'select_cnt' => number_format($result['select_cnt'])
        );

        widget::run('head', $head);
        $this->load->view(ADM_F.'/mail_select_list', $data);
        widget::run('tail');
    }
    
    function select_send() {
        if ($this->input->post('mb_id')) {
            $member = unserialize(MEMBER);
            $mb_ids = $this->input->post('mb_id');

            $mb_name = $this->input->post('mb_name');
            $mb_nick = $this->input->post('mb_nick');
            $mb_email = $this->input->post('mb_email');
            $mb_birth = $this->input->post('mb_birth');

            $ma = $this->Mail_model->get_mail($this->input->post('ma_id'), 'ma_subject,ma_content');

            $mail_msg = '';
            $mail_fail = 0;
            $this->load->library('email');
            foreach ($mb_ids as $mb_id) {
                $content = str_replace(
                    array('[이름]', '[별명]', '[회원아이디]', '[이메일]', '[생일]'),
                    array($mb_name[$mb_id], $mb_nick[$mb_id], $mb_id, $mb_email[$mb_id], $mb_birth[$mb_id]),
                $ma['ma_content']);
                
                $this->email->clear();
                $this->email->to($mb_email[$mb_id]);
                $this->email->from($member['mb_email'], $this->config->item('cf_title'));
                $this->email->subject($ma['ma_subject']);
                $this->email->message($content);
                if (!$this->email->send()) {
                    $mail_msg .= $mb_email[$mb_id].'<br/>';
                    $mail_fail++;
                }
            }
        }
        else 
            alert('잘못된 접근입니다.');

        $head = array('title' => '메일전송 결과');
        $data = array(
            'mail_msg' => (!$mail_msg) ? '없음' : $mail_msg,
            'total_cnt' => count($mb_ids) - $mail_fail
        );

        widget::run('head', $head);
        $this->load->view(ADM_F.'/mail_select_send', $data);
        widget::run('tail');
    }
}
?>