<?php
class Point extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->library('pagination');
        $this->load->model('Member_point_model');
        define('WIDGET_SKIN', 'main');
    }

    function index() {
        $this->page();
    }

    function page($page=1) {
        if (!IS_MEMBER)
            alert('회원만 조회하실 수 있습니다.');

        $member = unserialize(MEMBER);

        $config['base_url'] = RT_PATH.'/member/point/page/';
        $config['total_rows'] = $this->Member_point_model->total_cnt($member['mb_id']);
        $config['per_page'] = 15;
        $config['uri_segment'] = 4;
        $this->pagination->initialize($config);

        $offset = ($page - 1) * $config['per_page'];
        $result = $this->Member_point_model->list_result($member['mb_id'], $config['per_page'], $offset);

        $list = array();
        $sum_point1 = $sum_point2 = FALSE;
        foreach ($result as $i => $row) {
            $point1 = $point2 = 0;
            if ($row['po_point'] > 0) {
                $point1 = "+" . number_format($row['po_point']);
                $sum_point1 += $row['po_point'];
            } else {
                $point2 = number_format($row['po_point']);
                $sum_point2 += $row['po_point'];
            }

            $list[$i] = new stdClass();
            $list[$i]->po_content = $row['po_content'];
            $list[$i]->po_datetime = substr($row['po_datetime'], 2, 8);
            $list[$i]->point1 = $point1;
            $list[$i]->point2 = $point2;
        }

        if ($config['total_rows']) {
            if ($sum_point1 > 0)
                $sum_point1 = '+' . number_format($sum_point1);
            $sum_point2 = number_format($sum_point2);
        }

        $head = array('title' => $member['mb_nick'].' 님의 포인트 내역');
        $data = array(
            'paging' => $this->pagination->create_links(),
            'mb_point' => number_format($member['mb_point']),
            'list' => $list,
            'sum_point1' => $sum_point1,
            'sum_point2' => $sum_point2
        );

        widget::run('head', $head);
        $this->load->view('member/point', $data);
        widget::run('tail');
    }
}
?>