<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['cf_delay_sec'] = 10; // 글쓰기 딜레이

$config['cf_email_wr_write']       = FALSE; // 답변메일 받기 사용
$config['cf_email_wr_super_admin'] = FALSE; // 최고관리자 메일 발송
$config['cf_email_wr_group_admin'] = FALSE; // 그룹 관리자 메일 발송
$config['cf_email_wr_board_admin'] = FALSE; // 게시판 관리자 메일 발송

$config['cf_use_mvcp_log'] = TRUE; // 복사, 이동시 로그 사용
$config['cf_search_part'] = 100000; // 검색 파트 개수 조절
