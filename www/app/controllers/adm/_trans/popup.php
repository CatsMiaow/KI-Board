<?php
class Popup extends CI_Controller {
    function __construct() {
        parent::__construct();
        check_token(ADM_F.'/popup/lists');
        $this->load->model(ADM_F.'/Popup_model');
    }

    function delete() {
        if ($this->input->post('pu_id'))
            $pu_ids = array($this->input->post('pu_id'));
        else if ($this->input->post('chk'))
            $pu_ids = $this->input->post('chk');
        else
            alert('잘못된 접근입니다.');

        $this->Popup_model->delete($pu_ids);
        
        goto_url(URL);
    }

    function update() {
        if ($this->input->post('chk')) {
            $pu_ids = $this->input->post('chk');
            $pu_names = $this->input->post('pu_name');
            $pu_uses = $this->input->post('pu_use');
        }
        else
            alert('잘못된 접근입니다.');

        $this->Popup_model->list_update($pu_ids, $pu_names, $pu_uses);
        
        goto_url(URL);
    }
}
?>