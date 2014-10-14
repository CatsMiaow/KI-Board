<?php
class Repair extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model(ADM_F.'/Repair_model');
        $this->load->dbutil();
        define('WIDGET_SKIN', 'admin');
    }

    function index() {
        $this->Repair_model->delete_popular();
        $this->Repair_model->delete_memo();

        $rep_result = $opt_result = FALSE;
        $tables = $this->db->list_tables();
        foreach ($tables as $table) {
            // 테이블 수리    
            if (!$this->dbutil->repair_table($table))
                $rep_result .= $table.' 실패 <br/>';

            // 테이블 최적화
            if (!$this->dbutil->optimize_table($table))
                $opt_result .= $table.' 실패 <br/>';
        }
        
        $head = array('title' => '테이블 복구 및 최적화');
        $data = array(
            'rep_result' => ($rep_result) ? $rep_result : '테이블 수리 완료',
            'opt_result' => ($opt_result) ? $opt_result : '테이블 최적화 완료'
        );

        widget::run('head', $head);
        $this->load->view(ADM_F.'/repair', $data);
        widget::run('tail');
    }
}
?>