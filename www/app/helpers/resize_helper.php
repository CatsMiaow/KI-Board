<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function resize_thumb($filename, $bo_table, $width='', $height='', $crop=FALSE) {
	if (!$filename || !$width)
        return FALSE;

    if ($crop) {
        $w = explode(',', $width);
        $h = explode(',', $height);
        $config['x_axis'] = ($w[1] / 2) - ($w[0] / 2);
        $config['y_axis'] = ($h[1] / 2) - ($h[0] / 2);
        $width  = $w[0];
        $height = $h[0];
    }

	$source_file = DATA_PATH.'/file/'.$bo_table.'/'.$filename;
    $thumb_path  = '/file/'.$bo_table.'/thumb/'.$width.'px_'.$filename;    
	$img_html    = "<img src='".DATA_DIR.$thumb_path."' alt='이미지'/>";
	
	if (file_exists(DATA_PATH.$thumb_path))
		return $img_html;
    
    $config['source_image']   = $source_file;
    $config['new_image']	  = DATA_PATH.$thumb_path;
    $config['create_thumb']   = TRUE;
    $config['thumb_marker']   = FALSE;
    $config['maintain_ratio'] = (!$height) ? TRUE : FALSE;
    $config['width'] 		  = $width;
    $config['height']		  = (!$height) ? $width : $height;
            
	$CI =& get_instance();
	$CI->load->library('image_lib');
    $CI->image_lib->initialize($config); 
    if (($crop && $CI->image_lib->crop()) || $CI->image_lib->resize())
    	return $img_html;
   	else
   		return '이미지 생성에 실패 하였습니다. (jpg,gif,jpeg,png 파일이 아닙니다.)';
}

function resize($filename) {
	if (is_array($filename))
		$filename = $filename[0];
	
	preg_match("/src=[\"'](.*\/)(.*\.(jpg|gif|jpeg|png))[\"']/i", $filename, $files);
	
	if (!isset($files[0]))
		return $filename; // FALSE;
	
	$CI =& get_instance();
	if ($files[1] == $CI->config->item('base_url').DATA_DIR.'/file/'.BO_TABLE.'/') {
		$size = @getimagesize(DATA_PATH.'/file/'.BO_TABLE.'/'.$files[2]);
		if (isset($size) && $size[0] > RESIZE_WIDTH)
			return "<img src='".$files[1].$files[2]."' width='".RESIZE_WIDTH."' alt='이미지' onclick='javascript:resize(this, ".$size[0].", ".$size[1].");' style='cursor:pointer;'/>";
		else
			return "<img src='".$files[1].$files[2]."' alt='이미지'/>";
	}
	else
		return $filename;
}

function resize_content($content) {
	return preg_replace_callback('/\<img[^\<\>]*\>/i', 'resize', $content);
}
?>