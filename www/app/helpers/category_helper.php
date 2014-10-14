<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function make_category($args=array()) {
    extract($args); // type, code, id, lst
    
    if (isset($lst)) { $lo = 'true';  $first = '전체'; }
    else             { $lo = 'false'; $first = '선택하세요'; }
    
    $is_multi = FALSE;
    if (is_array($code))
        $is_multi = TRUE;
    
    $CI =& get_instance();
    $CI->load->model('Category_model');
    $result = $CI->Category_model->get_category($type, TRUE);
    
    $topt = $sopt = '';
    foreach ($result as $row) {
        if (is_numeric($row['code']))
            $topt .= "<option value='".$row['code']."'>".$row['ca_name']."</option>";
        else
            $sopt .= "<option value='".$row['code']."'>".$row['ca_name']."</option>";
    }
    
    $change = " onchange='changeCate(this, ".$lo.");' ";
    $scate = ($sopt) ? "<select id='sub_".$id."' class='form-control input-sm auto' style='display:none;'>".$sopt."</select>" : ''; 
    
    if (!$is_multi) {
        $result['code']   = $code;
        $result['select'] = "<select id='".$id."1' class='form-control input-sm auto' name='".$id."'".$change."><option value=''>".$first."</option>".$topt."</select>";
    }
    else {
        $no = 1;
        $list = array();
        foreach ($code as $key => $row) {
            $list[$key] = new stdClass();
            $list[$key]->select = "<select id='".$id.$no."' class='form-control input-sm auto' name='".$id."[".$key."]'".$change."><option value=''>".$first."</option>".$topt."</select>";
            $no++;            
        }
        
        $result['code'] = implode("','", $code);
        $result['list'] = $list;
    }
    
    $result['scate'] = $scate;
    return $result;
}
?>