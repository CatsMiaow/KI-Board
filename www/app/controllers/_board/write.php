<?php if ( ! defined('WIDGET')) exit('No direct script access allowed');

class Write extends Widget {
	function index() {
		$board  =& $this->board;
		$member =& $this->member;
		$write  =& $this->write;
		$seg    =& $this->seg;
		$param  =& $this->param;
		
		$w     = $seg->get('w');	 // 모드
		$wr_id = $seg->get('wr_id'); // 게시물아이디
		$qstr  = $seg->output().$param->output();
		$sca   = $param->get('sca'); // 분류
		$js = array('jquery/validate'); // JavaScript Files

		// 개인게시판 권한
        if ($board['bo_use_private'] && !IS_ADMIN)
            alert('작성 권한이 없습니다.');
		
		// I will be back.
		$return_url = url_encode('board/'.BO_TABLE.'/write'.$qstr);
		// 공지사항
		$notice_array = explode(',', trim($board['bo_notice']));
            
        if ($w == 'u' || $w == 'r') {
	 		if (!isset($write['wr_id']))
	 			alert("글이 존재하지 않습니다.\\n\\n삭제되었거나 이동된 경우입니다.", 'board/'.BO_TABLE.'/lists');

			$sca = $write['ca_code'];
	 	}
		
		if ($w == '') {
		    if ($wr_id)
		        alert('글쓰기에는 wr_id 값을 사용하지 않습니다.', 'board/'.BO_TABLE);
		
		    if ($member['mb_level'] < $board['bo_write_level']) {
		        if (IS_MEMBER)
		            alert('글을 쓸 권한이 없습니다.');
		        else
		            alert("글을 쓸 권한이 없습니다.\\n\\n회원이라면 로그인 후 이용하세요.", "member/login/qry/".$return_url);
		    }
		
		    $title_msg = '글쓰기';
		}
		else if ($w == 'u') {
			if (IS_MEMBER && $write['mb_id'] == $member['mb_id']) {
				// 자신의 글이면 통과
		    }
			else if ($member['mb_level'] < $board['bo_write_level']) {
		        if (IS_MEMBER)
		            alert('글을 수정할 권한이 없습니다.');
		        else
		            alert("글을 수정할 권한이 없습니다.\\n\\n회원이라면 로그인 후 이용하세요.", "member/login/qry/".$return_url);
		    }

			// 수정 권한 IF
			if (IS_ADMIN == 'group' || IS_ADMIN == 'board') {
				$mb = $this->Basic_model->get_member($write['mb_id'], 'mb_level');
				$mb_level = (isset($mb['mb_level'])) ? $mb['mb_level'] : 1;
			}
			
			if (IS_ADMIN == 'super') {
				// 통과
			}
			else if (IS_ADMIN == 'group') { // 그룹관리자
				if ($member['mb_id'] == $board['gr_admin']) { // 자신이 관리하는 그룹인가
					if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
						alert('그룹관리자의 권한보다 높은 회원의 글이므로 수정할 수 없습니다.');
				}
				else
					alert('자신이 관리하는 그룹의 게시판이 아니므로 글을 수정할 수 없습니다.');
			}
			else if (IS_ADMIN == 'board') { // 게시판관리자
				if ($member['mb_id'] == $board['bo_admin']) { // 자신이 관리하는 게시판인가
					if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
						alert('게시판관리자의 권한보다 높은 회원의 글이므로 수정할 수 없습니다.');
				}
				else
					alert('자신이 관리하는 게시판이 아니므로 글을 수정할 수 없습니다.');
			}
			else {
				if ($write['mb_id']) { 
					if (!IS_MEMBER || $member['mb_id'] != $write['mb_id'])
						alert('자신의 글이 아니므로 수정할 수 없습니다.');
				}
				else {
					$CI =& get_instance();
					$CI->load->library('encrypt');
		        	if (md5($this->input->post('password')) !== $CI->encrypt->decode($write['wr_password']))
						alert('비밀번호가 맞지 않습니다.');	
				}
			}
		
		    // 원글만 구한다.
		    $cnt = $this->Board_model->is_reply(BO_TABLE, $wr_id, $write['wr_num'], $write['wr_reply']);
		    if ($cnt && !IS_ADMIN)
		        alert("이 글과 관련된 답변글이 존재하므로 수정할 수 없습니다.\\n\\n답변글이 있는 원글은 수정할 수 없습니다.");
		
		    // 댓글 달린 원글의 수정 여부
		    if ($board['bo_count_modify'] > 0) {
			    $cnt = $this->Board_model->is_comment(BO_TABLE, $wr_id, (IS_MEMBER) ? $member['mb_id'] : '');
			    if ($cnt >= $board['bo_count_modify'] && !IS_ADMIN)
			        alert("이 글과 관련된 댓글가 존재하므로 수정할 수 없습니다.\\n\\n댓글가 ".$board['bo_count_modify']."건 이상 달린 원글은 수정할 수 없습니다.");
      		}
      		
			$title_msg = '글수정';
		}
		else if ($w == 'r') {
		    if ($member['mb_level'] < $board['bo_reply_level']) {
		        if (IS_MEMBER)
		            alert('글을 답변할 권한이 없습니다.');
		        else
		            alert("글을 답변할 권한이 없습니다.\\n\\n회원이라면 로그인 후 이용하세요.", "member/login/qry/".$return_url);
		    }
		
		    if (in_array((int)$wr_id, $notice_array))
		        alert('공지에는 답변 할 수 없습니다.');
			
		    // 비밀글인지를 검사
		    if (strpos($write['wr_option'], 'secret') !== FALSE) {
		        if ($write['mb_id']) {
		            // 회원의 경우는 해당 글쓴 회원 및 관리자
		            if (!($write['mb_id'] == $member['mb_id'] || IS_ADMIN))
		                alert('비밀글에는 자신 또는 관리자만 답변이 가능합니다.');
		        }
				else {
		            // 비회원의 경우는 비밀글에 답변이 불가함
		            if (!IS_ADMIN)
		                alert('비회원의 비밀글에는 답변이 불가합니다.');
		        }
		    }
		
		    // 최대 답변은 테이블에 잡아놓은 wr_reply 사이즈만큼만 가능합니다.
		    if (strlen($write['wr_reply']) == 10)
		        alert("더 이상 답변하실 수 없습니다.\\n\\n답변은 10단계 까지만 가능합니다.");
		
			$reply = $this->Board_model->get_reply_step(BO_TABLE, $write['wr_num'], $board['bo_reply_order'], $write['wr_reply']);
			
		    $title_msg = '글답변';
		}
		else
		    alert('잘못된 접근입니다.');

		$notice_checked = $secret_checked = 0; // check 필드
		
		$is_notice = $is_nocomt = FALSE;
        if (IS_ADMIN) {
			if ($board['bo_use_comment'])
				$is_nocomt = TRUE;

            if ($w != 'r') {
    		    $is_notice = TRUE;
                if ($w == 'u') {
    		        // 답변 수정시 공지 체크 없음
    		        if ($write['wr_reply'])
    		            $is_notice = FALSE;
    		        else
    		            $notice_checked = (in_array((int)$wr_id, $notice_array)) ? 1 : 0;
    		    }
            }
		}

		$is_secret = $board['bo_use_secret'];
		$is_editor = ($board['bo_use_editor']) ? TRUE : FALSE;
		$is_email  = ($this->config->item('cf_use_email') && $board['bo_use_email'] && $this->config->item('cf_email_wr_write')) ? TRUE : FALSE;
		$is_sign   = (!IS_MEMBER || (IS_ADMIN && $w == 'u' && $member['mb_id'] != $write['mb_id'])) ? TRUE : FALSE;
		
		// 분류
		$category = FALSE;
		if ($board['bo_use_category']) {
		    $this->load->helper('category');
            $category = make_category(array(
                'type' => 'bo_'.BO_TABLE,
                'id'   => 'ca_code',
                'code' => $sca
            ));
		}
		
		$name = $email = '';
		if ($w == '' || $w == 'r') {
			if (IS_MEMBER) {
		        $name = cut_str(get_text($write['wr_name']), 20);
		        $email = $member['mb_email'];
	        }
	        
	        if ($w == 'r' && strpos($write['wr_option'], 'secret') !== FALSE) {
		        $is_secret = TRUE;
		        $secret_checked = 1;
		    }
		}
		else if ($w == 'u') {
			$name = cut_str(get_text($write['wr_name']), 20);
		    $email = $write['wr_email'];
		    
		    if (strpos($write['wr_option'], 'secret') !== FALSE)
		        $secret_checked = 1;
		}
		
		// 히든 옵션
		$option_hidden = '';
		if ($is_editor) $option_hidden .= "<input type='hidden' name='editor' value='editor' />";

		// 옵션 박스
		$option = $option_check = array();
	    if ($is_notice) {
	    	$option['notice'] = array('title'=>'공지', 'value'=>'1');
	    	$option_check['notice'] = $notice_checked;
	    }

		if ($is_secret) {
	        if (IS_ADMIN || $is_secret == 1) {
	        	$option['secret'] = array('title'=>'비밀글', 'value'=>'secret');
	    		$option_check['secret'] = $secret_checked;
			}
			else
	            $option_hidden .= "<input type='hidden' name='secret' value='secret' />";
	    }

	    if ($is_email) {
			$option['mail'] = array('title'=>'답변메일받기', 'value'=>'mail');
	    	$option_check['mail'] = ($w == 'u' && strpos($write['wr_option'], 'mail') !== FALSE) ? 1 : 0;
     	}

        if ($is_nocomt) {
        	$option['nocomt'] = array('title'=>'댓글금지', 'value'=>'nocomt');
	    	$option_check['nocomt'] = (strpos($write['wr_option'], 'nocomt') !== FALSE) ? 1 : 0;
        }
        
        // 제목
		$subject = cut_str(get_text($write['wr_subject']), 255);
		
		// 내용
		if ($w == '')
		    $content = $board['bo_insert_content'];
		else if ($w == 'r') {
			$subject = '';
			$content = $board['bo_insert_content'];
		}
		else if ($is_editor)
			$content = str_replace('&', '&amp;', $write['wr_content']); // Tag를 온전히 출력하기 위해서
		else
		    $content = get_text($write['wr_content']);
     	

     	// 에디터
        $editor = $editorConfig = '';
        if ($is_editor) {
            $attach = array();
            if ($w == 'u' && ($write['wr_count_file'] || $write['wr_count_image'])) {
				$CI =& get_instance();
                $CI->load->model('Board_file_model');
                $result = $CI->Board_file_model->get_files(BO_TABLE, $wr_id, 'bf_no,bf_editor,bf_source,bf_file,bf_filesize', 'all');

				$base_url = $this->config->item('base_url');
                foreach ($result as $row) {
                    $filename = $row['bf_source'];
                    if ($row['bf_editor']) {
						$filepath = $base_url.DATA_DIR.'/file/'.BO_TABLE.'/'.$row['bf_file'];
                        $attach['image'][] = array(
                            'attacher' => 'image',
                            'data' => array(
                                'imageurl' => $filepath,
                        		'filename' => $filename,
                        		'filesize' => (int)$row['bf_filesize'],
                        		'thumburl' => $filepath
                            )
                        );
					}
					else {
                        $attach['file'][] = array(
                            'attacher' => 'file',
                            'data' => array(
                                'attachurl' => $base_url.RT_PATH.'/board/'.BO_TABLE.'/download/wr_id/'.$wr_id.'/no/'.$row['bf_no'],
                        		'filemime' => 'application/octet-stream', // mime_content_type();
                        		'filename' => $filename,
                        		'filesize' => (int)$row['bf_filesize']
                            )
                        );
                    }
                }
            }

            $editorConfig = array( // Editor 설정
				'editor' => array(
					'initializedId' => '1', // 에디터 넘버링
					'wrapper'	    => 'tx_trex_container', // 에디터 컨테이너 박스 아이디
					'form'		    => 'fwrite', // 폼 이름
					'field'			=> 'wr_content', // 필드 이름
					'content' 		=> $content,  // 내용
					'attachments'	=> $attach // 첨부
				)
			);

            $editor = $this->load->view('board/editor', $editorConfig['editor'], TRUE);
			$content = ''; // 그냥 비우기
        }

		// SyntaxHighlighter
		$syntax = FALSE;
		if ($board['bo_use_syntax'] && $is_editor) {
			$this->load->config('cf_syntax');
			$syntax = $this->config->item('brush_name');
		}
		
		$head = array(
			'title' => $board['gr_subject'].' > '.$board['bo_subject'].' > '.$title_msg,
			'sca' => ($sca) ? str_replace('.', '-', $sca) : ''
		);
		$data = array(
			'title_msg' => $title_msg,
			'w' 		=> $w,
			'wr_id'   	=> $wr_id,
			'sca_str' 	=> ($sca) ? '?sca='.$sca : '',
			'qstr' 	  	=> $param->replace('w,wr_id'),
			
			'mb_id' 	   => (!$w && IS_MEMBER) ? $member['mb_id'] : 'guest',
			'name'  	   => $name,
			'email' 	   => $email,
			'subject' 	   => $subject,
			'content' 	   => $content,
			'editor'  	   => $editor,
			'editorConfig' => json_encode($editorConfig),

			'option' 		=> $option,
			'option_check'  => json_encode($option_check),
			'option_hidden' => $option_hidden,

			'category'  => $category,
			'is_editor' => $is_editor,
			'is_sign'   => $is_sign,
			'syntax'	=> $syntax
		);

		// Extra
		if ($board['bo_use_extra']) {
			if ($w == 'u')
				$data = array_merge($data, $this->Board_model->get_extra(BO_TABLE, $wr_id));
			else {
				$extra = $this->db->list_fields('ki_extra_'.BO_TABLE);
				foreach ($extra as $fld) {
					if ($fld == 'wr_id')
						continue;
					$data[$fld] = FALSE;
				}
			}
		}

		// JavaScript Load
		if ($is_editor) { $js[] = '../editor/js/editor_loader'; $js[] = 'editor_config'; }
		if (!IS_MEMBER) { $js[] = 'md5'; $js[] = 'kcaptcha'; }
		if ($board['bo_use_category']) $js[] = 'category';

		widget::run('head', $head);
		$this->load->view('board/'.$board['bo_skin'].'/write', $data);
		widget::run('tail', array('js' => $js));
	}
}