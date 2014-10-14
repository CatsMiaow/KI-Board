<?php
class Thumbnail extends CI_Controller {
    private $mime = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

    function __construct() {
        parent::__construct();
    }

    function _remap($type, $seg) {
        $noimg = $this->input->server('DOCUMENT_ROOT').IMG_DIR.'/noimg.png';
        if ($type == 'index' || !isset($seg[0], $seg[1])) {
            $this->output($noimg, 'png');
            return false;
        }
        
        $bo_table = $seg[0]; $wr_id = $seg[1];
        switch ($type) {
            case 'sns':
                $file = DATA_PATH.'/temp/thumb_sns'.$bo_table.$wr_id.'.jpg';
                if (!file_exists($file)) {
                    $this->db->select('bf_file, bf_type');
                    $this->db->where_in('bf_type', array_keys($this->mime))->order_by('bf_no', 'asc');
                    $row = $this->db->get_where('ki_board_file', array(
                        'bo_table' => $bo_table,
                        'wr_id' => $wr_id,
                        'bf_editor' => 1
                    ), 1)->row_array();

                    if (!isset($row['bf_file'])) {
                        $file = $noimg;
                    }
                    else { // 공통 소스로 빼야 할 듯
                        $this->load->library('image_lib', array(
                            'source_image' => DATA_PATH.'/file/'.$bo_table.'/'.$row['bf_file'],
                            'new_image' => $file,
                            'create_thumb' => TRUE,
                            'thumb_marker' => FALSE,
                            'maintain_ratio' => TRUE,
                            'width' => 200,
                            'height' => 200
                        ));
                        if (!$this->image_lib->resize())
                            $file = $noimg;
                    }
                }
                $this->output($file);
            break;
            default: return false; break;
        }
    }

    private function output($file, $type='') {
        if (!$type) {
            $info = @getimagesize($file);
            $type = $this->mime[$info[2]];
        }

        header('Content-Type: image/'.$type);
        switch ($type) {
            case 'gif':
                $im = imagecreatefromgif($file);
                imagegif($im);
            break;
            case 'jpeg':
                $im = imagecreatefromjpeg($file);
                imagejpeg($im);
            break;
            case 'png':
                $im = imagecreatefrompng($file);
                imagepng($im);
            break;
            default: return false; break;
        }
        imagedestroy($im);
    }
}