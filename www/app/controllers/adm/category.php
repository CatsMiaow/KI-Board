<?php
class category extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Categoryform_model');
		$this->load->helper('categoryform');
        define('WIDGET_SKIN', 'admin');
		define('CSS_SKIN', 'category');
	}

    function lists($type='', $tid='') {
        switch ($type) {
            case 'board':
                $bo = $this->Basic_model->get_board($tid, 'bo_table,bo_subject');
        		if (!isset($bo['bo_table']))
        			alert('존재하지 않는 게시판 입니다.');
                    
                $name = $bo['bo_subject'];
                $type = 'bo_'.$tid;
            break;
            default: alert('잘못된 접근입니다.'); break;
        }

		$bc = $this->Categoryform_model->list_result($type);

		$code_html = FALSE;
		if ($bc) {
			$t_code = $s_code = array();
			foreach ($bc as $row) {
				$code_exp = explode('-', $row['code']);
				
				if (!isset($code_exp[1]))
					$t_code[$code_exp[0]] = $row['ca_name'];
				else
					$s_code[$code_exp[0]][$code_exp[1]] = $row['ca_name'];
			}

			 $code_html = get_categoryform($t_code, $s_code);
		}

		// echo '<PRE>';
		// print_r($s_code);
		$head = array('title' => $name.' 분류관리');
		$data = array(
            'name' => $name,
			'type' => $type,
            'tid' => $tid,
			'code_html' => $code_html
		);

		widget::run('head', $head);
		$this->load->view(ADM_F.'/category', $data);
		widget::run('tail');
	}
}
?>