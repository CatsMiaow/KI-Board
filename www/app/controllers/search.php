<?php
class Search extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->helper('textual');
		$this->load->library('pagination');
		$this->load->model('Search_model');
		define('WIDGET_SKIN', 'main');
		// $this->output->enable_profiler(TRUE);
	}
	
	function index() {
		$this->qry();
	}

	function qry() {
		$this->load->library('querystring');
 		$param =& $this->querystring;
		$stx = $param->get('stx');

		$type = $this->uri->segment(5, 'write');
		$page = $this->uri->segment(4, 1);
		if (!$stx) goto_url('/');
		
		$ori_stx = $stx;
		$member = unserialize(MEMBER);
		
		// 검색 가능 게시판
		$result = $this->Search_model->search_board($member['mb_level'], $stx);
		
		$boards = array();
		foreach ($result as $row) {
			$boards[] = $row['bo_table'];
			$levels[$row['bo_table']] = $row['bo_read_level'];
		}
		
		$config['suffix'] = '/'.$type.$param->output();
		$config['base_url'] = RT_PATH.'/search/qry/page/';
		$config['per_page'] = 20;
		$config['uri_segment'] = 4;
		
		$offset = ($page - 1) * $config['per_page'];
		$result = $this->Search_model->list_result($type, $stx, $config['per_page'], $offset, $boards);
		
		$config['total_rows'] = $result['total_count'];
		$this->pagination->initialize($config);


		$list = array();
		if ($type == 'write') {
			// 게시글
			foreach ($result['qry'] as $i => $row) {
				$bo_table = $row['bo_table'];

				$href = RT_PATH.'/board/'.$bo_table.'/view/wr_id/'.$row['wr_id'].'?sfl=wr_subject.wr_content&stx='.$ori_stx;
				$row['wr_content'] = preg_replace("/\s+&nbsp;+/", '', get_text(strip_tags(htmlspecialchars_decode($row['wr_content']))));

				$list[$i] = new stdClass();
				$list[$i]->href = $href;
				$list[$i]->subject = search_font(get_text($row['wr_subject']), $stx);
				$list[$i]->content = ($levels[$bo_table] <= $member['mb_level']) ? search_font(cut_str($row['wr_content'], 300), $stx) : '';
					
				// $list[$i]->name = $row['wr_name'];
				$list[$i]->datetime = substr($row['wr_datetime'], 0, 10);
				$list[$i]->is_comment = FALSE;
				$i++;
			}
		}
		else {
			// 댓글
			foreach ($result['qry'] as $i => $row) {
				$bo_table = $row['bo_table'];

				$href = RT_PATH.'/board/'.$bo_table.'/view/wr_id/'.$row['wr_id'].'?sfl=wr_subject.wr_content&stx='.$ori_stx.'#c_'.$row['co_id'];
				$row['co_content'] = get_text($row['co_content']);
					
				$list[$i] = new stdClass();
				$list[$i]->href = $href;
				$list[$i]->content = ($levels[$bo_table] <= $member['mb_level']) ? search_font(cut_str($row['co_content'], 300), $stx) : '';

				// $list[$i]->name = $row['co_name'];
				$list[$i]->datetime = substr($row['co_datetime'], 0, 10);
				$list[$i]->is_comment = TRUE;
			}
		}

		
		$head = array('title' => '검색어: '.get_text(stripslashes($stx)));
		$data = array(
			'stx' => $ori_stx,
			'type' => $type,
			'list' => $list,
			'total_count' => number_format($config['total_rows']),
			'paging' => $this->pagination->create_links()
		);
		
		widget::run('head', $head);
		$this->load->view('main/search', $data);
		widget::run('tail');
	}
}
?>