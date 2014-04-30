<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class View extends Widget {
	function index() {
		$board  =& $this->board;
		$member =& $this->member;
		$seg 	=& $this->seg;
		$param 	=& $this->param;
		
		$wr_id = $seg->get('wr_id'); // 게시물아이디
		$qstr  = $seg->output();
		$dqstr = $seg->replace('wr_id').$param->output();
		$stx   = $param->get('stx'); // 검색어
		$sfl   = $param->get('sfl'); // 검색필드
		$sca   = $param->get('sca'); // 분류
		$js = array('board'); // JavaScript Files

		if ($wr_id) {
			$write =& $this->write;
			
			if (!isset($write['wr_id']))
				alert('글이 존재하지 않습니다.\\n\\n글이 삭제되었거나 이동된 경우입니다.', 'board/'.BO_TABLE);

			// 로그인된 회원의 권한이 설정된 읽기 권한보다 작다면
			if ($member['mb_level'] < $board['bo_read_level']) {
				if (IS_MEMBER)
					alert('글을 읽을 권한이 없습니다.');
				else 
					alert('글을 읽을 권한이 없습니다.\\n\\n회원이라면 로그인 후 이용하세요.', 'member/login/qry/'.url_encode('board/'.BO_TABLE.'/view'.$qstr));
			}

			// 자신의 글 and 관리자가 아니라면 비밀글 체크
			if (!(IS_MEMBER && $write['mb_id'] && $write['mb_id'] == $member['mb_id']) && !IS_ADMIN) {
				if (strpos($write['wr_option'], 'secret') !== FALSE) {

					$is_owner = FALSE;
					if ($write['wr_reply'] && IS_MEMBER) {
						// 자신의 비밀글의 답변이라면 통과
						$row = $this->Board_model->is_owner(BO_TABLE, $write['wr_num']);
						if ($row['mb_id'] == $member['mb_id'])
							$is_owner = TRUE;
					}

					$ss_name = 'ss_secret_'.BO_TABLE.'_'.$write['wr_num'];

					if (!$is_owner) {
						// 한번 읽은 게시물의 번호는 세션에 저장되어 있고 같은 게시물을 읽을 경우는 다시 비밀번호를 묻지 않습니다.
						// 이 게시물이 저장된 게시물이 아니면서 관리자가 아니라면
						if (!$this->session->userdata($ss_name))
							goto_url('board/'.BO_TABLE.'/password/w/s/wr_id/'.$wr_id.$dqstr);
					}

					$this->session->set_userdata($ss_name, TRUE);
				}
			}

			// 한번 읽은글은 브라우저를 닫기전까지는 카운트를 증가시키지 않음
			$ss_name = 'ss_view_'.BO_TABLE.'_'.$wr_id;
			if (!$this->session->userdata($ss_name)) {
				$this->Board_model->hit_update(BO_TABLE, $wr_id);
				$this->session->set_userdata($ss_name, TRUE);
			}
		}
		else
			goto_url('board/'.BO_TABLE);

		// IP 표시
		$is_ip_view = $board['bo_use_ip_view'];
		if (IS_ADMIN) {
			$is_ip_view = TRUE;
			$ip = $write['wr_ip'];
		}
		else // 관리자가 아니라면 IP 주소를 감춘후 보여줍니다.
			$ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", "\\1.♡.\\3.\\4", $write['wr_ip']);

		if ($stx)
			$stx = get_text($stx);
		
		// 최고, 그룹관리자라면 글 복사, 이동 버튼
		$btn_admin = '';
		if ($write['wr_reply'] == '' && (IS_ADMIN == 'super' || IS_ADMIN == 'group')) {
            $start = "post_win('mvcp', '_board/movecopy', {'is_admin':'".IS_ADMIN."','bo_table':'".BO_TABLE."','wr_id':'".$wr_id."','sw':'";
            $end = "'}, 'left=50, top=50, width=500, height=550, scrollbars=1');";

			$btn_admin  = '<span class="btn-group">';
			$btn_admin .= "<button type='button' class='btn btn-default' onclick=\"".$start."copy".$end."\">복사</button>";
            $btn_admin .= "<button type='button' class='btn btn-default' onclick=\"".$start."move".$end."\">이동</button>";
            $btn_admin .= '</span>　';
		}

		// 목록 버튼
		$btn_list = "<a href='".RT_PATH."/board/".BO_TABLE."/lists".$dqstr."' class='btn btn-warning'>목록</a>";
		
		// 글쓰기 & 답변 버튼
        $btn_write = $btn_reply = '';
        if ($board['bo_use_private'] && !IS_ADMIN)
			 $btn_write = $btn_reply = FALSE;
		else {
			if ($member['mb_level'] >= $board['bo_write_level'])
				$btn_write = "<a href='".RT_PATH."/board/".BO_TABLE."/write".($sca ? '?sca='.$sca : '')."' class='btn btn-primary'><span class='glyphicon glyphicon-pencil'></span> 글쓰기</a>";
				
			if ($member['mb_level'] >= $board['bo_reply_level'])
				$btn_reply = "<a href='".RT_PATH."/board/".BO_TABLE."/write/w/r".$qstr."' class='btn btn-info'>답변</a>";
		}

		// 수정 & 삭제 버튼
		$btn_update = $btn_delete = '';
		// 로그인중이고 자신의 글이라면 또는 관리자라면 비밀번호를 묻지 않고 바로 수정, 삭제 가능
		if ((IS_MEMBER && $member['mb_id'] == $write['mb_id']) || IS_ADMIN) {
			$btn_update = "<a href='".RT_PATH."/board/".BO_TABLE."/write/w/u".$qstr."' class='btn btn-info'>수정</a>";
			$btn_delete = "<button type='button' class='btn btn-danger' onclick=\"javascript:post_send('_trans/board_write/delete', {bo_table:'".BO_TABLE."', wr_id:'".$wr_id."', is_admin:'".IS_ADMIN."', qstr:'".$dqstr."'}, true);\">삭제</button>";
		}
		else if (!$write['mb_id']) { // 회원이 쓴 글이 아니라면
			$btn_update = "<a href='".RT_PATH."/board/".BO_TABLE."/password/w/u".$qstr."' class='btn btn-info'>수정</a>";
			$btn_delete = "<a href='".RT_PATH."/board/".BO_TABLE."/password/w/d".$qstr."' class='btn btn-danger'>삭제</a>";
		}

		$btn_prev = $btn_next = '';
		if (!$board['bo_use_list_view']) {
			$pn = $this->Board_model->prev_next_link(BO_TABLE, $write['wr_num'], $write['wr_reply'], $sca, $sfl, $stx);
			
			// 이전글 링크
			$prev = $pn['prev'];
			if ($prev['wr_id']) {
				$prev_wr_subject = cut_str(get_text($prev['wr_subject']), 255);
				$btn_prev = "<a href='".RT_PATH."/board/".BO_TABLE."/view".$seg->replace('wr_id', $prev['wr_id'])."' title='".$prev_wr_subject."'>&larr; 이전글</a>";
			}
		
			// 다음글 링크
			$next = $pn['next'];
			if ($next['wr_id']) {
				$next_wr_subject = cut_str(get_text($next['wr_subject']), 255);
				$btn_next = "<a href='".RT_PATH."/board/".BO_TABLE."/view".$seg->replace('wr_id', $next['wr_id'])."' title='".$next_wr_subject."'>다음글 &rarr;</a>";
			}
		}

		// 버튼s
		$link_btns = $btn_admin.'<span class="btn-group">'.$btn_list.$btn_update.$btn_delete.$btn_reply.$btn_write.'</span>';

		// 전체목록보이기
		$list_view = FALSE;
		if ($member['mb_level'] >= $board['bo_list_level'] && $board['bo_use_list_view'])
			$list_view = TRUE;

		// 사이드 뷰
		if ($board['bo_use_sideview'])
			$this->load->helper('sideview');

		// 가공
		$view = get_convert($write, $board, 255, $qstr);

		if (strpos($sfl, 'subject'))
			$view['subject'] = search_font($view['subject'], $stx);

		// 이미지 리사이즈
		if ($write['wr_count_image'] > 0) {
			define('RESIZE_WIDTH', $board['bo_image_width']);
            $this->load->helper('resize');
			$view['wr_content'] = resize_content($view['wr_content']);
        }

		$is_editor = (strpos($view['wr_option'], 'editor') !== FALSE) ? TRUE : FALSE;
		
		$view['content'] = conv_content($view['wr_content'], $is_editor);
		if (strpos($sfl, 'content'))
			$view['content'] = search_font($view['content'], $stx);


		// SyntaxHighlighter
		$is_syntax = FALSE;
		if ($board['bo_use_syntax'] && $is_editor) {
			$this->load->config('cf_syntax');
			$brush_js = $this->config->item('brush_js');

			preg_match_all("/brush: (".implode('|', array_keys($brush_js)).")/i", $view['content'], $match);
			$match = array_unique($match[1]);

			if ($match) {
				$is_syntax = TRUE; // 있을 때

				$view['content'] = preg_replace_callback('/(<pre class="brush:[^>]+>)([\s\S]+?)(<\/pre>)/i',
					create_function('$content',
						'return $content[1]
							.str_ireplace("<br>", "\n",strip_tags(str_ireplace("</p>", "<br>", $content[2]), "<br>"))
							.$content[3];'
				), $view['content']);

				$js[] = 'syntax/shCore';
				foreach($match as $brush) {
					$js[] = 'syntax/'.$brush_js[$brush];
				}
			}
		}

		// 댓글 출력 여부
		$is_comment = FALSE;
		if ($board['bo_use_comment'] && strpos($write['wr_option'], 'nocomt') === FALSE)
			$is_comment = TRUE;

		$head = array(
			'title' => $board['gr_subject'].' > '.$board['bo_subject'].' > '.strip_tags($view['subject']),
			'sca' => $sca
		);
		$data = array(
			// 한글 깨짐시 cut_hangul_last 채용
			'subject' => $view['subject'],
			'content' => $view['content'],

			'name' => $view['name'],
			'ip' => ($is_ip_view) ? '('.$ip.')' : '',
			'datetime' => date('y-m-d H:i', strtotime($view['wr_datetime'])),
			'hit' => number_format($view['wr_hit']),
			
			'btn_prev' => $btn_prev,
			'btn_next' => $btn_next,
			'link_btns' => $link_btns,
			'btn_sns' => ($board['bo_use_sns']) ? sns_post(BO_TABLE, $wr_id, $view['subject'], $view['content']) : '',
			'is_comment' => $is_comment,
			'is_syntax'  => $is_syntax,
			'wr_id' => $wr_id,
			'qstr' => $qstr
		);

		// JavaScript Load
		if ($board['bo_use_sideview']) $js[] = 'sideview';
		if (!IS_MEMBER && $is_comment) { $js[] = 'md5'; $js[] = 'kcaptcha'; }

		// Extra
		if ($board['bo_use_extra'])
			$data = array_merge($data, $this->Board_model->get_extra(BO_TABLE, $wr_id));

		widget::run('head', $head);
		$this->load->view('board/'.$board['bo_skin'].'/view', $data);

		if ($list_view) {
			if (IS_ADMIN) $js[] = 'board_check';
			if ($board['bo_use_category']) $js[] = 'category';
			widget::run('_board/lists', TRUE);
		}

		widget::run('tail', array('js' => $js));
	}
}