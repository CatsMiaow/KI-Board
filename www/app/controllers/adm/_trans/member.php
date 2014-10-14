<?php
class Member extends CI_Controller {
    function __construct() {
        parent::__construct();
        check_token(ADM_F.'/member/lists');
        $this->load->model(ADM_F.'/Member_model');
    }

    function delete() {
        if ($this->input->post('mb_id'))
            $mb_ids = array($this->input->post('mb_id'));
        else if ($this->input->post('chk'))
            $mb_ids = $this->input->post('chk');
        else
            alert('잘못된 접근입니다.');

        $msg = '';
        $mb_true = array();
        $row = $this->Member_model->get_mbs_infor($mb_ids, 'mb_id,mb_level');
        foreach ($row as $mb) {
            if ($mb['mb_level'] < 2)
                $msg .= $mb['mb_id']." : 이미 탈퇴/삭제한 회원입니다.\\n";
            else if ($mb['mb_id'] == ADMIN)
                $msg .= $mb['mb_id']." : 최고관리자는 삭제할 수 없습니다.\\n";
            else
                $mb_true[] = $mb['mb_id'];
        }

        if ($msg)
            echo "<script type='text/javascript'>alert('".$msg."');</script>";

        if (!$mb_true)
            goto_url(URL);

        // 회원자료 삭제
        $this->Member_model->delete($mb_true);

        // 아이콘 삭제
        foreach($mb_ids as $mb_id) {
            @unlink(DATA_PATH.'/member/'.substr($mb_id,0,2).'/'.$mb_id.'.gif');
            @unlink(DATA_PATH.'/member/'.substr($mb_id,0,2).'/n_'.$mb_id.'.gif');
        }

        goto_url(URL);
    }

    function update() {
        if ($this->input->post('chk')) {
            $mb_ids = $this->input->post('chk');
            $mb_levels = $this->input->post('mb_levels');
        } else
            alert('잘못된 접근입니다.');

        if (SU_ADMIN != ADMIN) {
            $key = array_search(ADMIN, $mb_ids);
            if ($key !== FALSE) {
                unset($mb_ids[$key]);

                $msg = '최고관리자는 수정할 수 없습니다.';
                echo "<script type='text/javascript'>alert('".$msg."');</script>";
            }
        }

        foreach ($mb_ids as $mb_id) {
            $this->Member_model->list_update($mb_id, $mb_levels[$mb_id]);
        }
        
        goto_url(URL);
    }
}
?>