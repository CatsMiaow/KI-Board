<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* 사용자 정의 */
define('ADMIN', ''); // 최고관리자
define('ADM_F', 'adm'); // 관리자폴더

define('RT_PATH', ''); // ex) /test
define('SKIN_PATH', $_SERVER['DOCUMENT_ROOT'].RT_PATH.'/skin/');

define('IMG_DIR', RT_PATH.'/img');
define('JS_DIR',  RT_PATH.'/js');
define('CSS_DIR', RT_PATH.'/css');
define('DATA_DIR', RT_PATH.'/data');
define('DATA_PATH', $_SERVER['DOCUMENT_ROOT'].DATA_DIR);
define('EDT_DIR', RT_PATH.'/editor');

define('TIME_YMD', date('Y-m-d', time()));
define('TIME_HIS', date('H:i:s', time()));
define('TIME_YMDHIS', date('Y-m-d H:i:s', time()));


/* End of file constants.php */
/* Location: ./application/config/constants.php */