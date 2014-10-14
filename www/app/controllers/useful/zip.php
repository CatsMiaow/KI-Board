<?php
class Zip extends CI_Controller {
    function __construct() {
        parent::__construct();
    }

    function qry($form, $fzip1, $fzip2, $faddr1, $faddr2) {
        // 메모리를 많이 잡아먹어서 아래의 코드로 대체
        // ini_set('memory_limit', '20M');
        // $zipfile = file("./zip.db");

        $zipfile = array();
        $fp = fopen(SKIN_PATH."useful/zip.db", "r");
        while(!feof($fp)) {
            $zipfile[] = fgets($fp, 4096);
        }
        fclose($fp);

        $count = 0;
        $list = array();
        $addr1 = FALSE;

        if ($this->input->post('addr1')) {
            $addr1 = $this->input->post('addr1');

            foreach($zipfile as $i => $row) {
                if (strstr(substr($row,9,512), $addr1)) {
                    $list[$i] = new stdClass();
                    $list[$i]->zip1 = substr($row,0,3);
                    $list[$i]->zip2 = substr($row,4,3);
                    $addr = explode(" ", substr($row,8));

                    if ($addr[sizeof($addr)-1]) {
                        $list[$i]->addr = str_replace($addr[sizeof($addr)-1], "", substr($row,8));
                        $list[$i]->bunji = trim($addr[sizeof($addr)-1]);
                    }
                    else
                        $list[$i]->addr = substr($row,8);

                    $count++;
                }
            }
            if (!$list)
                alert('찾으시는 주소가 없습니다.');
        }

        $head = array('title' => '우편번호 검색');
        $data = array(
            'form' => $form,
            'fzip1' => $fzip1,
            'fzip2' => $fzip2,
            'faddr1' => $faddr1,
            'faddr2' => $faddr2,
            'search_count' => $count,
            'list' => $list,
            'addr1' => $addr1
        );

        widget::run('head', $head);
        $this->load->view('useful/zip', $data);
        widget::run('tail');
    }
}
?>