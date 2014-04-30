<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* 테스트
 * URL: http://*.com/test/board/free/idx/123/page/456/sst/subject/sod/asc/stx/테스트
 * $this->load->library('segment', array(
 *	'offset' => 2
 *	'required' => array('default','field','setting')
 * ), 'seg');
 * echo 'board: '.$seg->get('board').'<br/>';
 * echo 'idx: '.$seg->get('idx').'<br/>';
 * echo 'page: '.$seg->get('page').'<br/>';
 * echo 'stx: '.$seg->get('stx').'<br/>';
 * echo '전체주소: '.$seg->output().'<br/>';
 * echo 'page 값의 위치: '.$seg->get_order('page').'<br/>';
 * echo 'page, idx 삭제: '.$seg->replace('page,idx').'<br/>';
 * echo 'page 값 변경: '.$seg->replace('page', '789').'<br/>';
 * echo '정렬: '.$seg->sort_link('subject', 'desc');
*/

// 검색 파라미터
class Segment {
	private $CI, $seg, $base_url;
	private $offset = 4;
	private $required = array();

	public function __construct($param=array()) {
		$this->CI =& get_instance();

		// Config
		foreach ($param as $key => $val) {
			if (isset($this->$key))
				$this->$key = $val;
		}

        $this->base_url = implode('/', array_slice($this->CI->uri->segment_array(), 0, $this->offset-1));
		$this->seg = array_map(array('segment','escape'), $this->CI->uri->uri_to_assoc($this->offset, $this->required));
	}

	// 보안
	private function escape($v) {
		return $this->CI->db->escape_str($v);
	}

	// 값 가져오기
	public function get($seg, $value=FALSE) {
		if (!isset($this->seg[$seg]))
			return $value;
		
		return $this->seg[$seg];
	}
    
	// 값의 위치
    public function pos($seg) {
        $odr = array_search($seg, array_keys($this->seg));
        if ($odr !== FALSE)
            return ($odr + 1) * 2 + ($this->offset - 1);
        else
            return FALSE;
    }
	
	// 쿼리스트링
	public function output() {
		if ($this->seg)
			return '/'.$this->CI->uri->assoc_to_uri($this->seg);
	}

	// 쿼리스트링 수정
	public function replace($key, $val='', $qstr='') {
		if (!$key)
			return FALSE;

		if (!$qstr)
			$qstr = $this->output();

		$keys = explode(',', $key);
		foreach ($keys as $row)
			$srh[] = '(/'.$row.'/[a-z0-9_-]+)';
		
		if ($val && !isset($keys[1])) {
			$val = '/'.$key.'/'.$val;
			if (strpos($qstr, '/'.$key.'/') === FALSE)
				return $qstr .= $val;
		}

		return preg_replace($srh, $val, $qstr);
	}

	// 필드 정렬
	public function sort($sst, $sod='asc') {		
		if ($this->get('sst') == $sst)
			$seg_qstr = $this->replace('sod', ($this->get('sod') == 'asc') ? 'desc' : 'asc');
		else
			$seg_qstr = $this->replace('sst,sod').'/sst/'.$sst.'/sod/'.$sod;

		return '/'.$this->base_url.$seg_qstr;
	}
}