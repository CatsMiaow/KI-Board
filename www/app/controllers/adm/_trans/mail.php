<?php
class Mail extends CI_Controller {
    function __construct() {
        parent::__construct();
        check_token(ADM_F.'/mail/lists');
        $this->load->model(ADM_F.'/Mail_model');
    }

    function delete() {
        $this->Mail_model->delete();

        goto_url(ADM_F.'/mail/lists');
    }
}
?>