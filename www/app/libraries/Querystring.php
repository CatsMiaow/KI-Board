<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* 테스트
 * URL: http://*.com/main/index?board=free&idx=123&page=456&sst=subject&sod=asc&stx=%ED%85%8C%EC%8A%A4%ED%8A%B8
 * $this->load->library('querystring', NULL, 'param');
 * echo 'board: '.$param->get('board').'<br/>';
 * echo 'idx: '.$param->get('idx').'<br/>';
 * echo 'page: '.$param->get('page').'<br/>';
 * echo 'stx: '.$param->get('stx').'<br/>';
 * echo '전체주소: '.$param->output().'<br/>';
 * echo 'page, idx 삭제: '.$param->replace('page,idx').'<br/>';
 * echo 'page 값 변경: '.$param->replace('page', '789').'<br/>';
 * echo '정렬: '.$param->sort('subject', 'desc');
*/

// 검색 파라미터
class Querystring {
    private $CI;
    private $required = array();

    public function __construct($param=array()) {
        $this->CI =& get_instance();

        // Config
        foreach ($param as $key => $val) {
            if (isset($this->$key))
                $this->$key = $val;
        }

        parse_str($this->CI->input->server('QUERY_STRING'), $parse);
        $this->qstr = array_map(array('querystring','escape'), $parse);

        if (isset($this->required)) { // 기본 파라미터
            foreach ($this->required as $key => $val) {
                if (is_int($key)) {
                    unset($this->required[$key]);
                    $this->required[$val] = '';
                }
            }

            $this->qstr = array_merge($this->required, $this->qstr);
        }
    }

    // 보안
    private function escape($v) {
        return $this->CI->db->escape_str($v);
    }

    // 값 가져오기
    public function get($param, $value=FALSE) {
        if (!isset($this->qstr[$param]))
            return $value;

        return $this->qstr[$param];
    }

    // 전체 배열
    public function getArray() {
        return $this->qstr;
    }

    // 전체 주소
    public function output() {
        if ($this->qstr)
            return '?&'.http_build_query($this->qstr);
    }

    // 쿼리스트링 수정
    public function replace($key, $val='', $qstr='') {
        if (!$key)
            return FALSE;

        if (!$qstr)
            $qstr = $this->output();

        $keys = explode(',', $key);
        foreach ($keys as $row)
            $srh[] = '/&'.$row.'=[a-z0-9_-]+/i';
        
        if ($val && !isset($keys[1])) {
            $val = '&'.$key.'='.$val;
            if (strpos($qstr, '&'.$key.'=') === FALSE)
                return $qstr .= $val;
        }

        return preg_replace($srh, $val, $qstr);
    }

    // 필드 정렬
    public function sort($sst, $sod='desc') {
        if ($this->get('sst') == $sst)
            $param_qstr = $this->replace('sod', ($this->get('sod') == 'asc') ? 'desc' : 'asc');
        else {
            $param_qstr = $this->replace('sst,sod').'&sst='.$sst.'&sod='.$sod;
            if (count($this->qstr) < 1)
                $param_qstr = '?'.$param_qstr;
        }

        return $param_qstr;
    }
}