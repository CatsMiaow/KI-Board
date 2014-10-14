<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class Tail extends Widget {
    function index($data=FALSE) {
        $widget = FALSE;
        if (defined('WIDGET_SKIN'))
            $widget = WIDGET_SKIN;
        elseif (defined('BO_TABLE'))
            $widget = BO_TAIL;

        if ($widget)
            $this->$widget($data);

        $js = isset($data['js']) ? $data['js'] : array();
        
        $this->load->view('_tail', array('js' => $js));
    }

    function admin($admin=FALSE) {
        $this->load->view(ADM_F.'/tail', $admin);
    }

    function main($main=FALSE) {
        $this->load->view('main/tail', $main);
    }
}