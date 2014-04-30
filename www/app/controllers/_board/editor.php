<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class Editor extends Widget {
    function index() {
        $member =& $this->member;
        $board  =& $this->board;
        
        $seg =& $this->seg;
		$type = $seg->get('type');

        switch ($type) {
            case 'image': $title = '이미지'; break;
            case 'file':  $title = '파일'; break;
            case 'media': $title = '멀티미디어'; break;
            default: alert_close('잘못된 접근입니다.'); break; 
        }
        
        if ($member['mb_level'] < $board['bo_upload_level'])
            alert_close('업로드 권한이 없습니다.');
        
        $head = array('title' => $title.' 첨부');
        $data = array(
            'upload_size' => $board['bo_upload_size'],
            'upload_ext' => '*.'.str_replace('|', ';*.', $board['bo_upload_ext'])
        );
        
        widget::run('head', $head);
		$this->load->view('board/editor_'.$type, $data);
		widget::run('tail');
	}
}