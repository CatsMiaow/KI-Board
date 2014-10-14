<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class _Common {
    function index() {
        $CI =& get_instance();

        header("Content-Type: text/html; charset=".$CI->config->item('charset'));
        header("Expires: 0"); // rfc2616 - Section 14.21
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Cache-Control: pre-check=0, post-check=0, max-age=0"); // HTTP/1.1
        header("Pragma: no-cache"); // HTTP/1.0
        
        $is_member = $is_super = FALSE;
        $login_id = $CI->session->userdata('ss_mb_id');
        if ($login_id) {
            $member = $CI->Basic_model->get_member($login_id);

            if (substr($member['mb_today_login'], 0, 10) != TIME_YMD) {
                $CI->load->model('Point_model');
                $CI->Point_model->insert($member['mb_id'], $CI->config->item('cf_login_point'), TIME_YMD.' 첫로그인', '@login', $member['mb_id'], TIME_YMD);

                $CI->db->where('mb_id', $member['mb_id']);
                $CI->db->update('ki_member', array(
                    'mb_today_login' => TIME_YMDHIS,
                    'mb_login_ip'    => $CI->input->server('REMOTE_ADDR')
                ));
            }

            if ($member['mb_id']) {
                $is_member = TRUE;
                if ($member['mb_level'] >= 10) // 관리자 조건
                    $is_super = $member['mb_id'];

                if (!$CI->config->item('cf_use_nick'))
                    $member['mb_nick'] = $member['mb_name'];
            }
        }
        else
            $member['mb_level'] = 1;
        
        $php_self     = $CI->input->server('PHP_SELF');
        $http_referer = $CI->input->server('HTTP_REFERER');

        // visit
        if ($CI->session->userdata('ck_visit_ip') != $CI->input->server('REMOTE_ADDR')) {
            $CI->session->set_userdata('ck_visit_ip', $CI->input->server('REMOTE_ADDR'));
            
            $visit_referer = ($http_referer) ? $http_referer : $php_self;
            $CI->db->simple_query(" insert into ki_visit ( vi_ip, vi_date, vi_time, vi_referer, vi_agent ) values ( '".$CI->input->server('REMOTE_ADDR')."', '".TIME_YMD."', '".TIME_HIS."', '".$visit_referer."', '".$CI->input->server('HTTP_USER_AGENT')."' ) ");
        }

        // 관리자 페이지
        if ($is_super) {
            //
        }
        elseif ($CI->uri->segment(1) == ADM_F)
            show_404();

        $referer = parse_url($http_referer);
        $repself = str_replace('/index.php', '', $php_self);
        if (!empty($referer['path']) && $referer['path'] != $repself)
            $url = $http_referer;
        else
            $url = '/';

        define('URL', $url);
        define('IS_MEMBER', $is_member);
        define('SU_ADMIN', $is_super);
        define('MEMBER', serialize($member));
    }
}
?>