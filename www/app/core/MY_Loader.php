<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* CI 2.2.0
SKIN 폴더 경로 변경( _ci_view_paths )
SKIN 기본 확장자 변경( .php -> .html )
*/
class MY_Loader extends CI_Loader {
	function __construct() {
		parent::__construct();
		$this->_ci_view_paths = array(SKIN_PATH => TRUE);
	}
	
	public function view($view, $vars = array(), $return = FALSE) {
		return $this->_ci_load(array('_ci_view' => $view.'.html', '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
	}
}