<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// 각 관리자 체크( 를... 좀 더 체계적으로 할 수 없을까 )
function is_admin($member, $board) {
	if (empty($member['mb_id'])) return FALSE;

	switch ($member['mb_id']) {
		case ADMIN : $is_admin = 'super'; break;
		case $board['gr_admin'] : $is_admin = 'group'; break;
		case $board['bo_admin'] : $is_admin = 'board'; break;
		default: $is_admin = FALSE; break;
	}

	return $is_admin;
}

// URL 인코드
function url_encode($str) {
	return str_replace('%', '.', urlencode($str));
}

// 리스트 정보 가공
function get_convert($row, $board, $subject_len=60, $qstr, $list_view=FALSE) {
	$row['href'] = RT_PATH.'/board/'.BO_TABLE.'/view/wr_id/'.$row['wr_id'].$qstr;
	$row['subject'] = cut_str(get_text($row['wr_subject']), $subject_len);

	$tmp_name = cut_str(get_text($row['wr_name']), 14);
	$row['name'] = ($board['bo_use_sideview']) ? get_sideview($row['mb_id'], $tmp_name) : "<span class='".($row['mb_id']?'member':'guest')."'>".$tmp_name."</span>";
	
	// 당일인 경우 시간으로 표시함
    $row['datetime'] = substr($row['wr_datetime'],0,10);
	$row['datetime2'] = ($row['datetime'] == TIME_YMD) ? substr($row['wr_datetime'],11,5) : substr($row['wr_datetime'],5,5);

    if ($list_view) {
		// 최근 갱신 시간
		$row['last'] = substr($row['wr_last'],0,10);
		$row['last2'] = ($row['last'] == TIME_YMD) ? substr($row['wr_last'],11,5) : substr($row['wr_last'],5,5);

		$row['comment_cnt'] = '';
		if ($row['wr_comment'])
			$row['comment_cnt'] = '('.$row['wr_comment'].')';

		// 답변 여백
		$reply = strlen($row['wr_reply']);
		$row['ico_reply'] = '';
		if ($reply > 1) {
			for ($k=1; $k<$reply; $k++)
				$row['ico_reply'] .= '　';
		}
		if ($reply > 0)
			$row['ico_reply'] .= '<span class="glyphicon glyphicon-expand text-danger" title="답변"></span>';

		$row['ico_new'] = '';
		if ($row['wr_datetime'] >= date("Y-m-d H:i:s", time() - ($board['bo_new'] * 3600)))
			$row['ico_new'] = '<span class="glyphicon glyphicon-fire text-danger" title="최신"></span>';

		$row['ico_hot'] = '';
		if ($row['wr_hit'] >= $board['bo_hot'])
			$row['ico_hot'] = '<span class="glyphicon glyphicon-flag text-danger" title="이슈 '.$board['bo_hot'].'Hit!"></span>';

		$row['ico_secret'] = '';
		if (strpos($row['wr_option'], 'secret') !== FALSE)
			$row['ico_secret'] = '<span class="glyphicon glyphicon-lock text-danger" title="비밀"></span>';

		// 가변 파일 - 첨부파일이 0개 이상일 경우에만 실행
		$row['ico_file'] = $row['ico_image'] = $row['ico_movie'] = '';

		if ($row['wr_count_file'] > 0)
			$row['ico_file'] = '<span class="glyphicon glyphicon-file text-danger" title="파일"></span>';

		if ($row['wr_count_image'] > 0)
			$row['ico_image'] = '<span class="glyphicon glyphicon-picture text-danger" title="이미지"></span>';

		if (stripos($row['wr_content'], '&lt;embed'))
			$row['ico_movie'] = '<span class="glyphicon glyphicon-facetime-video text-danger" title="동영상"></span>';
	}

    return $row;
}

// SNS 보내기
function sns_post($bo_table, $wr_id, $title, $content) {
	$url = urlencode('http://'.$_SERVER['HTTP_HOST'].'/board/'.$bo_table.'/view/wr_id/'.$wr_id);
	$title_de = strip_tags($title);
	$content_de = cut_str(trim(str_replace(array('&nbsp;',"\n"), ' ', strip_tags($content))), 100);

	$title = urlencode($title_de);
	$content = urlencode($content_de);
	$img = urlencode('http://'.$_SERVER['HTTP_HOST'].'/_board/thumbnail/sns/'.$bo_table.'/'.$wr_id);

	$str = '';
	$str .= "<a href='//twitter.com/home?status=".$title."%0A".$url."' target='_blank'><span class='label label-info' title='트위터'>Twitter</span></a>";
	$str .= " <a href='//www.facebook.com/sharer.php?s=100&p[url]=".$url."&p[images][0]=".$img."&p[title]=".$title."&p[summary]=".$content."' target='_blank'><span class='label label-danger' title='페이스북'>Facebook</span></a>";
	$str .= " <a href='//me2day.net/posts/new?new_post[body]=%22".str_replace('%22', '%5C%22', $title)."%22:".$url."%0A%0A".$url."&new_post[tags]=' target='_blank'><span class='label label-success' title='미투데이'>me2day</span></a>";
	$str .= " <a href='//csp.cyworld.com/bi/bi_recommend_pop.php?url=".$url."&title=".urlencode(base64_encode($title_de))."&thumbnail=".$img."&summary=".urlencode(base64_encode($content_de))."&writer=' target='_blank'><span class='label label-warning' title='C공감'>Cyworld</span></a>";

	return $str;
}