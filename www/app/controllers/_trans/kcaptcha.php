<?php
class Kcaptcha extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('kcaptcha');
    }

    function session() {
        require(APPPATH.'config/kcaptcha'.EXT);

        while(TRUE) {
            $keystring = '';
            for ($i=0; $i<$length; $i++) {
                $keystring .= $allowed_symbols{mt_rand(0,strlen($allowed_symbols)-1)};
            }
            if (!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $keystring)) break;
        }

        $this->session->set_userdata("captcha_keystring", $keystring);
        $this->kcaptcha->setKeyString($this->session->userdata("captcha_keystring"));
        echo md5($this->kcaptcha->getKeyString());
    }

    function image($t) {
        error_reporting (E_ALL);
        $this->kcaptcha->setKeyString($this->session->userdata("captcha_keystring"));
        $this->kcaptcha->getKeyString();
        $this->kcaptcha->image();
    }
}
?>