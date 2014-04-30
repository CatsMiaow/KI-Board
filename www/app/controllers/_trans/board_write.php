<?php
class Board_write extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('Board_model');
		$this->load->config('cf_board');
		$this->load->helper('board');
	}
	
	function update() {
		$w = $this->input->post('w');
		
		$this->load->library('form_validation');
		$config = array(
			array('field'=>'bo_table', 'label'=>'게시판아이디', 'rules'=>'trim|required|alpha_dash'),
			array('field'=>'wr_subject', 'label'=>'제목', 'rules'=>'trim|required'),
			array('field'=>'wr_content', 'label'=>'내용', 'rules'=>'trim|required|xss_clean')
		);
		if (!IS_MEMBER) {
			$config[] = array('field'=>'wr_name', 'label'=>'이름', 'rules'=>'trim|required|max_length[10]');
			$pass_req = ($w == 'u') ? '' : '|required';
			$config[] = array('field'=>'wr_password', 'label'=>'비밀번호', 'rules'=>'trim'.$pass_req.'|max_length[20]|md5');
			$config[] = array('field'=>'wr_email', 'label'=>'이메일', 'rules'=>'trim|valid_email');
			$config[] = array('field'=>'wr_key', 'label'=>'자동등록방지', 'rules'=>'trim|required');
		}
		
		$this->form_validation->set_rules($config);
		if ($this->form_validation->run() == FALSE)
			alert('올바르지 않은 접근입니다.');
		else {
			if (!IS_MEMBER)
				check_wrkey();
			
			$bo_table = $this->input->post('bo_table');
			$wr_id = $this->input->post('wr_id');
			$notice = $this->input->post('notice');
			$wr_content = $this->input->post('wr_content');
			
			$board = $this->Basic_model->get_board($bo_table, 'bo_subject, bo_admin, bo_write_level, bo_reply_level, bo_use_private, bo_use_secret, bo_use_name, bo_use_email, bo_reply_order, bo_use_extra, bo_notice', TRUE);
			$member = unserialize(MEMBER);

			define('IS_ADMIN', is_admin($member, $board));

			// 개인게시판 권한
            if ($board['bo_use_private'] && !IS_ADMIN && !SU_ADMIN)
                alert('작성 권한이 없습니다.');

			$secret = $this->input->post('secret');
			$notice_array = explode(',', trim($board['bo_notice']));
			
			if ($w == 'u' || $w == 'r') {
			    $write = $this->Basic_model->get_write($bo_table, $wr_id, 'wr_id, wr_num, wr_reply, wr_option, mb_id, wr_password, wr_name, wr_email, wr_count_file, wr_count_image');
			    if (!isset($write['wr_id']))
			        alert('글이 존재하지 않습니다.\\n\\n글이 삭제되었거나 이동하였을 수 있습니다.');
			        
       			$wr_id = $write['wr_id'];
			}
			
			// 비밀글은 사용일 경우에만 가능해야 함
			if (!IS_ADMIN && !$board['bo_use_secret'] && $secret)
				alert('비밀글 미사용 게시판 이므로 비밀글로 등록할 수 없습니다.');
			
			if ($w == '' || $w == 'u') {
			    // 글쓰기 권한과 수정은 별도로 처리되어야 함
			    if ($w == 'u' && IS_MEMBER && $write['mb_id'] == $member['mb_id']) {
					// 통과
			    }
				else if ($member['mb_level'] < $board['bo_write_level'])
			        alert('글을 쓸 권한이 없습니다.');

				if (!IS_ADMIN && $notice)
					alert('관리자만 공지할 수 있습니다.');
			}
			else if ($w == 'r') {
			    if (in_array((int)$wr_id, $notice_array))
			        alert('공지에는 답변 할 수 없습니다.');
			
			    if ($member['mb_level'] < $board['bo_reply_level'])
			        alert('글을 답변할 권한이 없습니다.');
			
			    // 답변의 답변 단계 체크 - wr_reply varchar(10)
			    if (strlen($write['wr_reply']) == 10)
			        alert('더 이상 답변하실 수 없습니다.\\n\\n답변은 10단계 까지만 가능합니다.');

				// 하나의 답변에 대한 갯수 체크 (최대 26개)
			    $reply = $this->Board_model->get_reply_step($bo_table, $write['wr_num'], $board['bo_reply_order'], $write['wr_reply']);
			}
			else
			    alert('w 값이 제대로 넘어오지 않았습니다.');
			
			if (!IS_ADMIN && ($w == '' || $w == 'r')) {
				if ($this->session->userdata('ss_datetime') >= (time() - $this->config->item('cf_delay_sec')))
			        alert('너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.');
			
			    $this->session->set_userdata('ss_datetime', time());
			
				// 동일내용 연속 등록 불가
				$row = $this->Board_model->same_write($bo_table);
				$curr_md5 = md5($this->input->server('REMOTE_ADDR').$this->input->post('wr_subject').$wr_content);
				
				if ($row && $row['prev_md5'] == $curr_md5)
					alert('동일한 내용을 연속해서 등록할 수 없습니다.');
			}

			if ($w == '' || $w == 'r') {
			    if (IS_MEMBER) {
			        $mb['mb_id']       = $member['mb_id'];
			        $mb['wr_name']     = $board['bo_use_name'] ? $member['mb_name'] : $member['mb_nick'];
			        $mb['wr_password'] = $member['mb_password'];
			        $mb['wr_email']    = $member['mb_email'];
			    }
			    else {
			    	$this->load->library('encrypt');
			        $mb['mb_id']       = '';
			        $mb['wr_name']     = $this->input->post('wr_name');
			        $mb['wr_password'] = $this->encrypt->encode($this->input->post('wr_password'));
			        $mb['wr_email']    = $this->input->post('wr_email');
			    }
			
			    if ($w == 'r') {
			        // 답변의 원글이 비밀글이라면 패스워드는 원글과 동일하게 넣는다.
			        if ($secret)
			            $mb['wr_password'] = $write['wr_password'];
			
			        $wr_num = $write['wr_num'];
			        $wr_reply = $reply;
			    }
			    else {
			    	// 가장 작은 번호에 1을 빼서 넘겨줌
			        $wr_num = $this->Board_model->get_min_num($bo_table);
	    			$wr_num = (int)($wr_num - 1);
			        $wr_reply = '';
			    }
			
				// Insert
				$wr_id = $this->Board_model->write_insert($bo_table, $wr_content, $wr_num, $wr_reply, $mb, $board['bo_notice']);
			}
			else if ($w == 'u') {
			    if (IS_MEMBER) {
			        // 자신의 글이라면
			        if ($member['mb_id'] == $write['mb_id']) {
			            $mb['mb_id']    = $member['mb_id'];
			            $mb['wr_name']  = $board['bo_use_name'] ? $member['mb_name'] : $member['mb_nick'];
			            $mb['wr_email'] = $member['mb_email'];
			        }
					else {
			            $mb['mb_id']    = $write['mb_id'];
			            $mb['wr_name']  = $write['wr_name'];
			            $mb['wr_email'] = $write['wr_email'];
			        }
			    }
				else {
			        $mb['mb_id']    = '';
					$mb['wr_name']  = $this->input->post('wr_name');
					$mb['wr_email'] = $this->input->post('wr_email');
			    }
			
				// 공지사항 체크
				$bo_notice = '';
				if (IS_ADMIN) {
				    $bo_notice = $board['bo_notice'];
					if ($notice) {
				        if (!in_array((int)$wr_id, $notice_array))
				            $bo_notice = $wr_id.','.$board['bo_notice'];
				    }
				    else {
				        $nokey = array_search((int)$wr_id, $notice_array);
                        if (is_int($nokey)) {
                            unset($notice_array[(int)$nokey]);
                            $bo_notice = implode(',', $notice_array);
                        }
				    }
			    }
			
				// Update
				$this->Board_model->write_update($bo_table, $wr_content, $wr_id, $mb, $bo_notice);
			    
			    $wr_num = $write['wr_num']; // 비밀글 세션 저장에 필요
			}

			// Extra
			if ($board['bo_use_extra']) {
				$extra = $this->db->list_fields('ki_extra_'.$bo_table);
				foreach ($extra as $fld) {
					if ($fld == 'wr_id')
						continue;
					$sql[$fld] = $this->input->post($fld);
				}

				$this->Board_model->extra_update($w, $bo_table, $wr_id, $sql);
			}
			
			// Editor
            $editor = $this->input->post('editor');
            if ($editor) {
                $images = $this->input->post('images'); // 이미지소스
                $inames = $this->input->post('inames'); // 이미지원본
                $files  = $this->input->post('files');  // 파일소스
    			$fnames = $this->input->post('fnames'); // 파일원본
                
                $this->load->model('Board_file_model');
                $val_img = $val_file = ''; $time = time().'_';
                $base_url = $this->config->item('base_url').RT_PATH;
                
                // Images
                $cont_img = $edt_img = $rest_img = array();
                if ($images && count($images) > 0)
                    $cont_img = array_unique($images);
                            
				$no = 0;
				if ($w == 'u' && $write['wr_count_image'] > 0) {
				    $result = $this->Board_file_model->get_files($bo_table, $wr_id, 'bf_no,bf_file', TRUE);
					foreach ($result as $row)
						$edt_img[$row['bf_no']] = $row['bf_file'];
					
                    if ($edt_img) {
						$rest_img = array_diff($edt_img, $cont_img);
                        
						$bf_nos = array();
						foreach($rest_img as $key => $row) {
							$bf_nos[] = $key;
							@unlink(DATA_PATH.'/file/'.$bo_table.'/'.$row);	
						}
						$no = max(array_keys($edt_img)) + 1;
						if ($bf_nos)
							$this->Board_file_model->file_delete($bo_table, $wr_id, $bf_nos, TRUE);
					}
				}
                
				if ($cont_img) {
				    $tstr_img = $nstr_img = array();
				    $rest_img = array_diff($cont_img, $edt_img);
					foreach ($rest_img as $key => $img) {
						$filename = $time.$img;
						$newimg = DATA_PATH.'/file/'.$bo_table.'/'.$filename;
						
						if (@rename(DATA_PATH.'/temp/'.$img, $newimg)) {
							$byte = @filesize($newimg);
							$size = @getimagesize($newimg);
                            
							$val_img .= "('".$bo_table."','".$wr_id."','1','".$no."','".$inames[$key]."','".$filename."','0','".$byte."','".$size[0]."','".$size[1]."','".$size[2]."','".TIME_YMDHIS."'),";
                            $tstr_img[] = $base_url.'/data/temp/'.$img;
                            $nstr_img[] = $base_url.'/data/file/'.$bo_table.'/'.$filename;
							$no++;
						}
					}
                    if ($val_img = substr($val_img, 0, -1)) {
                        $wr_content = str_replace($tstr_img, $nstr_img, $wr_content);
                        $this->Board_file_model->file_insert($bo_table, $wr_id, $val_img, TRUE);
                    }
				}
                
                // Files
    			$new_file = $old_file = $edt_file = $rest_file = array();
                if ($files && count($files) > 0) {
					$cont_file = array_unique($files);

					function newfile($val) { return !is_numeric($val); }
					function oldfile($val) { return is_numeric($val); }

					$new_file = array_filter($cont_file, 'newfile');
					$old_file = array_filter($cont_file, 'oldfile');
				}

    			$no = 0;
    			if ($w == 'u' && $write['wr_count_file'] > 0) {
    			    $result = $this->Board_file_model->get_files($bo_table, $wr_id, 'bf_no,bf_file');
    				foreach ($result as $row)
    					$edt_file[$row['bf_no']] = $row['bf_file'];
    				
                    if ($edt_file) {
						$edt_no = array_keys($edt_file);
    					$rest_file = array_diff($edt_no, $old_file);
    					foreach($rest_file as $row) {
    						@unlink(DATA_PATH.'/file/'.$bo_table.'/'.$edt_file[$row]);
    					}
    					$no = max($edt_no) + 1;
    					if ($rest_file)
    						$this->Board_file_model->file_delete($bo_table, $wr_id, $rest_file);
    				}
    			}
                
    			if ($new_file) {
                    $tstr_file = $nstr_file = array();
    			    $rest_file = array_diff($new_file, $edt_file);
    				foreach ($rest_file as $key => $file) {
    				    $filename = $time.$file;
    					$newfile = DATA_PATH.'/file/'.$bo_table.'/'.$filename;
    
                        if (@rename(DATA_PATH.'/temp/'.$file, $newfile)) {
    						$byte = @filesize($newfile);
    						$size = @getimagesize($newfile);
    						
    						$val_file .= "('".$bo_table."','".$wr_id."','0','".$no."','".$fnames[$key]."','".$filename."','0','".$byte."','".$size[0]."','".$size[1]."','".$size[2]."','".TIME_YMDHIS."'),";
                            $tstr_file[] = $base_url.'/data/temp/'.$file;
                            $nstr_file[] = $base_url.'/board/'.$bo_table.'/download/wr_id/'.$wr_id.'/no/'.$no;
    						$no++;
    					}
    				}
                    if ($val_file = substr($val_file, 0, -1)) {
                        $wr_content = str_replace($tstr_file, $nstr_file, $wr_content);
                        $this->Board_file_model->file_insert($bo_table, $wr_id, $val_file);                        
                    }
                }
                
                if ($val_img || $val_file)
                    $this->Board_model->content_update($bo_table, $wr_id, $wr_content);
			}
			
			// 비밀글이라면 세션에 비밀글의 아이디를 저장한다. 자신의 글은 다시 패스워드를 묻지 않기 위함
			if ($secret)
			    $this->session->set_userdata('ss_secret_'.$bo_table.'_'.$wr_num, TRUE);
			
			// 메일발송 사용 (수정글은 발송하지 않음)
			if ($w != 'u' && $this->config->item('cf_use_email') && $board['bo_use_email']) {
			    // 관리자의 정보를 얻고
				$super_admin = $this->Basic_model->get_member(ADMIN, 'mb_email');
				$group_admin = $this->Basic_model->get_member($board['gr_admin'], 'mb_email');
				$board_admin = $this->Basic_model->get_member($board['bo_admin'], 'mb_email');
			
				$this->load->helper('textual');
				$wr_subject = get_text(stripslashes($this->input->post('wr_subject')));			
			    $wr_content = conv_content(stripslashes($wr_content), $this->input->post('editor'));
			
			    $warr = array(''=>'입력', 'u'=>'수정', 'r'=>'답변');
			    $str = $warr[$w];
			
				$subject = "'".$board['bo_subject']."' 게시판에 ".$str."글이 올라왔습니다.";
			    $link_url = $this->config->item('base_url').'/board/'.$bo_table.'/view/wr_id/'.$wr_id;
			
			    $data = array(
					'wr_name' => $mb['wr_name'],
					'wr_subject' => $wr_subject,
					'wr_content' => $wr_content,
					'link_url' => $link_url
				);
				$content = $this->load->view('mail/write_update', $data, TRUE);
				
				$to_email = array();
				$this->load->library('email');

				$this->email->clear();
				$this->email->from(($mb['wr_email'] ? $mb['wr_email'] : $super_admin['mb_email']), $mb['wr_name']);
				
				// 게시판 관리자에게 보내는 메일
				if ($this->config->item('cf_email_wr_board_admin') && $board_admin['mb_email']) {
					$to_email[] = $board_admin['mb_email'];
				}
				
				// 그룹 관리자에게 보내는 메일
				if ($this->config->item('cf_email_wr_group_admin') && $group_admin['mb_email']) {
					if ($group_admin['mb_email'] != $board_admin['mb_email']) {
						$to_email[] = $group_admin['mb_email'];
					}
				}
				
				// 최고관리자에게 보내는 메일
				if ($this->config->item('cf_email_wr_super_admin') && $super_admin['mb_email']) {
					if ($super_admin['mb_email'] != $board_admin['mb_email'] && $super_admin['mb_email'] != $group_admin['mb_email']) {
						$to_email[] = $super_admin['mb_email'];
					}
				}
				
				// 답변글에만 원게시자가 있음
				// 답변 메일받기 (원게시자에게 보내는 메일)
				if ($w == 'r' && strpos($write['wr_option'], 'mail') !== FALSE && $write['wr_email'] && $write['wr_email'] != $mb['wr_email']) {
					if ($this->config->item('cf_email_wr_write'))
						$to_email[] = $write['wr_email'];
			    }
			    
			    $this->email->to($to_email);
				$this->email->subject($subject);
				$this->email->message($content);
				$this->email->send();
			}			

            $this->db->cache_delete('default', 'index');

			goto_url('board/'.$bo_table.'/view/wr_id/'.$wr_id.$this->input->post('qstr'));
		}
	}
	
	function delete() {
		$bo_table = $this->input->post('bo_table');
		$wr_id = $this->input->post('wr_id');

		$member = unserialize(MEMBER);
		$board = $this->Basic_model->get_board($bo_table, 'bo_admin, bo_count_delete, bo_use_extra, bo_notice, bo_min_wr_num', TRUE);
		$write = $this->Basic_model->get_write($bo_table, $wr_id, 'wr_id, wr_num, wr_reply, wr_option, wr_content, mb_id, wr_password');

		define('IS_ADMIN', is_admin($member, $board));
		
		$wr_ids = array();
		if (!is_array($wr_id)) { // 단일삭제
			if (!isset($write['wr_id']))
			    alert('등록된 글이 없습니다.');
			    
	  		// 수정 권한 IF
			if (IS_ADMIN == 'group' || IS_ADMIN == 'board') {
				$mb = $this->Basic_model->get_member($write['mb_id'], 'mb_level');
				$mb_level = (isset($mb['mb_level'])) ? $mb['mb_level'] : 1;
			}
			
			if (IS_ADMIN == 'super' && SU_ADMIN) {
				// 통과
			}
			else if (IS_ADMIN == 'group') { // 그룹관리자
				if ($member['mb_id'] == $board['gr_admin']) { // 자신이 관리하는 그룹인가
					if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
						alert('그룹관리자의 권한보다 높은 회원의 글이므로 삭제할 수 없습니다.');
				}
				else
					alert('자신이 관리하는 그룹의 게시판이 아니므로 글을 삭제할 수 없습니다.');
			}
			else if (IS_ADMIN == 'board') { // 게시판관리자
				if ($member['mb_id'] == $board['bo_admin']) { // 자신이 관리하는 게시판인가
					if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
						alert('게시판관리자의 권한보다 높은 회원의 글이므로 삭제할 수 없습니다.');
				}
				else
					alert('자신이 관리하는 게시판이 아니므로 글을 삭제할 수 없습니다.');
			}
			else {
				if ($write['mb_id']) { 
					if (!IS_MEMBER || $member['mb_id'] != $write['mb_id'])
						alert('자신의 글이 아니므로 삭제할 수 없습니다.');
				}
				else {
					$this->load->library('encrypt');
		        	if (md5($this->input->post('password')) != $this->encrypt->decode($write['wr_password']))
						alert("비밀번호가 맞지 않습니다.");	
				}
			}
			
			// 원글만 구한다.
		    $cnt = $this->Board_model->is_reply($bo_table, $wr_id, $write['wr_num'], $write['wr_reply']);
		    if ($cnt)
		        alert("이 글과 관련된 답변글이 존재하므로 삭제할 수 없습니다.\\n\\n우선 답변글부터 삭제하여 주십시오.");
		
		    // 코멘트 달린 원글의 수정 여부
		    if ($board['bo_count_delete'] > 0) {
			    $cnt = $this->Board_model->is_comment($bo_table, $wr_id, (IS_MEMBER) ? $member['mb_id'] : '');
			    if ($cnt >= $board['bo_count_delete'] && !IS_ADMIN)
			        alert("이 글과 관련된 코멘트가 존재하므로 삭제할 수 없습니다.\\n\\n코멘트가 ".$board['bo_count_delete']."건 이상 달린 원글은 삭제할 수 없습니다.");
	  		}
	  		
	  		$wr_ids = array($wr_id); // 배열화
		}
		else {
			foreach($write as $row) {
				if (!isset($row['wr_id']))
			    	continue;
			    
				// 수정 권한 IF
				if (IS_ADMIN == 'group' || IS_ADMIN == 'board') {
					$mb = $this->Basic_model->get_member($row['mb_id'], 'mb_level');
					$mb_level = (isset($mb['mb_level'])) ? $mb['mb_level'] : 1;
				}
			
				if (IS_ADMIN == 'super' && SU_ADMIN) {
					// 통과
				}
				else if (IS_ADMIN == 'group') { // 그룹관리자
					if ($member['mb_id'] == $board['gr_admin']) { // 자신이 관리하는 그룹인가
						if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
							continue;
					}
					else
						continue;
				}
				else if (IS_ADMIN == 'board') { // 게시판관리자
					if ($member['mb_id'] == $board['bo_admin']) { // 자신이 관리하는 게시판인가
						if ($member['mb_level'] < $mb_level) // 자신의 레벨이 낮다면
							continue;
					}
					else
						continue;
				}
				else
					continue; // 나머지는 삭제 불가
					
				$cnt = $this->Board_model->is_reply($bo_table, $row['wr_id'], $row['wr_num'], $row['wr_reply']);
		    	if ($cnt)
		    		continue;
		    		
	    		$wr_ids[] = $row['wr_id'];
			}
		}

		// 공지사항
		$bo_notice = '';
		if (IS_ADMIN && $wr_ids) {
			$notice_array = explode(',', trim($board['bo_notice']));
	        foreach($notice_array as $row) {
	        	if ($row && !in_array((int)$row, $wr_ids)) {
	                $bo_notice .= $row.',';
          		}
			}
		}

		$this->Board_model->write_delete($bo_table, $wr_ids, $bo_notice, $board['bo_min_wr_num'], $board['bo_use_extra']);
		
        $this->db->cache_delete('default', 'index');
        
		goto_url('board/'.$bo_table.'/lists'.$this->input->post('qstr'));
	}
}