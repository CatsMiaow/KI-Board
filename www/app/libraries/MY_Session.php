<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* CI 2.2.0
SWFUpload에서 Session 문제로 Core 수정
*/
class MY_Session extends CI_Session {
	function __construct() {
		parent::__construct();
	}

	function sess_read() {
		// Fetch the cookie
		$session = $this->CI->input->cookie($this->sess_cookie_name);

		// No cookie?  Goodbye cruel world!...
		if ($session === FALSE)
		{
			log_message('debug', 'A session cookie was not found.');
			return FALSE;
		}

		// HMAC authentication
		$len = strlen($session) - 40;

		if ($len <= 0)
		{
			log_message('error', 'Session: The session cookie was not signed.');
			return FALSE;
		}

		// Check cookie authentication
		$hmac = substr($session, $len);
		$session = substr($session, 0, $len);

		// Time-attack-safe comparison
		$hmac_check = hash_hmac('sha1', $session, $this->encryption_key);
		$diff = 0;

		for ($i = 0; $i < 40; $i++)
		{
			$xor = ord($hmac[$i]) ^ ord($hmac_check[$i]);
			$diff |= $xor;
		}

		if ($diff !== 0)
		{
			log_message('error', 'Session: HMAC mismatch. The session cookie data did not match what was expected.');
			$this->sess_destroy();
			return FALSE;
		}

		// Decrypt the cookie data
		if ($this->sess_encrypt_cookie == TRUE)
		{
			$session = $this->CI->encrypt->decode($session);
		}

		// Unserialize the session array
		$session = $this->_unserialize($session);

		// Is the session data we unserialized an array with the correct format?
		if ( ! is_array($session) OR ! isset($session['session_id']) OR ! isset($session['ip_address']) OR ! isset($session['user_agent']) OR ! isset($session['last_activity']))
		{
			$this->sess_destroy();
			return FALSE;
		}

		// Is the session current?
		if (($session['last_activity'] + $this->sess_expiration) < $this->now)
		{
			$this->sess_destroy();
			return FALSE;
		}

		// Does the IP Match?
		if ($this->sess_match_ip == TRUE AND $session['ip_address'] != $this->CI->input->ip_address())
		{
			$this->sess_destroy();
			return FALSE;
		}

		// Does the User Agent Match?
		if ($this->sess_match_useragent == TRUE AND trim($session['user_agent']) != trim(substr($this->CI->input->user_agent(), 0, 120)))
		{
			// swfupload
			if (trim($session['user_agent']) != 'Shockwave Flash' && $this->CI->input->user_agent() != 'Shockwave Flash') {
				$this->sess_destroy();
				return FALSE;
			}
		}

		// Is there a corresponding session in the DB?
		if ($this->sess_use_database === TRUE)
		{
			$this->CI->db->where('session_id', $session['session_id']);

			if ($this->sess_match_ip == TRUE)
			{
				$this->CI->db->where('ip_address', $session['ip_address']);
			}

			if ($this->sess_match_useragent == TRUE && trim($session['user_agent']) != 'Shockwave Flash') // swfupload
			{
				$this->CI->db->where('user_agent', $session['user_agent']);
			}

			$query = $this->CI->db->get($this->sess_table_name);

			// No result?  Kill it!
			if ($query->num_rows() == 0)
			{
				$this->sess_destroy();
				return FALSE;
			}

			// Is there custom data?  If so, add it to the main session array
			$row = $query->row();
			if (isset($row->user_data) AND $row->user_data != '')
			{
				$custom_data = $this->_unserialize($row->user_data);

				if (is_array($custom_data))
				{
					foreach ($custom_data as $key => $val)
					{
						$session[$key] = $val;
					}
				}
			}
		}

		// Session is valid!
		$this->userdata = $session;
		unset($session);

		return TRUE;
	}
}