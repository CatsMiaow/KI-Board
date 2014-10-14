<?php
class Board extends CI_Controller {
    function __construct() {
        parent::__construct();
        check_token(ADM_F.'/board/lists');
        $this->load->model(ADM_F.'/Board_model');
    }

    function delete() {
        if ($this->input->post('bo_table'))
            $bo_tables = array($this->input->post('bo_table'));
        else if ($this->input->post('chk'))
            $bo_tables = $this->input->post('chk');
        else
            alert('잘못된 접근입니다.');

        // 게시판 폴더 삭제
        $this->load->dbforge();
        $this->load->helper('admin');
        $ca_types = array();
        foreach($bo_tables as $bo_table) {
            $this->dbforge->drop_table('ki_extra_'.$bo_table);
            rm_rf(DATA_PATH.'/file/'.$bo_table);
            $ca_types[] = 'bo_'.$bo_table;
        }

        $this->Board_model->delete($bo_tables, $ca_types);
        
        goto_url(URL);
    }

    function update() {
        if ($this->input->post('chk')) {
            $bo_tables = $this->input->post('chk');
            $bo_subjects = $this->input->post('bo_subject');
            $gr_ids = $this->input->post('gr_id');
            $bo_skins = $this->input->post('bo_skin');
            $bo_use_searchs = $this->input->post('bo_use_search');
            $bo_order_searchs = $this->input->post('bo_order_search');
        }
        else
            alert('잘못된 접근입니다.');

        foreach ($bo_tables as $bo_table) {
            $bo_use_search = (isset($bo_use_searchs[$bo_table])) ? $bo_use_searchs[$bo_table] : '';
            $this->Board_model->list_update($bo_table, $bo_subjects[$bo_table], $gr_ids[$bo_table], $bo_skins[$bo_table], $bo_use_search, $bo_order_searchs[$bo_table]);
        }
        
        goto_url(URL);
    }
}
?>