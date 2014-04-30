<?php
class Member_model extends CI_Model {
	function __construct() {
		parent::__construct();
		$this->load->library('encrypt');
	}

	function list_result($sst, $sod, $sfl, $stx, $limit, $offset) {
		$this->db->start_cache();
		if ($stx) {
			switch ($sfl) {
				case "mb_point" :
					$this->db->where('mb_point >=', $stx);
				break;
				case "mb_level" :
					$this->db->where('mb_level', $stx);
				break;
				case "mb_tel" :
				case "mb_hp" :
					$this->db->like($sfl, $stx, 'brfore');
				break;
				default :
					$this->db->like($sfl, $stx, 'after');
				break;
			}
		}
		$this->db->stop_cache();

		$result['total_cnt'] = $this->db->count_all_results('ki_member');

		$this->db->select('mb_id,mb_name,mb_nick,mb_level,mb_point,mb_mailling,mb_open,mb_email_certify,mb_today_login,mb_leave_date');
		$this->db->order_by($sst, $sod);
		$qry = $this->db->get('ki_member', $limit, $offset);
		$result['qry'] = $qry->result_array();

		$this->db->where('mb_leave_date <>', '');
		$result['leave_cnt'] = $this->db->count_all_results('ki_member');

		$this->db->flush_cache();

		return $result;
	}

	function insert() {
		if ($this->config->item('cf_use_nick'))
			$mb_nick = $this->input->post('mb_nick');
		else
			$mb_nick = substr(md5(uniqid($this->input->post('mb_id'), TRUE)), 0, 14);

		$sql = array(
			'mb_id'			   => $this->input->post('mb_id'),
			'mb_password'	   => $this->encrypt->encode($this->input->post('mb_password')),
			'mb_name'		   => $this->input->post('mb_name'),
			'mb_sex'		   => $this->input->post('mb_sex'),
			'mb_birth'		   => $this->input->post('mb_birth'),
			'mb_nick'		   => $mb_nick,
			'mb_email'		   => $this->input->post('mb_email'),
			'mb_homepage'	   => $this->input->post('mb_homepage'),
			'mb_tel'		   => $this->input->post('mb_tel'),
			'mb_hp'			   => $this->input->post('mb_hp'),
			'mb_zip'		   => $this->input->post('mb_zip1').'-'.$this->input->post('mb_zip2'),
			'mb_addr1'		   => $this->input->post('mb_addr1'),
			'mb_addr2'		   => $this->input->post('mb_addr2'),
			'mb_profile'	   => $this->input->post('mb_profile'),
			'mb_datetime'	   => TIME_YMDHIS,
			'mb_ip'			   => $this->input->server('REMOTE_ADDR'),
			'mb_level'		   => $this->input->post('mb_level'),
			'mb_login_ip'	   => $this->input->server('REMOTE_ADDR'),
			'mb_mailling'	   => $this->input->post('mb_mailling'),
			'mb_open'		   => $this->input->post('mb_open'),
			'mb_email_certify' => TIME_YMDHIS,
			'mb_leave_date'    => ''
		);

		$this->db->insert('ki_member', $sql);
	}

	function update() {
		$sql = array(
			'mb_name'	    => $this->input->post('mb_name'),
			'mb_level'      => $this->input->post('mb_level'),
			'mb_email'      => $this->input->post('mb_email'),
			'mb_birth'		=> $this->input->post('mb_birth'),
			'mb_sex'		=> $this->input->post('mb_sex'),
			'mb_homepage'   => $this->input->post('mb_homepage'),
			'mb_tel'        => $this->input->post('mb_tel'),
			'mb_hp'         => $this->input->post('mb_hp'),
			'mb_zip'        => $this->input->post('mb_zip1').'-'.$this->input->post('mb_zip2'),
			'mb_addr1'      => $this->input->post('mb_addr1'),
			'mb_addr2'      => $this->input->post('mb_addr2'),
			'mb_profile'    => $this->input->post('mb_profile'),
			'mb_mailling'   => $this->input->post('mb_mailling'),
			'mb_open'       => $this->input->post('mb_open'),
			'mb_leave_date' => $this->input->post('mb_leave_date')
		);
		if ($this->config->item('cf_use_nick'))
			$sql['mb_nick'] = $this->input->post('mb_nick');

		if ($this->input->post('mb_password'))
			$sql['mb_password'] = $this->encrypt->encode($this->input->post('mb_password'));

		if ($this->input->post('passive_certify'))
			$sql['mb_email_certify'] = TIME_YMDHIS;

		$this->db->update('ki_member', $sql, array('mb_id' => $this->input->post('mb_id')));
	}

	function list_update($mb_id, $mb_level) {
		$this->db->update('ki_member', array(
			'mb_level' => $mb_level
		), array('mb_id' => $mb_id));
	}

	function delete($mb_ids) {
		// 회원자료는 정보만 없앤 후 아이디는 보관하여 다른 사람이 사용하지 못하도록 함
		// 게시판에서 회원아이디는 삭제하지 않기 때문
		$sql = array(
			'mb_password'	   => '',
			'mb_email'		   => '',
			'mb_homepage'	   => '',
			'mb_password_q	'  => '',
			'mb_password_a'	   => '',
			'mb_level'		   => '1',
			'mb_sex'		   => '',
			'mb_birth'		   => '',
			'mb_tel'		   => '',
			'mb_hp'			   => '',
			'mb_zip'		   => '-',
			'mb_addr1'		   => '',
			'mb_addr2'		   => '',
			'mb_point'		   => '',
			'mb_leave_date'	   => date('Ymd', time()),
			'mb_email_certify' => '',
			'mb_mailling'	   => '',
			'mb_open'		   => '',
			'mb_profile'	   => '',
			'mb_memo_call'	   => '',
			'mb_memo_cnt'	   => ''
		);
		$this->db->where_in('mb_id', $mb_ids);
		$this->db->update('ki_member', $sql);

		// 포인트 & 쪽지 삭제
		$this->db->where_in('mb_id', $mb_ids);
		$this->db->delete(array('ki_point','ki_memo')); 

		// 그룹관리자인 경우 그룹관리자를 공백으로
		$this->db->where_in('gr_admin', $mb_ids);
		$this->db->update('ki_board_group', array('gr_admin' => ''));

		// 게시판관리자인 경우 게시판관리자를 공백으로
		$this->db->where_in('bo_admin', $mb_ids);
		$this->db->update('ki_board', array('bo_admin' => ''));
	}

	function get_mbs_infor($mb_ids, $fields='*') {
		$this->db->select($fields);
		$this->db->where_in('mb_id', $mb_ids);
		$query = $this->db->get('ki_member');
		return $query->result_array();
	}
}
?>