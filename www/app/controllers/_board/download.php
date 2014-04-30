<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class Download extends Widget {
	function index() {
		$seg =& $this->seg;
		$wr_id = $seg->get('wr_id');
		$no    = $seg->get('no');
		
		// 쿠키에 저장된 ID값과 넘어온 ID값을 비교하여 같지 않을 경우 오류 발생
		// 다른곳에서 링크 거는것을 방지하기 위한 코드
		if (!$this->session->userdata('ss_view_'.BO_TABLE.'_'.$wr_id))
		    alert('잘못된 접근입니다.');
		
		$CI =& get_instance();
		$CI->load->model('Board_file_model');
		$file = $CI->Board_file_model->get_file(BO_TABLE, $wr_id, $no);
		if (!isset($file['bf_file']))
		    alert_close('파일 정보가 존재하지 않습니다.');
		
		$board  =& $this->board;
		$member =& $this->member;
		if ($member['mb_level'] < $board['bo_download_level']) {
		    $alert_msg = '다운로드 권한이 없습니다.';
		    if (IS_MEMBER)
		        alert($alert_msg);
		    else
		        alert($alert_msg."\\n\\n회원이라면 로그인 후 이용하세요.", 'member/login/qry/'.url_encode('board/'.BO_TABLE.'/view/wr_id/'.$wr_id));
		}
		
		// 다운수 증가
		$ss_name = 'ss_down_'.BO_TABLE.'_'.$wr_id.'_'.$no;
		if (!$this->session->userdata($ss_name)) {
		    // 다운로드 카운트 증가
		    $CI->Board_file_model->file_down_update(BO_TABLE, $wr_id, $no);
			$this->session->set_userdata($ss_name, TRUE);
		}

		$filepath = addslashes(DATA_PATH.'/file/'.BO_TABLE.'/'.$file['bf_file']);
		if (file_exists($filepath)) {
			if (preg_match("/^utf/i", $this->config->item('charset')))
			    $original = urlencode($file['bf_source']);
			else
			    $original = $file['bf_source'];
			
			$this->load->helper('download');
			if (!force_download($original, file_get_contents($filepath)))
				 alert('파일을 찾을 수 없습니다.');
		}
		else
		    alert('파일을 찾을 수 없습니다.');
	}
}