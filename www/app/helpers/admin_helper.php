<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// 회원권한을 SELECT 형식으로 얻음
function get_mb_level_select($name, $slt_no='', $not_id='', $maxLv=10) {
    $not_id = (!$not_id) ? "id='".$name."' " : ''; 
    $str = "<select ".$not_id."name='".$name."' class='form-control'>";

    for ($i=1; $i<=$maxLv; $i++) {
        $slt = ($slt_no == $i) ? "selected='selected'" : '';
        $str .= "<option value='".$i."' ".$slt.">".$i."</option>";
    }

    $str .= '</select>';
    return $str;
}

// 작업아이콘 출력
function icon($act, $link, $target='_self') {

    $color = '';
    switch ($act) {
        case '작성': $icon = 'pencil'; break;
        case '추가': $icon = 'plus'; break;
        case '생성': $icon = 'plus'; break;
        case '수정': $icon = 'edit'; break;
        case '삭제': $icon = 'trash'; $color = 'btn-danger'; break; // remove
        case '보기': $icon = 'search'; break;
        case '미리보기': $icon = 'picture'; break;
    }

    $icon = '<span class="glyphicon glyphicon-'.$icon.'"></span>';
    if (strpos($link, 'javascript') === FALSE)
        $btn = "<a href='".RT_PATH.'/'.ADM_F.'/'.$link."' class='btn btn-sm btn-default ".$color."' target='".$target."' title='".$act."'>".$icon."</a>";
    else
        $btn = "<a href='javascript:;' class='btn btn-sm btn-default ".$color."' onclick=\"".$link."\" title='".$act."'>".$icon."</a>";

    return $btn;
}

// 게시판 그룹을 SELECT 형식으로 얻음
function get_group_select($name, $slt_id='', $not_id='') {
    global $gr_select; // 한번만 실행

    if (!$gr_select) {
        $CI =& get_instance();
        $CI->db->select('gr_id,gr_subject');
        $query = $CI->db->get('ki_board_group');
        $gr_select = $query->result_array();
    }
        
    $not_id = (!$not_id) ? "id='".$name."' " : ''; 
    $str = "<select ".$not_id."name='".$name."' class='form-control'>";
    foreach($gr_select as $row) {
        $slt = ($slt_id == $row['gr_id']) ? "selected='selected'" : '';
        $str .= "<option value='".$row['gr_id']."' ".$slt.">".$row['gr_subject']."</option>";
    }
    $str .= '</select>';
    return $str;
}

// 스킨경로를 얻는다
function get_skin_dir($skin, $name, $slt_skin='', $not_id='') {
    global $skin_file; // 한번만 실행

    if (!$skin_file) {
        $skin_file = array();
        $dirname = SKIN_PATH.$skin.'/';
        $handle = opendir($dirname);
        while ($file = readdir($handle)) {
            if($file == '.' || $file == '..') continue;

            if (is_dir($dirname.$file))
                $skin_file[] = $file;
        }
        closedir($handle);
        sort($skin_file);
    }

    $not_id = (!$not_id) ? "id='".$name."' " : ''; 
    $str = "<select ".$not_id."name='".$name."' class='form-control'>";
    foreach($skin_file as $row) {
        $option = $row;
        if (strlen($option) > 10)
            $option = substr($row, 0, 18) . "…";

        $slt = ($slt_skin == $row) ? "selected='selected'" : '';
        $str .= "<option value='".$row."' ".$slt.">".$option."</option>";
    }
    $str .= '</select>';
    return $str;
}

// array_fill_keys (PHP 5 >= 5.2.0) -_-
function array_false($arr, $is_key=FALSE) {
    if ($is_key)
        $arr = array_keys($arr);

    foreach ($arr as $val) {
        $row[$val] = FALSE;
    }
    return $row;
}

// rm -rf 옵션 : exec(), system() 함수를 사용할 수 없는 서버 또는 win32용 대체
// www.php.net 참고 : pal at degerstrom dot com
function rm_rf($file) {
    if (file_exists($file)) {
        @chmod($file,0777);
        if (is_dir($file)) {
            $handle = opendir($file);
            while($filename = readdir($handle)) {
                if ($filename != '.' && $filename != '..')
                    rm_rf($file.'/'.$filename);
            }
            closedir($handle);
            rmdir($file);
        } else
            unlink($file);
    }
}
?>