<?php
class Visit extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library(array('pagination', 'querystring'));
		$this->load->model(ADM_F.'/Visit_model');
		define('WIDGET_SKIN', 'admin');
		define('CSS_SKIN', 'jquery');
		// $this->output->enable_profiler(TRUE);
	}
	
	function lists() {
 		$param =& $this->querystring;
		$page = $this->uri->segment(5, 1);
		$sfl  = $param->get('sfl');
		$stx  = $param->get('stx');
		$fr_date = $param->get('from', TIME_YMD);
		$to_date = $param->get('to', TIME_YMD);
		
		$config['suffix'] = $param->output();
		$config['base_url'] = RT_PATH.'/'.ADM_F.'/visit/lists/page/';
		$config['per_page'] = 15;

		$offset = ($page - 1) * $config['per_page'];
		$result = $this->Visit_model->list_result($fr_date, $to_date, $config['per_page'], $offset);

		$config['total_rows'] = $result['total_cnt'];
		$this->pagination->initialize($config);
		
		$list = array();
		foreach ($result['qry'] as $i => $row) {
			$parse = parse_url($row['vi_referer']);
			$host = isset($parse['host']) ? $parse['host'] : '';

			$list[$i] = new stdClass();
			$list[$i]->vi_ip 	  = $row['vi_ip'];
			$list[$i]->vi_date 	  = $row['vi_date'];
			$list[$i]->vi_time 	  = $row['vi_time'];
			$list[$i]->vi_referer = $row['vi_referer'];
			$list[$i]->vi_agent   = $row['vi_agent'];
			$list[$i]->path 	  = $host.$parse['path'];
		}
		
		$head = array('title' => '방문자분석');
		$data = array(
			'list' 	  => $list,
			'fr_date' => $fr_date,
			'to_date' => $to_date,
			'paging'  => $this->pagination->create_links()
		);
		
		widget::run('head', $head);
		$this->load->view(ADM_F.'/visit', $data);
		widget::run('tail');
	}
}
?>