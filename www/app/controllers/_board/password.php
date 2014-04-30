<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class Password extends Widget {
	function index() {
		$seg =& $this->seg;
		
		$w     	    = $seg->get('w');          // 모드
		$wr_id	    = $seg->get('wr_id');      // 게시물아이디
		$comment_id = $seg->get('comment_id'); // 코멘트아이디
		$qstr  		= $seg->output();

		switch ($w) {
			case 'u' : $action = 'board/'.BO_TABLE.'/write'.$qstr; break;
			case 'd' :
				$qstr = $seg->replace('wr_id', '', $qstr);
				$action = '_trans/board_write/delete';
			break;
			case 'x' : $action = '_trans/board_comment/delete'; break;
			case 's' :
				if (IS_ADMIN) // 관리자 통과
					goto_url('board/'.BO_TABLE.'/view/wr_id/'.$wr_id);
						
				$write = $this->Basic_model->get_write(BO_TABLE, $wr_id, 'mb_id');
				
				// 회원의 글이라면
				if ($write['mb_id']) {
					$member =& $this->member;
					if (IS_MEMBER && $member['mb_id'] == $write['mb_id']) // 자신의 글
						goto_url('board/'.BO_TABLE.'/view/wr_id/'.$wr_id);
					else {
						$msg = '글을 읽을 권한이 없습니다.';
					 	if (!IS_MEMBER)
					 		$msg .= '\\n\\n답글의 경우 비회원은 본인글을 읽은 후 읽어 주시기 바랍니다.';
						alert($msg);
					}
				}
				else // 비회원
					$action = '_trans/board_password/check';
			break;
			default: alert('잘못된 접근입니다.'); break;
		}
		
		$head = array('title' => '비밀번호 확인');
		$data = array(
			'w' 		 => $w,
			'wr_id' 	 => $wr_id,
			'comment_id' => $comment_id,
			'action' 	 => $action,
			'qstr' 		 => $seg->replace('w,comment_id', '', $qstr)
		);

		widget::run('head', $head);
		$this->load->view('board/password', $data);
		widget::run('tail');
	}
}