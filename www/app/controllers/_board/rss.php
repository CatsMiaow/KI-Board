<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class Rss extends Widget {
    function index() {
        $board =& $this->board;
        $bo_table = $board['bo_table'];
        
        if ($board['bo_read_level'] > 1) {
            echo '비회원의 접근이 가능하지 않습니다.';
            exit;
        }

        if (!$board['bo_use_rss']) {
            echo 'RSS를 지원하지 않습니다.';
            exit;
        }
        
        $this->load->helper('xml');
        $base_url = $this->config->item('base_url');
        
        $this->db->select('wr_id, mb_id, ca_code, wr_subject, wr_content, wr_datetime');
        $qry = $this->db->get_where('ki_write', array(
            'bo_table' => $bo_table
        ), 15);
        $result = $qry->result_array();
        
        if ($board['bo_use_category']) {
			$CI =& get_instance();
			$CI->load->model('Category_model');
			$category = $CI->Category_model->get_category('bo_'.$bo_table);
        }
        
        header("Content-type: text/xml; charset=".$this->config->item('charset'));
        header("Cache-Control: no-cache, must-revalidate"); 
        header("Pragma: no-cache");
        
        echo "<?xml version=\"1.0\" encoding=\"".$this->config->item('charset')."\"?>\n";
        echo "<rss version=\"2.0\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:taxo=\"http://purl.org/rss/1.0/modules/taxonomy/\">\n";
        echo "<channel>\n";
        echo "<title><![CDATA[".xml_convert($board['bo_subject'])."]]></title>\n";
        echo "<link>".$base_url.'/board/'.$bo_table."</link>\n";
        echo "<description></description>\n";
        echo "<language>ko</language>\n";
        echo "<generator>Tested.co.kr</generator>\n"; 
        echo "<pubDate>".date('r', time())."</pubDate>\n";

        foreach ($result as $row) {
            echo "<item>\n";
            echo "<author>".$row['mb_id']."</author>\n";
            if ($board['bo_use_category'])
                echo "<category><![CDATA[".xml_convert($category[$row['ca_code']])."]]></category>\n";
            echo "<title><![CDATA[".xml_convert($row['wr_subject'])."]]></title>\n";
            echo "<link>".$base_url.'/board/'.$bo_table.'/view/wr_id/'.$row['wr_id']."</link>\n";
            echo "<description><![CDATA[".xml_convert(cut_str(preg_replace("/\s+&nbsp;+/", '', strip_tags(htmlspecialchars_decode($row['wr_content']))), 300))."]]></description>\n";
            echo "<pubDate>".date('r', strtotime($row['wr_datetime']))."</pubDate>\n";
            echo "</item>\n"; 
        }
        
        echo "</channel>\n";
        echo "</rss>\n";
    }
}