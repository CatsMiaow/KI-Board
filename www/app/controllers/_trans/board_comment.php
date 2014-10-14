<?php
class Board_comment extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('Board_comt_model');
        $this->load->config('cf_board');
        $this->load->helper('board');
        // $this->output->enable_profiler(TRUE);
    }

    function update() {
        $this->load->library('form_validation');

        $config = array(
            array('field'=>'bo_table', 'label'=>'게시판아이디', 'rules'=>'trim|required|alpha_dash'),
            array('field'=>'wr_id', 'label'=>'원글아이디', 'rules'=>'trim|required|is_natural_no_zero'),
            array('field'=>'comment_id', 'label'=>'댓글아이디', 'rules'=>'trim|is_natural_no_zero'),
            array('field'=>'co_content', 'label'=>'내용', 'rules'=>'trim|required|xss_clean'),
            array('field'=>'w', 'label'=>'w', 'rules'=>'trim|required'),
        );
        if (!IS_MEMBER) {
            $config[] = array('field'=>'co_name', 'label'=>'이름', 'rules'=>'trim|required|max_length[10]');
            $config[] = array('field'=>'co_password', 'label'=>'비밀번호', 'rules'=>'trim|required|max_length[20]|md5');
            $config[] = array('field'=>'wr_key', 'label'=>'자동등록방지', 'rules'=>'trim|required');
        }

        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE)
            alert('올바르지 않은 접근입니다.');
        else {
            if (!IS_MEMBER)
                check_wrkey();
            
            $w = $this->input->post('w');
            $wr_id = $this->input->post('wr_id');
            $bo_table = $this->input->post('bo_table');
            $comment_id = $this->input->post('comment_id');
            
            $board = $this->Basic_model->get_board($bo_table, 'bo_subject,bo_admin,bo_comment_level,bo_use_name,bo_reply_order,bo_use_email', TRUE);
            $member = unserialize(MEMBER);

            define('IS_ADMIN', is_admin($member, $board));

            if ($w == 'c' || $w == 'cu')  {
                if ($member['mb_level'] < $board['bo_comment_level'])
                    alert('댓글을 쓸 권한이 없습니다.');
            }
            else
                alert('잘못된 접근입니다.');

            $wr = $this->Basic_model->get_write($bo_table, $wr_id, 'wr_id, wr_num, ca_code, wr_option, wr_subject, mb_id, wr_email');

            if (!isset($wr['wr_id']))
                alert("글이 존재하지 않습니다.\\n\\n글이 삭제되었거나 이동하였을 수 있습니다.");

            // 세션의 시간 검사 (글쓰기 딜레이)
            if ($w == 'c' && $this->session->userdata('ss_datetime') >= (time() - $this->config->item('cf_delay_sec')) && !IS_ADMIN)
                alert('너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.');

            $this->session->set_userdata('ss_datetime', time());

            // 동일내용 연속 등록 불가
            $row = $this->Board_comt_model->same_comment($bo_table, $wr_id, $comment_id);
            $curr_md5 = md5($this->input->server('REMOTE_ADDR').$this->input->post('co_content'));

            if ($row && $row['prev_md5'] == $curr_md5)
                alert('동일한 내용을 연속해서 등록할 수 없습니다.');

            if (IS_MEMBER) {
                $mb['mb_id']       = $member['mb_id'];
                $mb['co_name']     = $board['bo_use_name'] ? $member['mb_name'] : $member['mb_nick'];
                $mb['co_password'] = $member['mb_password'];
                $mb['co_email']    = $member['mb_email'];
            }
            else {
                $this->load->library('encrypt');
                $mb['mb_id']       = '';
                $mb['co_name']     = $this->input->post('co_name');
                $mb['co_password'] = $this->encrypt->encode($this->input->post('co_password'));
                $mb['co_email']    = '';
            }

            // 댓글 입력
            if ($w == 'c') {
                // 댓글 답변
                if ($comment_id) {
                    $reply = $this->Board_comt_model->get_comment($bo_table, $wr_id, $comment_id, 'co_id, co_num, co_reply, mb_id');
                    if (!isset($reply['co_id']))
                        alert("답변할 댓글이 없습니다.\\n\\n답변하는 동안 댓글이 삭제되었을 수 있습니다.");

                    $tmp_num = $reply['co_num'];
                    if (strlen($reply['co_reply']) == 10)
                        alert("더 이상 답변하실 수 없습니다.\\n\\n답변은 10단계 까지만 가능합니다.");

                    $tmp_reply = $this->Board_comt_model->get_reply_step($bo_table, $wr_id, $tmp_num, $board['bo_reply_order'], $reply['co_reply']);
                }
                else {
                    // 가장 큰 번호에 1을 더해서 넘겨줌
                    $tmp_num = $this->Board_comt_model->get_max_num($bo_table, $wr_id);
                    $tmp_num = (int)($tmp_num + 1);

                    $tmp_reply = '';
                }

                $comment_id = $this->Board_comt_model->comment_insert($bo_table, $wr_id, $tmp_num, $tmp_reply, $wr['ca_code'], $mb);

                // 메일발송 사용
                if ($this->config->item('cf_use_email') && $board['bo_use_email']) {
                    $super_admin = $this->Basic_model->get_member(ADMIN, 'mb_email');
                    $group_admin = $this->Basic_model->get_member($board['gr_admin'], 'mb_email');
                    $board_admin = $this->Basic_model->get_member($board['bo_admin'], 'mb_email');

                    $this->load->helper('textual');
                    $wr_subject = get_text(stripslashes($wr['wr_subject']));
                    $co_content = nl2br(get_text(stripslashes("----- 원글 -----\n\n".$wr['wr_subject']."\n\n\n----- 댓글 -----\n\n".$this->input->post('co_content'))));

                    $warr = array('c'=>'댓글', 'cu'=>'댓글 수정');
                    $str = $warr[$w];

                    $subject = "'".$board['bo_subject']."' 게시판에 ".$str."글이 올라왔습니다.";
                    $link_url = $this->config->item('base_url').'/board/'.$bo_table.'/view/wr_id/'.$wr_id.'#c_'.$comment_id;

                    $data = array(
                        'co_name' => $mb['co_name'],
                        'wr_subject' => $wr_subject,
                        'co_content' => $co_content,
                        'link_url' => $link_url
                    );
                    $content = $this->load->view('mail/write_update', $data, TRUE);

                    $to_email = array();
                    $this->load->library('email');

                    $this->email->clear();
                    $this->email->from(($mb['co_email'] ? $mb['co_email'] : $super_admin['mb_email']), $mb['co_name']);
            
                    // 게시판 관리자에게 보내는 메일
                    if ($this->config->item('cf_email_wr_board_admin') && $board_admin['mb_email']) {
                        $to_email[] = $board_admin['mb_email'];
                    }

                    // 그룹 관리자에게 보내는 메일
                    if ($this->config->item('cf_email_wr_group_admin') && $group_admin['mb_email']) {
                        if ($group_admin['mb_email'] != $board_admin['mb_email']) {
                            $to_email[] = $group_admin['mb_email'];
                        }
                    }

                    // 최고관리자에게 보내는 메일
                    if ($this->config->item('cf_email_wr_super_admin') && $super_admin['mb_email']) {
                        if ($super_admin['mb_email'] != $board_admin['mb_email'] && $super_admin['mb_email'] != $group_admin['mb_email']) {
                            $to_email[] = $super_admin['mb_email'];
                        }
                    }

                    // 답변 메일받기 (원게시자에게 보내는 메일)
                    if (strpos($wr['wr_option'], 'mail') !== FALSE && $wr['wr_email'] && $wr['wr_email'] != $mb['co_email']) {
                        if ($this->config->item('cf_email_wr_write'))
                            $to_email[] = $wr['wr_email'];
                    }

                    $this->email->to($to_email);
                    $this->email->subject($subject);
                    $this->email->message($content);
                    $this->email->send();
                }
            }
            else if ($w == 'cu') { // 댓글 수정
                $comment = $reply = $this->Board_comt_model->get_comment($bo_table, $wr_id, $comment_id, 'mb_id, co_num, co_reply');
                $tmp_num = $reply['co_num'];
                $tmp_reply = $reply['co_reply'];

                // 수정 권한 IF
                if (IS_ADMIN == 'group' || IS_ADMIN == 'board') {
                    $mb = $this->Basic_model->get_member($comment['mb_id'], 'mb_level');
                    $mb_level = (isset($mb['mb_level'])) ? $mb['mb_level'] : 1;
                }
                
                if (IS_ADMIN == 'super' && SU_ADMIN) {
                    // 통과
                }
                else if (IS_ADMIN == 'group') { // 그룹관리자
                    if ($member['mb_id'] == $board['gr_admin']) { // 자신이 관리하는 그룹인가
                        if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
                            alert('그룹관리자의 권한보다 높은 회원의 댓글이므로 수정할 수 없습니다.');
                    }
                    else
                        alert('자신이 관리하는 그룹의 게시판이 아니므로 댓글을 수정할 수 없습니다.');
                }
                else if (IS_ADMIN == 'board') { // 게시판관리자
                    if ($member['mb_id'] == $board['bo_admin']) { // 자신이 관리하는 게시판인가
                        if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
                            alert('게시판관리자의 권한보다 높은 회원의 댓글이므로 수정할 수 없습니다.');
                    }
                    else
                        alert('자신이 관리하는 게시판이 아니므로 댓글을 수정할 수 없습니다.');
                }
                else if (IS_MEMBER) {
                    if ($member['mb_id'] != $comment['mb_id'])
                        alert('자신의 글이 아니므로 수정할 수 없습니다.');
                }

                $cnt = $this->Board_comt_model->is_comment_reply($bo_table, $wr_id, $comment_id, $tmp_num, $tmp_reply);
                if ($cnt && !IS_ADMIN)
                    alert('이 댓글과 관련된 답변댓글이 존재하므로 수정할 수 없습니다.');

                $this->Board_comt_model->comment_update($bo_table, $wr_id, $comment_id);
            }

            $this->db->cache_delete('default', 'index');

            goto_url('board/'.$bo_table.'/view'.$this->input->post('qstr').'#c_'.$comment_id);
        }
    }

    function delete() {
        $bo_table = $this->input->post('bo_table');
        $wr_id = $this->input->post('wr_id');
        $comment_id = $this->input->post('comment_id');

        $comment = $this->Board_comt_model->get_comment($bo_table, $wr_id, $comment_id, 'wr_id, co_id, mb_id, co_password, co_num, co_reply');
        $board = $this->Basic_model->get_board($bo_table, 'bo_admin', TRUE);
        $member = unserialize(MEMBER);

        define('IS_ADMIN', is_admin($member, $board));

        if (!isset($comment['co_id']))
            alert('등록된 댓글이 없습니다.');
        
        // 수정 권한 IF
        if (IS_ADMIN == 'group' || IS_ADMIN == 'board') {
            $mb = $this->Basic_model->get_member($comment['mb_id'], 'mb_level');
            $mb_level = (isset($mb['mb_level'])) ? $mb['mb_level'] : 1;
        }

        if (IS_ADMIN == 'super' && SU_ADMIN) {
            // 통과
        }
        else if (IS_ADMIN == 'group') { // 그룹관리자
            if ($member['mb_id'] == $board['gr_admin']) { // 자신이 관리하는 그룹인가
                if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
                    alert('그룹관리자의 권한보다 높은 회원의 댓글이므로 삭제할 수 없습니다.');
            }
            else
                alert('자신이 관리하는 그룹의 게시판이 아니므로 댓글을 삭제할 수 없습니다.');
        }
        else if (IS_ADMIN == 'board') { // 게시판관리자
            if ($member['mb_id'] == $board['bo_admin']) { // 자신이 관리하는 게시판인가
                if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
                    alert('게시판관리자의 권한보다 높은 회원의 댓글이므로 삭제할 수 없습니다.');
            }
            else
                alert('자신이 관리하는 게시판이 아니므로 댓글을 삭제할 수 없습니다.');
        }
        else if (IS_MEMBER) {
            if ($member['mb_id'] != $comment['mb_id'])
                alert('자신의 글이 아니므로 삭제할 수 없습니다.');
        }
        else {
            $this->load->library('encrypt');
            if (md5($this->input->post('password')) != $this->encrypt->decode($comment['co_password']))
                alert('비밀번호가 맞지 않습니다.');
        }
        
        $cnt = $this->Board_comt_model->is_comment_reply($bo_table, $comment['wr_id'], $comment_id, $comment['co_num'], $comment['co_reply']);
        if ($cnt)
            alert('이 댓글과 관련된 답변댓글이 존재하므로 삭제할 수 없습니다.');
        
        $this->Board_comt_model->comment_delete($bo_table, $comment['wr_id'], $comment_id);

        $this->db->cache_delete('default', 'index');

        goto_url('board/'.$bo_table.'/view'.$this->input->post('qstr'));
    }
}
?>