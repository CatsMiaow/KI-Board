<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// 경고메세지를 경고창으로
function alert($msg='', $url='') {
	$CI =& get_instance();

	if (!$msg) $msg = '올바른 방법으로 이용하세요.';

	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=".$CI->config->item('charset')."\">";
	echo "<script type='text/javascript'>alert('".$msg."');";
	if (!$url)
        echo "history.go(-1);";
    echo "</script>";
    if ($url)
        goto_url($url);
	exit;
}

// 경고메세지 출력후 창을 닫음
function alert_close($msg) {
	$CI =& get_instance();

	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=".$CI->config->item('charset')."\">";
	echo "<script type='text/javascript'> alert('".$msg."'); window.close(); </script>";
	exit;
}

// 해당 url로 이동
function goto_url($url) {
	$temp = parse_url($url);
	if (empty($temp['host'])) {
		$CI =& get_instance();
		$url = ($temp['path'] != '/') ? RT_PATH.'/'.$url : $CI->config->item('base_url').RT_PATH;
	}
	echo "<script type='text/javascript'> location.replace('".$url."'); </script>";
	exit;
}

// 불법접근을 막도록 토큰을 생성하면서 토큰값을 리턴
function get_token() {
	$CI =& get_instance();

	$token = md5(uniqid(rand(), TRUE));
	$CI->session->set_userdata('ss_token', $token);

	return $token;
}

// POST로 넘어온 토큰과 세션에 저장된 토큰 비교
function check_token($url=FALSE) {
	$CI =& get_instance();
	// 세션에 저장된 토큰과 폼값으로 넘어온 토큰을 비교하여 틀리면 에러
	if ($CI->input->post('token') && $CI->session->userdata('ss_token') == $CI->input->post('token')) {
		// 맞으면 세션을 지운다. 세션을 지우는 이유는 새로운 폼을 통해 다시 들어오도록 하기 위함
		$CI->session->unset_userdata('ss_token');
	}
	else
		alert('Access Error',($url) ? $url : $CI->input->server('HTTP_REFERER'));

	// 잦은 토큰 에러로 인하여 토큰을 사용하지 않도록 수정
	// $CI->session->unset_userdata('ss_token');
	// return TRUE;
}

function check_wrkey() {
	$CI =& get_instance();
	$key = $CI->session->userdata('captcha_keystring');
	if (!($key && $key == $CI->input->post('wr_key'))) {
		$CI->session->unset_userdata('captcha_keystring');
	    alert('정상적인 접근이 아닙니다.', '/');
	}
}
?>