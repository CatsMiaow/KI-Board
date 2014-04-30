<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* CI 2.1.4
로그 메시지에 PHP_SELF 추가
*/
class MY_Log extends CI_Log {
	function __construct() {
		parent::__construct();
	}
	
	public function write_log($level = 'error', $msg, $php_error = FALSE)
	{
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}

		$level = strtoupper($level);

		if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
		{
			return FALSE;
		}

		$filepath = $this->_log_path.'log-'.date('Y-m-d').'.php';
		$message  = '';

		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}

		if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
		{
			return FALSE;
		}

		// $message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";

		// 2013.11.04, ADD `PHP_SELF`
		$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg.' <-- '.$_SERVER['PHP_SELF']."\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, FILE_WRITE_MODE);
		return TRUE;
	}
}