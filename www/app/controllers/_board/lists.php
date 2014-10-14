<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class Lists extends Widget {
    function index($view=FALSE) {
        $board    =& $this->board;
        $member   =& $this->member;
        $wr_field =& $this->wr_field;
        $seg      =& $this->seg;
        $param    =& $this->param;

        $wr_id = $seg->get('wr_id');   // 게시물아이디
        $page  = $seg->get('page', 1); // 페이지
        $qstr  = $seg->replace('wr_id').$param->output();
        $sst   = $param->get('sst');   // 정렬필드
        $sod   = $param->get('sod');   // 정렬순서
        $sfl   = $param->get('sfl');   // 검색필드
        $stx   = $param->get('stx');   // 검색어
        $sca   = $param->get('sca');   // 분류
        $spt   = $param->get('spt');   // 검색 파트
        $js = array('board'); // JavaScript Files
        
        if ($member['mb_level'] < $board['bo_list_level']) {
            if (IS_MEMBER)
                alert('목록을 볼 권한이 없습니다.');
            else
                alert("목록을 볼 권한이 없습니다.\\n\\n회원이라면 로그인 후 이용하세요.", 'member/login/qry/'.url_encode('board/'.BO_TABLE.'/lists'.$qstr));
        }
        
        // 분류 사용 여부
        $sca_str = ($sca) ? '?sca='.$sca : '';
        $category = FALSE;
        if ($board['bo_use_category']) {
            $this->load->helper('category');
            $category = make_category(array(
                'type' => 'bo_'.BO_TABLE,
                'id'   => 'ca_code',
                'code' => $sca,
                'lst'  => TRUE
            ));
        }

        // 검색 파트 row
        $search_part = $this->config->item('cf_search_part');
        $btn_prev_part = $btn_next_part = '';

        // 분류 선택, 검색어, 검색 파트 적용
        if ($sca || ($sfl && $stx) || $board['bo_count_write'] > $search_part) {
            if ($stx)
                $stx = get_text($stx);

            $min_spt = $board['bo_min_wr_num'];
            if (!$spt)
                $spt = $min_spt;

            $total_count = $this->Board_model->list_count(BO_TABLE, $spt, $sca, $sfl, $stx);

            $prev_spt = $spt - $search_part;
            if ($min_spt && $prev_spt >= $min_spt)
                $btn_prev_part = '<li><a href="'.RT_PATH.'/board/'.BO_TABLE.'/lists'.$param->replace('spt', $prev_spt, $qstr).'">이전검색</a></li>';

            $next_spt = $spt + $search_part;
            if ($next_spt < 0) 
                $btn_next_part = '<li><a href="'.RT_PATH.'/board/'.BO_TABLE.'/lists'.$param->replace('spt', $next_spt, $qstr).'">다음검색</a></li>';
        }
        else
            $total_count = $board['bo_count_write'];

        $config['suffix']       = $qstr;
        $config['base_url']    = RT_PATH.'/board/'.BO_TABLE.'/lists/page/';
        $config['per_page']    = $board['bo_page_rows'];
        $config['total_rows']  = $total_count;
        $config['uri_segment'] = $seg->pos('page');

        // 검색 파트 ADD
        $config['full_tag_open']  = '<ul class="pagination">'.$btn_prev_part;
        $config['full_tag_close'] = $btn_next_part.'</ul>';

        $CI =& get_instance();
        $CI->load->library('pagination', $config);

        // 정렬
        if (!$sst) {
            if ($board['bo_sort_field'])
                $sst = $board['bo_sort_field'];
            else {
                $sst = 'wr_num, wr_reply';
                $sod = 'asc';
            }
        }
        else
            $sst = preg_match("/^(wr_datetime|wr_hit)$/i", $sst) ? $sst : FALSE;

        $offset = ($page - 1) * $config['per_page'];
        $result = $this->Board_model->list_result(BO_TABLE, $spt, $sca, $sst, $sod, $sfl, $stx, $config['per_page'], $offset, $wr_field);

        // 사이드 뷰
        if ($board['bo_use_sideview'])
            $this->load->helper('sideview');

        // 일반 리스트
        $list = $wr_ids = array();
        foreach ($result as $i => $row) {
            $row = get_convert($row, $board, $board['bo_subject_len'], $qstr, TRUE);
            
            $list[$i] = new stdClass();
            $list[$i]->num = $total_count - ($page - 1) * $config['per_page'] - $i;
            $list[$i]->href = $row['href'];
            $list[$i]->wr_id = $row['wr_id'];
            $list[$i]->subject = (strpos($sfl, 'subject')) ? search_font($row['subject'], $stx) : $row['subject'];
            $list[$i]->comment_cnt = $row['comment_cnt'];
            $list[$i]->name = $row['name'];
            $list[$i]->datetime2 = $row['datetime2'];
            $list[$i]->wr_hit = $row['wr_hit'];

            $list[$i]->ico_reply  = $row['ico_reply'];
            $list[$i]->ico_new    = $row['ico_new'];
            $list[$i]->ico_hot    = $row['ico_hot'];
            $list[$i]->ico_secret = $row['ico_secret'];
            $list[$i]->ico_file   = $row['ico_file'];
            $list[$i]->ico_image  = $row['ico_image'];
            $list[$i]->ico_movie  = $row['ico_movie'];

            $wr_ids[$row['wr_id']] = $i;
        }

        // Extra
        if ($board['bo_use_extra'] && $wr_ids) {
            $result = $this->Board_model->get_extra(BO_TABLE, array_keys($wr_ids));
            foreach ($result as $row) {
                $i = $wr_ids[$row['wr_id']];
                foreach ($row as $fld => $val) {
                    $list[$i]->$fld = $val;
                }
            }
        }

        // 공지사항 리스트
        if (!$sca && !$stx) {
            $notice = explode(',', trim($board['bo_notice']));
            if ($notice[0]) {
                $result = $this->Board_model->list_notice(BO_TABLE, $notice, $wr_field);

                $list_nt = array();
                foreach ($result as $i => $row) {
                    $row = get_convert($row, $board, $board['bo_subject_len'], $qstr, TRUE);
                    
                    $list_nt[$i] = new stdClass(); 
                    $list_nt[$i]->href = $row['href'];
                    $list_nt[$i]->wr_id = $row['wr_id'];
                    $list_nt[$i]->subject = $row['subject'];
                    $list_nt[$i]->comment_cnt = $row['comment_cnt'];
                    $list_nt[$i]->name = $row['name'];
                    $list_nt[$i]->datetime2 = $row['datetime2'];
                    $list_nt[$i]->wr_hit = $row['wr_hit'];
                }
            }
        }

        // 리스트 버튼
        $btn_list = '';
        if ($sfl && $stx)
            $btn_list = '<a href="'.RT_PATH.'/board/'.BO_TABLE.'/lists'.$sca_str.'" class="btn btn-warning">목록</a>';

        // 글쓰기 버튼
        $btn_write = '';
        if ($board['bo_use_private'] && !IS_ADMIN)
            $btn_write = FALSE;
        elseif ($member['mb_level'] >= $board['bo_write_level'])
            $btn_write = '<a href="'.RT_PATH.'/board/'.BO_TABLE.'/write'.$sca_str.'" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span> 글쓰기</a>';

        // RSS 버튼
        $btn_rss = '';
        if ($board['bo_use_rss'])
            $btn_rss = '<a href="'.RT_PATH.'/board/'.BO_TABLE.'/rss" class="btn btn-xs btn-warning" target="_blank">RSS</a>';

        // 관리자 버튼
        $btn_admin = '';
        if (SU_ADMIN)
            $btn_admin = '<a href="'.RT_PATH.'/'.ADM_F.'/board/form/u/'.BO_TABLE.'" class="btn btn-xs btn-primary" target="_blank">관리자</a>';
        else if (IS_ADMIN)
            $btn_admin = '<button type="button" class="btn btn-xs btn-primary" onclick="board_admin();">관리자</button>';

        // 관리자 체크박스 및 버튼 표시xsxs
        $btn_chkbox = '';
        if (IS_ADMIN) {
            $btn_chkbox = '<button type="button" class="btn btn-danger" onclick="select_delete();">선택삭제</button>';
            if (SU_ADMIN || IS_ADMIN == 'group') {
                $btn_chkbox .= '<button type="button" class="btn btn-info" onclick="select_copy(\'copy\');">선택복사</button>';
                $btn_chkbox .= '<button type="button" class="btn btn-info" onclick="select_copy(\'move\');">선택이동</button>';
            }
        }
        
        // 정렬 링크
        $head = array(
            'title' => $board['gr_subject'].' > '.$board['bo_subject'],
            'sca' => $sca
        );
        $data = array(
            'total_count' => $total_count,
            'category'    => $category,

            'btn_list'   => $btn_list,
            'btn_write'  => $btn_write,
            'btn_rss'    => $btn_rss,
            'btn_admin'  => $btn_admin,
            'btn_chkbox' => $btn_chkbox,
    
            'wr_id' => $wr_id,
            'sca' => $sca,
            'sfl' => $sfl,
            'stx' => $stx,
            'list' => $list,
            'list_nt' => isset($list_nt) ? $list_nt : array(),
            'paging' => $CI->pagination->create_links(),

            'sort_datetime' => $param->sort('wr_datetime', 'desc'),
            'sort_hit' => $param->sort('wr_hit', 'desc')
        );

        if ($view)
            $this->load->view('board/'.$board['bo_skin'].'/list', $data);
        else {
            // JavaScript Load
            if (IS_ADMIN) $js[] = 'board_check';
            if ($board['bo_use_sideview']) $js[] = 'sideview';
            if ($board['bo_use_category']) $js[] = 'category';

            widget::run('head', $head);
            $this->load->view('board/'.$board['bo_skin'].'/list', $data);
            widget::run('tail', array('js' => $js));
        }
    }
}