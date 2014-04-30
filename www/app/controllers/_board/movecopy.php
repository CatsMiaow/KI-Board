<?php
class Movecopy extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Board_mvcp_model');
		$this->load->helper('board');
	}
	
	function index() {
		$bo_table = $this->input->post('bo_table');
		$wr_id = $this->input->post('wr_id');
		$sw = $this->input->post('sw');

		$board = $this->Basic_model->get_board($bo_table, 'bo_admin', TRUE);
		$member = unserialize(MEMBER);

		define('IS_ADMIN', is_admin($member, $board));

		// 게시판 관리자 이상 복사, 이동 가능
		if (!IS_ADMIN)
		    show_404();
		
		if (!$wr_id)
			alert_close('잘못된 접근입니다.');

		switch ($sw) {
			case 'move' : $act = '이동'; break;
			case 'copy' : $act = '복사'; break;
			default: alert_close('잘못된 접근입니다.'); break;
		}

		$result = $this->Board_mvcp_model->list_move_copy($bo_table, $member['mb_id']);
		
		$list = array();
		$save_gr_subject = '';
		foreach($result as $i => $row) {
			$list[$i] = new stdClass();
			$list[$i]->bo_table = $row['bo_table'];

			$span = ($save_gr_subject == $row['gr_subject']) ? "<span style='color:#cccccc;'>" : '<span>';

			$list[$i]->gr_subject = $span.$row['gr_subject'].' &gt; </span>';
			$list[$i]->bo_subject = $row['bo_subject'];
			
			$save_gr_subject = $row['gr_subject'];
		}
		
		$head = array('title' => '게시물 '.$act);
		$data = array(
			'sw' => $sw,
			'bo_table' => $bo_table,
			'wr_id' => serialize($wr_id),
			'act' => $act,
			'list' => $list
		);
		
		widget::run('head', $head);
		$this->load->view('board/movecopy', $data);
		widget::run('tail');
	}
}
?>