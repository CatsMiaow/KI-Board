<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class Head extends Widget {
    function index($data=FALSE) {
        $widget = FALSE;
        if (defined('WIDGET_SKIN'))
            $widget = WIDGET_SKIN;
        
        $css = array();
        if (defined('CSS_SKIN'))
            $css = explode(',', CSS_SKIN);
        
        $var_board = '';
        if (defined('BO_TABLE')) {
            $var_board = "  , rt_bo_table = '".BO_TABLE."'\n";
            if (isset($data['sca']))
                $var_board .= "  , rt_bo_sca = '".$data['sca']."'\n";
            
            if (BO_HEAD) {
                $bo_head = explode('/', BO_HEAD);
                $widget = $bo_head[0];
                if (isset($bo_head[1]))
                    $css[] = $bo_head[1];
            }
        }

        if ($widget)
            $widget = $this->$widget($data);

        $head = array(
			'title'       => isset($data['title']) ? $data['title'] : $this->config->item('cf_title'),
			'charset'     => $this->config->item('charset'),
			'css'         => $css,
			'var_board'   => $var_board,
			'widget_skin' => $widget['skin'],
			'widget_body' => $widget['body'] ? ' class="'.$widget['body'].'"' : ''
        );
        $this->load->view('_head', $head);
    }

    // 관리자
    function admin($admin=FALSE) {
        define('ADM_PATH', RT_PATH.'/'.ADM_F);
        
        $admin['use_point'] = $this->config->item('cf_use_point');
        $admin['use_popup'] = $this->config->item('cf_use_popup');
        $admin['current'] = ADM_PATH.$this->uri->slash_segment(2, 'both');
        return array(
            'skin' => $this->load->view(ADM_F.'/head', $admin, TRUE),
            'body' => ''
        );
    }

    // 메인
    function main($main=FALSE) {
        if (IS_MEMBER) {
            $mb = unserialize(MEMBER);
            $main['mb_nick']      = $mb['mb_nick'];
            $main['mb_point']     = $mb['mb_point'];
            $main['mb_memo_cnt']  = $mb['mb_memo_cnt'];
            $main['mb_memo_call'] = $mb['mb_memo_call'];
        }
        return array(
            'skin' => $this->load->view('main/head', $main, TRUE),
            'body' => 'body_gray'
        );
    }
}