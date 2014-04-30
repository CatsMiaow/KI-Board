<?php
class Popup extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model(ADM_F.'/Popup_model');
		$this->load->helper('admin');
		define('WIDGET_SKIN', 'admin');
		define('CSS_SKIN', 'jquery');

		if (!$this->config->item('cf_use_popup'))
			goto_url(ADM_F);
	}
	
	function lists() {
		$this->load->library(array('pagination','querystring'));
		
 		$param =& $this->querystring;
		$page = $this->uri->segment(5, 1);
		$sst  = $param->get('sst', 'pu_id');
		$sod  = $param->get('sod', 'asc');
		$sfl  = $param->get('sfl');
		$stx  = $param->get('stx');
		
		$config['suffix'] = $param->output();
		$config['base_url'] = RT_PATH.'/'.ADM_F.'/popup/lists/page/';
		$config['per_page'] = 15;

		$offset = ($page - 1) * $config['per_page'];			
		$result = $this->Popup_model->list_result($sst, $sod, $sfl, $stx, $config['per_page'], $offset);

		$config['total_rows'] = $result['total_cnt'];
		$this->pagination->initialize($config);

		$list = array();
		$type = array('팝업', '고정');

		$token = get_token();
		foreach ($result['qry'] as $i => $row) {
			$list[$i] = new stdClass();
			$list[$i]->id = $row['pu_id'];
			$list[$i]->name = $row['pu_name'];
			$list[$i]->type = $type[$row['pu_type']];
			$list[$i]->sdate = $row['pu_sdate'];
			$list[$i]->edate = $row['pu_edate'];
			// $list[$i]->date = date('Y-m-d', strtotime($row['pu_datetime']));

			$list[$i]->use_chk = ($row['pu_use']) ? "checked='checked'" : '';

			$list[$i]->s_pre = icon('보기', "javascript:win_open('popup/".$row['pu_id']."', 'popup', 'left=".$row['pu_x']."px,top=".$row['pu_y']."px,width=".$row['pu_width']."px,height=".$row['pu_height']."px,scrollbars=0');");
			$list[$i]->s_mod = icon('수정', 'popup/form/u/'.$row['pu_id']);
			$list[$i]->s_del = icon('삭제', "javascript:post_send('".ADM_F."/_trans/popup/delete', {pu_id:'".$row['pu_id']."', token:'".$token."'}, true);");
		}

		$head = array('title' => '팝업관리');
		$data = array(
			'token' => $token,

			'list' => $list,
			's_add' => icon('작성', 'popup/form'),

			'sfl' => $sfl,
			'stx' => $stx,		

			'total_cnt' => number_format($result['total_cnt']),
			'paging' => $this->pagination->create_links(),

			'sort_pu_name' => $param->sort('pu_name'),
			'sort_pu_type' => $param->sort('pu_type'),
			'sort_pu_sdate' => $param->sort('pu_sdate'),
			'sort_pu_edate' => $param->sort('pu_edate'),
			'sort_pu_use' => $param->sort('pu_use')
		);

		widget::run('head', $head);
		$this->load->view(ADM_F.'/popup_list', $data);
		widget::run('tail');
	}
	
	function form($w='', $pu_id='') {
		$this->load->library('form_validation');

		$config = array(
			array('field'=>'pu_name', 'label'=>'팝업 이름', 'rules'=>'trim|required|max_length[20]|xss_clean'),
			array('field'=>'pu_file', 'label'=>'팝업 파일', 'rules'=>'trim|required|max_length[20]|alpha_dash')
		);

		$this->form_validation->set_rules($config);
		if ($this->form_validation->run() == FALSE) {
			if ($w == '' || $w != 'u') {
				$title = '등록';
				$pu = array_false(array('pu_id','pu_name','pu_file'));
				$s = array_false(array('date','h','i','s'));
				$e = array_false(array('date','h','i','s'));

				$pu['pu_use'] = $pu['pu_type'] = 0;
				$pu['pu_width'] = $pu['pu_height'] = 100;
				$pu['pu_x'] = $pu['pu_y'] = 0;
			}
			else if ($w == 'u') {
				$title = '수정';
				$pu = $this->Popup_model->get_popup($pu_id);
				if (!isset($pu['pu_id']))
					alert('등록된 자료가 없습니다.');

				// 시작일
				list($s['date'], $time) = explode(' ', $pu['pu_sdate']);
				list($s['h'], $s['i'], $s['s']) = explode(':', $time);
				
				// 종료일
				list($e['date'], $time) = explode(' ', $pu['pu_edate']);
				list($e['h'], $e['i'], $e['s']) = explode(':', $time);
			}

			$head = array('title' => '팝업 '.$title);
			$data = array(
				'w' => $w,
				'token' => get_token(),
				
				'id' 	=> $pu['pu_id'],
				'name' 	=> $pu['pu_name'],
				'file' 	=> $pu['pu_file'],
                'use_chk' => ($pu['pu_use']) ? "checked='checked'" : '',
				'type' 	=> $pu['pu_type'],

				'sdate'   => $s['date'],
				'stime_h' => $s['h'],
				'stime_i' => $s['i'],
				'stime_s' => $s['s'],

				'edate'   => $e['date'],
				'etime_h' => $e['h'],
				'etime_i' => $e['i'],
				'etime_s' => $e['s'],

				'width'  => $pu['pu_width'],
				'height' => $pu['pu_height'],
				'x' 	 => $pu['pu_x'],
				'y' 	 => $pu['pu_y']
			);

			widget::run('head', $head);
			$this->load->view(ADM_F.'/popup_form', $data);
			widget::run('tail');
		}
		else {
			check_token();
			
			$w = $this->input->post('w');
			if (!$w) {
				$pu = $this->Popup_model->get_popup($pu_id, 'pu_id');
				if (isset($pu['pu_id']))
					alert('이미 존재하는 팝업 ID 입니다.');
			}
			else if ($w == 'u') {
				// what!?
			}
			else
				alert('잘못된 접근입니다.');

			$pu_id = $this->Popup_model->record($w);

			// goto_url(ADM_F.'/popup/form/u/'.$pu_id);
			goto_url(ADM_F.'/popup/lists');
		}
	}
}
?>