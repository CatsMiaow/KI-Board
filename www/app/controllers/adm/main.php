<?php
class Main extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->helper('number');
        define('WIDGET_SKIN', 'admin');
        // $this->output->enable_profiler(TRUE);
    }

    function index() {
        // $this->output->cache(1440); // 캐시 되고 있는동안 common 작동 안함
        
        // 계정의 사용량을 구함 
        $account_space = `du -sb`; 
        $account_space = substr($account_space,0,strlen($account_space)-3);
        // DATA 폴더의 용량을 구함
        $data_path = DATA_PATH; 
        $data_space = `du -sb $data_path`; 
        $data_space = substr($data_space,0,strlen($data_space)-8); 

        // GD 버젼
        $gd_support = extension_loaded('gd');
        if ($gd_support) {
            $gd_info = gd_info();
            $gd_version = $gd_info['GD Version'];
        } else {
            $gd_version = 'GD가 설치되지 않음';
        }

        // MySQL 버전
        $query = $this->db->query('select version() as ver');
        $row = $query->row_array();
        $db_version = $row['ver'];

        /*        
        // http://kr2.php.net/manual/kr/function.mysql-stat.php
        $mysql_stat = explode('  ', mysql_stat());
        $a = explode(':', $mysql_stat[0]);
        $db_date = $a[0] . ': ';
        $days = floor($a[1]/86400);
        if ($days)
            $db_date .= $days . '일 ';
        $hours = (floor($a[1]/3600)%24);
        if ($hours)
            $db_date .= $hours . '시간 ';
        $min = (floor($a[1]/60)%60);
        if ($min)
            $db_date .= $min . '분';
        
        $t = explode(':', $mysql_stat[2]);
        
        $db_status = $mysql_stat[1].'<br/>';
        $db_status .= $t[0].': '.number_format($t[1]).'<br/>';
        $db_status .= $mysql_stat[3].'<br/>';
        $db_status .= $mysql_stat[4].'<br/>';
        $db_status .= $mysql_stat[5].'<br/>';
        $db_status .= $mysql_stat[6].'<br/>';
        $db_status .= $mysql_stat[7].'<br/>';
        */


        $head = array('title' => '관리자 페이지');
        $data = array(
            'os_version' => php_uname('r'),
            'ip_addr' => gethostbyname(trim(`hostname`)),
            'account_space' => byte_format($account_space),
            'data_space' => byte_format($data_space),
            'code_space' => byte_format($account_space - $data_space),
            'php_version' => phpversion(),
            'zend_version' => zend_version(),
            'gd_version' => $gd_version,
            'max_filesize' => get_cfg_var('upload_max_filesize'),
            'db_version' => $db_version,
            'db_date' => '', // $db_date
            'db_status' => '' // $db_status
        );
        
        widget::run('head', $head);
        $this->load->view(ADM_F.'/main', $data);
        widget::run('tail');
    }
}
?>