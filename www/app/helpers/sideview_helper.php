<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// 회원 레이어
function get_sideview($mb_id, $name='') {
    global $sideview;
    if (isset($sideview[$mb_id]) && $mb_id)
        return $sideview[$mb_id];

    $title_name = $name = str_replace(array("&#039;", "'", "\""), array("", "", "&#034;"), $name);
    
    if ($mb_id) {
        $tmp_name = '<span class="text-muted"><strong>';

        $CI =& get_instance();
        $CI->load->config('cf_icon');
        if ($CI->config->item('cf_use_icon')) {
            $mb_path    = '/member/'.substr($mb_id,0,2).'/';
            $icon_path  = $mb_path.$mb_id.'.gif';
            $named_path = $mb_path.'n_'.$mb_id.'.gif';
            
            if (file_exists(DATA_PATH.$icon_path)) {
                //$icon_width = $CI->config->item('cf_icon_width');
                //$icon_height = $CI->config->item('cf_icon_height');
                $mb_icon = "<img src='".DATA_DIR.$icon_path."' align='middle' alt='".$name."' /> ";   
                $tmp_name = $mb_icon.$tmp_name;
            }
            
            if (file_exists(DATA_PATH.$named_path)) {
                //$named_width = $CI->config->item('cf_named_width');
                //$named_height = $CI->config->item('cf_named_height');
                $name = "<img src='".DATA_DIR.$named_path."' align='middle' title='".$name."' alt='".$name."' />";                
            }                                    
        }
        
        $tmp_name .= $name.'</strong></span>';
        $title_mb_id = ''; // '['.$mb_id.']';
    }
    else {
        $tmp_name = '<span class="text-muted">'.$name.'</span>';
        $title_mb_id = '[비회원] ';
    }

    return $sideview[$mb_id] = "<a href=\"javascript:;\" onclick=\"javascript:showSideView(this, '".$mb_id."', &quot;".$name."&quot;);\" title=\"".$title_mb_id.$title_name."\">".$tmp_name."</a>";
}
?>