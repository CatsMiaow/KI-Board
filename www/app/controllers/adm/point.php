<?php
class Point extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model(ADM_F.'/Point_model');
		$this->load->library(array('form_validation', 'pagination', 'querystring'));
		$this->load->helper(array('admin', 'sideview'));
		define('WIDGET_SKIN', 'admin');

		if (!$this->config->item('cf_use_point'))
			goto_url(ADM_F);
	}

	function lists() {
		$config = array(
			array('field'=>'mb_id', 'label'=>'아이디', 'rules'=>'trim|required|max_length[20]|xss_clean'),
			array('field'=>'po_content', 'label'=>'포인트내용', 'rules'=>'trim|required'),
			array('field'=>'po_point', 'label'=>'포인트', 'rules'=>'trim|required|numeric')
		);

		$this->form_validation->set_rules($config);
		if ($this->form_validation->run() == FALSE) {
			
 			$param =& $this->querystring;
			$page = $this->uri->segment(5, 1);
			$sst  = $param->get('sst', 'po_id');
			$sod  = $param->get('sod', 'desc');
			$sfl  = $param->get('sfl');
			$stx  = $param->get('stx');
			
			$config['suffix'] = $param->output();
			$config['base_url'] = RT_PATH.'/'.ADM_F.'/point/lists/page/';
			$config['per_page'] = 15;

			$offset = ($page - 1) * $config['per_page'];
			$result = $this->Point_model->list_result($sst, $sod, $sfl, $stx, $config['per_page'], $offset);

			$config['total_rows'] = $result['total_cnt'];
			$this->pagination->initialize($config);

			if ($sfl == 'mb_id' && $stx && $result['total_cnt'] > 0) {
				$total_pnt = $stx.' 님 포인트 합계 : ' . number_format($result['total_pnt']) . '점';
				$stx_mb_id = TRUE;
			} else
				$total_pnt = '전체 포인트 합계 : ' . number_format($result['total_pnt']) . '점';

			$list = array();
			foreach ($result['qry'] as $i => $row) {
				$list[$i] = new stdClass();
				
				if ($this->config->item('cf_use_nick'))
					$list[$i]->mb_nick = $row['mb_nick'];

				$link1 = $link2 = '';
				if (!preg_match("/^\@/", $row['po_rel_table']) && $row['po_rel_table'])
					$po_content = "<a href='".RT_PATH."/board/view/tbl/".$row['po_rel_table']."/".$row['po_rel_id']." target=_blank'>".$row['po_content']."</a>";
				else
					$po_content = $row['po_content'];

				$list[$i]->id = $row['po_id'];
				$list[$i]->mb_id = $row['mb_id'];
				$list[$i]->datetime = substr($row['po_datetime'], 2, 8);
				$list[$i]->content = $po_content;
				$list[$i]->point = number_format($row['po_point']);
				$list[$i]->mb_name = get_sideview($row['mb_id'], $row['mb_name']);
				$list[$i]->mb_point = number_format($row['mb_point']);
			}

			$head = array('title' => '포인트관리');
			$data = array(
				'token' => get_token(),

				'list' => $list,
				'use_nick' => $this->config->item('cf_use_nick'),
		
				'sfl' => $sfl,
				'stx' => $stx,
				'stx_mb_id' => (isset($stx_mb_id)) ? $stx : '',

				'total_cnt' => number_format($result['total_cnt']),
				'total_pnt' => $total_pnt,
				'paging' => $this->pagination->create_links(),

				'sort_mb_id' => $param->sort('mb_id'),
				'sort_po_datetime' => $param->sort('po_datetime'),
				'sort_po_content' => $param->sort('po_content'),
				'sort_po_point' => $param->sort('po_point')
			);

			widget::run('head', $head);
			$this->load->view(ADM_F.'/point_list', $data);
			widget::run('tail');
		}
		else {
			check_token();
			$member = unserialize(MEMBER);
			$mb_id = $this->input->post('mb_id');
			$po_point = $this->input->post('po_point');
			$mb = $this->Basic_model->get_member($mb_id, 'mb_id,mb_point');

			if (!isset($mb['mb_id']))
				alert('존재하는 회원아이디가 아닙니다.');

			if (($po_point < 0) && ($po_point * (-1) > $mb['mb_point']))
				alert('포인트를 깎는 경우 현재 포인트보다 작으면 안됩니다.');

			$this->load->model('Point_model');
			$this->Point_model->insert($mb_id, $po_point, $this->input->post('po_content'), '@passive', $mb_id, $member['mb_id'].'-'.uniqid(''));

			goto_url(ADM_F.'/point/lists');
		}
	}
}
?>