<?php
class Boardextra extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->library('form_validation');
		define('WIDGET_SKIN', 'admin');
	}

	function ex($bo_table='') {
		$bo = $this->Basic_model->get_board($bo_table, 'bo_table,bo_subject');
		if (!isset($bo['bo_table']))
			alert('존재하지 않는 게시판 입니다.');

		$table_name = 'ki_extra_'.$bo_table;

		$config = array(
			array('field'=>'bo_table', 'label'=>'아이디', 'rules'=>'trim|required|max_length[20]|alpha_dash'),
			array('field'=>'type', 'label'=>'타입', 'rules'=>'trim|required')
		);
		if ($this->input->post('type') == 'field') {
			$config[] = array('field'=>'name', 'label'=>'이름', 'rules'=>'trim|required|max_length[20]|alpha_dash');
			$config[] = array('field'=>'attr', 'label'=>'속성', 'rules'=>'trim|required|alpha');
			$config[] = array('field'=>'size', 'label'=>'크기', 'rules'=>'trim|max_length[3]|is_natural');
		}

		$this->form_validation->set_rules($config);
		if ($this->form_validation->run() == FALSE) {
			$w = '';
			$type = 'field';
			$is_table = TRUE;
			$list = array();

			if (!$this->db->table_exists($table_name)) {
				$is_table = FALSE;
				$type = 'table';
			}
			else {
				$qry = $this->db->query('desc '.$table_name);
				$result = $qry->result_array();
				foreach ($result as $i => $row) {
					if ($row['Field'] == 'wr_id')
						continue;

					$list[$i] = new stdClass();
					$list[$i]->name = $row['Field'];
					$list[$i]->attr = $row['Type'];
					$list[$i]->unsg = FALSE;
					$list[$i]->size = '';

					preg_match('/\(([0-9]+)\)/', $row['Type'], $size);
					if (isset($size[1])) {
						$attr = str_replace($size[0], '', $row['Type']);
						if (strpos($attr, 'unsigned') !== FALSE) {
							$list[$i]->unsg = " selected='selected'";
							$attr = str_replace('unsigned', '', $attr);
						}

						$list[$i]->size = $size[1];
						$list[$i]->attr = trim($attr);
					}
				}
			}
			
			$head = array('title' => $bo['bo_subject'].' 여분필드 관리');
			$data = array(
				'is_table' => $is_table,
				'type' => $type,
				'w' => $w,
				'list' => $list,

				'bo_table' => $bo_table,
				'bo_subject' => $bo['bo_subject']
			);

			widget::run('head', $head);
			$this->load->view(ADM_F.'/boardextra', $data);
			widget::run('tail');
		}
		else {
			$w = $this->input->post('w');
			$type = $this->input->post('type');

			$this->load->dbforge();
			switch ($type) {
				case 'table':
					if ($w == '') {
						$this->dbforge->add_field(array(
							'wr_id' => array(
								'type' => 'int',
								'constraint' => 10,
								'unsigned' => TRUE
							)
						));
						$this->dbforge->add_key('wr_id', TRUE);
						$this->dbforge->create_table($table_name, TRUE);
					}
					else if ($w == 'd')
						$this->dbforge->drop_table($table_name);
				break;
				case 'field':
					$name = $this->input->post('name');
					$attr = $this->input->post('attr');
					$size = $this->input->post('size');
					$unsg = $this->input->post('unsg');
					
					if ($w == '') {
						$field = array('ex_'.$name => array('type' => $attr, 'null' => FALSE));
						if ($size) $field['ex_'.$name]['constraint'] = $size;
						if ($unsg) $field['ex_'.$name]['unsigned'] = TRUE;

						$this->dbforge->add_column($table_name, $field);
					}
					else if ($w == 'u') {
						$field = array($name => array('name' => $name, 'type' => $attr, 'null' => FALSE));
						if ($size) $field[$name]['constraint'] = $size;
						if ($unsg) $field[$name]['unsigned'] = TRUE;

						$this->dbforge->modify_column($table_name, $field);
					}
					else if ($w == 'd')
						$this->dbforge->drop_column($table_name, $name);
				break;
			}

			goto_url(ADM_F.'/boardextra/ex/'.$bo_table);
		}
	}
}
?>