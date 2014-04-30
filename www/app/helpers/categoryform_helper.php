<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function get_categoryform($t_code, $s_code) {
	$c = '';
	$first_tc = key(array_slice($t_code, 0, 1, TRUE));
	$last_tc = key(array_slice($t_code, -1, 1, TRUE));

	foreach ($t_code as $key => $val) {
		$t_col = $t_last = $t_first = FALSE;

		if (isset($s_code[$key]))
			$t_col = TRUE;

		if ($key == $last_tc) 
			$t_last = (!$t_col) ? 'last_end' : 'end';

		if ($key == $first_tc)
			$t_first = TRUE;
		
		$t = _cHtml($key, $val, $t_col, $t_last, $t_first);

		// 최상위 분류 출력
		$c .= $t;

		if (isset($s_code[$key])) {
			$ckey = $cval = $pkey = $pval = FALSE;
			$subcode = $s_code[$key];
			$last_sc = key(array_slice($subcode, -1, 1, TRUE));

			$subkey = array_keys($subcode);
			foreach($subcode as $nkey => $nval) {
				$s_col = $s_last = $s_first = FALSE;

				if (!$ckey) {
					if ($nkey == $last_sc)
						$c .= _cHtml($key.'-'.$nkey, $nval, FALSE, TRUE, TRUE);

					$ckey = $nkey;
					$cval = $nval;
					continue;
				}

				// 이전
				$pLen = strlen($pkey);
				$pPar = substr($pkey, 0, $pLen-3);

				// 현재
				$cLen = strlen($ckey);
				$cPar = substr($ckey, 0, $cLen-3);

				// 다음
				$nLen = strlen($nkey);
				$nPar = substr($nkey, 0, $nLen-3);

				if ($ckey == $nPar)
					$s_col = TRUE;
				
				if (!$pkey || $pkey == $cPar)
					$s_first = TRUE;

				// echo '<PRE>';
				// echo $ckey.'</br>';
				// print_r(array_filter($subkey, array(new arr_fun($ckey, $cLen, $cPar), 'cback')));

				if ($cLen < $nLen && !array_filter($subkey, array(new arr_fun($ckey, $cLen, $cPar), 'cback')))
					$s_last = 'end';
				else if ($cLen > $nLen)
					$s_last = $cLen - $nLen;

				$sCode = $key.'-'.$ckey;
				$s = _cHtml($sCode, $cval, $s_col, $s_last, $s_first);

				// 마지막 분류 추가
				if ($nkey == $last_sc) {
					$s_col = $s_first = FALSE;
					if ($nPar == $ckey)
						$s_first = TRUE;

					$s_last = $nLen;

					$s .= _cHtml($key.'-'.$nkey, $nval, $s_col, $s_last, $s_first);
				}

				$c .= $s;

				// 현재 -> 이전
				$pkey = $ckey;
				$pval = $cval;

				// 다음 -> 현재
				$ckey = $nkey;
				$cval = $nval;
			}
		}
	}

	return $c;
}

class arr_fun {
    function arr_fun($key, $len, $par) {
		$this->key = $key;
		$this->len = $len;
		$this->par = $par;
	}
    function cback($var) {
		if ($this->len == strlen($var) &&
			$this->par == substr($var, 0, $this->len-3) &&
			substr($this->key, -3) < substr($var, -3)
		) return $var;
    }
}

function _cHtml($code, $name, $col=FALSE, $last=FALSE, $first=FALSE) {
	if ($col) {
		$class = 'k_tree_on';
		$btn   = "<button type='button'>+</button>";
		$end = '';
	}
	else {
		$class = 'k_tree_off';
		$btn = '';
		$end = '</li>';
	}

	$first = ($first) ? '<ul>' : FALSE;

	if ($last) {
		$class = 'k_tree_last '.$class;

		if (is_numeric($last)) {
			$last = $last / 3;
			$end = '</li>';
			$end .= repeater('</ul></li>', $last);
		}
		else if ($last == 'last_end')
			$end = '</li></ul>';
	}

	return $first."<li id='C_".$code."' class='".$class."'>".$btn."<span class='k_tree_label'>".$name."</span>".$end;
}
?>