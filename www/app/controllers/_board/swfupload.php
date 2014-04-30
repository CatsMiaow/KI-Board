<?php
class Swfupload extends CI_Controller {
	function __construct() {
		parent::__construct();
	}
	
    function index() {
		if ($this->input->post('PHPSESSID'))
			session_id($this->input->post('PHPSESSID'));
		else
			return FALSE;

		if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
		 	$config['allowed_types'] = $this->input->post('upload_ext');
		 	$config['upload_path']   = DATA_PATH.'/temp';
			$config['max_size']		 = $this->input->post('upload_size');
			$config['encrypt_name']  = TRUE;
            
			$this->load->library('upload', $config);
			if ($this->upload->do_upload('Filedata')) {
				$data = $this->upload->data();
                
                $file = '/temp/'.$data['file_name'];
                $filedir = $this->config->item('base_url').DATA_DIR.$file;
                $filepath = DATA_PATH.$file;
                if (strpos('.jpg.gif.png', strtolower($data['file_ext'])) !== FALSE) {
                    $info = array(
                        'imageurl' => $filedir,
                		'filename' => $data['orig_name'],
                		'filesize' => filesize($filepath),
                		'imagealign' => 'L',
                		'thumburl' => $filedir
                    );
                }
                else {
                    $info = array(
                        'attachurl' => $filedir,
                		'filemime' => $data['file_type'],
                		'filename' => $data['orig_name'],
                		'filesize' => filesize($filepath)
                    );
                }
                
                echo json_encode($info);
			}
			else
				echo $this->upload->display_errors('', '');
		}
		else 
			return FALSE;
	}
}
?>