<?php
class Profile extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->helper(array('url', 'textual', 'sideview'));
	}

	function qry($mb_id) {
		if (!IS_MEMBER)
			alert_close("회원만 이용하실 수 있습니다.");

		$member = unserialize(MEMBER);

		if (!$member['mb_open'] && !SU_ADMIN && $member['mb_id'] != $mb_id)
			alert_close("자신의 정보를 공개하지 않으면 다른분의 정보를 조회할 수 없습니다.\\n\\n정보공개 설정은 회원정보수정에서 하실 수 있습니다.");

		$mb = $this->Basic_model->get_member($mb_id, "mb_id, mb_level, mb_point, mb_homepage, mb_open, mb_nick, mb_datetime, mb_today_login, mb_profile");
		if (!isset($mb['mb_id']))
			alert_close("회원정보가 존재하지 않습니다.\\n\\n탈퇴하였을 수 있습니다.");

		if (!$mb['mb_open'] && !SU_ADMIN && $member['mb_id'] != $mb_id)
			alert_close("정보공개를 하지 않았습니다.");

		$name = ($this->config->item('cf_use_nick') && $mb['mb_nick']) ? $mb['mb_nick'] : $mb['mb_name'];
		$name = get_sideview($mb['mb_id'], $name);

		// 회원가입후 몇일째인지? + 1 은 당일을 포함한다는 뜻
		$query = $this->db->query("select (TO_DAYS('".TIME_YMDHIS."') - TO_DAYS('".$mb['mb_datetime']."') + 1) as days");
		$row = $query->row_array();
		$mb_reg_after = $row['days'];

		$mb_homepage = prep_url($mb['mb_homepage']);
		$mb_profile = $mb['mb_profile'] ? conv_content($mb['mb_profile'], FALSE) : "소개 내용이 없습니다.";
		$mb_join_date = ($member['mb_level'] >= $mb['mb_level']) ?  substr($mb['mb_datetime'],0,10) ." (".$mb_reg_after." 일)" : "알 수 없음";
		$mb_last_login = ($member['mb_level'] >= $mb['mb_level']) ? $mb['mb_today_login'] : "알 수 없음";

		$head = array('title' => $mb['mb_nick']."님의 자기소개");
		$data = array(
			'name' 		 => $name,
			'profile' 	 => $mb_profile,
			'homepage' 	 => $mb_homepage,
			'point' 	 => number_format($mb['mb_point']),
			'join_date'  => $mb_join_date,
			'last_login' => $mb_last_login
		);

		widget::run('head', $head);
		$this->load->view('member/profile', $data);
		widget::run('tail');
	}
}
?>