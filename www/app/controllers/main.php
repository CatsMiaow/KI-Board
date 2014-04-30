<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {
	function __construct() {
		parent::__construct();
		define('WIDGET_SKIN', 'main');
		$this->load->model(array('Latest_model', 'Popup_model'));
		// $this->output->enable_profiler(TRUE);
	}

	function index() {
		$popup = $this->Popup_model->output();

		$pubasic = $pulayer = array();
		foreach ($popup as $i => $row) {
			$id = $row['pu_id'];
			$skin = 'popup/'.$row['pu_file'];

			if (!$this->input->cookie('popup'.$id) && file_exists(SKIN_PATH.$skin.'.html')) {
				if ($row['pu_type'] == 1) {
					$pubasic[] = "<div id='popup".$id."' style='position:absolute; width:".$row['pu_width']."px; height:".$row['pu_height']."px; top:".$row['pu_y']."px; left:".$row['pu_x']."px; z-index:100; overflow:hidden;'>".$this->load->view($skin, array('id'=>'popup'.$id), TRUE)."</div>";
				}
				else {
					$pulayer[$i]->id = $id;
					$pulayer[$i]->html = "win_open('popup/".$id."', 'popup".$id."', 'left=".$row['pu_x']."px,top=".$row['pu_y']."px,width=".$row['pu_width']."px,height=".$row['pu_height']."px,scrollbars=0');";
				}
			}
		}

		$data = array(
			'pubasic' => $pubasic,
			'pulayer' => $pulayer,
			'write'   => $this->Latest_model->write('test', 10, 50),
			'comment' => $this->Latest_model->comment(10, 50)
		);

		widget::run('head');
		$this->load->view('main/main', $data);
		widget::run('tail');
	}
}