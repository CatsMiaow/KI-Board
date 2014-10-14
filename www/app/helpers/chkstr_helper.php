<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// 입력값 검사 상수
define('_RT_ALPHAUPPER_', 1); // 영대문자
define('_RT_ALPHALOWER_', 2); // 영소문자
define('_RT_ALPHABETIC_', 4); // 영대,소문자
define('_RT_NUMERIC_', 8);    // 숫자
define('_RT_HANGUL_', 16);    // 한글
define('_RT_SPACE_', 32);     // 공백
define('_RT_SPECIAL_', 64);   // 특수문자

// 문자열이 한글, 영문, 숫자, 특수문자로 구성되어 있는지 검사
function check_string($str, $options) {
    $CI =& get_instance();

    $s = '';
    for($i=0; $i<strlen($str); $i++) {
        $c = $str[$i];
        $oc = ord($c);

        // 한글
        if ($oc >= 0xA0 && $oc <= 0xFF) {
            if (strtoupper($CI->config->item('charset')) == 'UTF-8') {
                if ($options & _RT_HANGUL_) {
                    $s .= $c . $str[$i+1] . $str[$i+2];
                }
                $i+=2;
            } else {
                // 한글은 2바이트 이므로 문자하나를 건너뜀
                $i++;
                if ($options & _RT_HANGUL_) {
                    $s .= $c . $str[$i];
                }
            }
        }
        // 숫자
        else if ($oc >= 0x30 && $oc <= 0x39) {
            if ($options & _RT_NUMERIC_) {
                $s .= $c;
            }
        }
        // 영대문자
        else if ($oc >= 0x41 && $oc <= 0x5A) {
            if (($options & _RT_ALPHABETIC_) || ($options & _RT_ALPHAUPPER_)) {
                $s .= $c;
            }
        }
        // 영소문자
        else if ($oc >= 0x61 && $oc <= 0x7A) {
            if (($options & _RT_ALPHABETIC_) || ($options & _RT_ALPHALOWER_)) {
                $s .= $c;
            }
        }
        // 공백
        else if ($oc >= 0x20) {
            if ($options & _RT_SPACE_) {
                $s .= $c;
            }
        }
        else {
            if ($options & _RT_SPECIAL_) {
                $s .= $c;
            }
        }
    }

    // 넘어온 값과 비교하여 같으면 참, 틀리면 거짓
    return ($str == $s);
}
?>