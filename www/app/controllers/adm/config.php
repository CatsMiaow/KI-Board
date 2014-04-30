<?php
class Config extends CI_Controller {
	function __construct() {
		parent::__construct();
		define('WIDGET_SKIN', 'admin');
	}

	function index() {
		if (SU_ADMIN != ADMIN) {
			alert('최고관리자만 접근할 수 있습니다.');
			return false;
		}

		function get_cf_custom($file) {
			$str = str_replace(
				array('<?','?>','\'','"'), '',
				file_get_contents($_SERVER['DOCUMENT_ROOT'].'/app/config/cf_'.$file.'.php')
			);
			preg_match_all("/config\[(.*)\]\s+=\s+(.*);\s+\/\/(.*)/", $str, $match);

			$list = array();
			foreach ($match[1] as $i => $v) {
				$list[$i] = new stdClass();
				$list[$i]->title = $v;
				$list[$i]->value = $match[2][$i];
				$list[$i]->comment = $match[3][$i];
			}
			return $list;
		}
		

		$head = array('title' => '환경설정');
		$data = array(
			'basic'    => get_cf_custom('basic'),
			'board'	   => get_cf_custom('board'),
			'icon'	   => get_cf_custom('icon'),
			'register' => get_cf_custom('register')
		);

		widget::run('head', $head);
		$this->load->view(ADM_F.'/config', $data);
		widget::run('tail');
	}
}
?>