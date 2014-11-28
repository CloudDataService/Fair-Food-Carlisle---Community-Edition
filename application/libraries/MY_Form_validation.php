<?php

class MY_Form_validation extends CI_Form_validation {

	protected $_CI; // instance of ci framework

	public function __construct()
	{
		parent::__construct();

		// get instance of ci framework
		$this->_CI = get_instance();
	}

	// make sure date is in valid british format
	public function parse_date($date)
	{
		if ( ! preg_match('/^[0-3]{1}[0-9]{1}\/[0-1]{1}[0-9]{1}\/[0-9]{4}$/', $date) )
			return false;

		$date = explode('/', $date);

		$mysql_date = $date[2] . '-' . $date[1] . '-' . $date[0];

		return $mysql_date;
	}

	// password must start with a capital letter, be at least 8 characters long and have 1 number in it
	// password more than 6 characters
	function password_restrict($password)
	{
		$this->set_message('check_email_unique', 'The password must be at least 6 characters long.');
		//return ( ! preg_match('/^(?=.{8,})[A-Z](?=.*[a-zA-Z])(?=.*[0-9]).*$/', $password) ? FALSE : TRUE);
		return ( ! preg_match('/^.{7,}$/', $password) ? FALSE : TRUE);
	}


	function other($index)
	{
		if (@$_POST[$index] == 'Other')
		{
			return '|required';
		}

		return false;
	}

	// checks to make sure an element is numeric, returns it as a float with two decimal places
	function numeric($str)
	{
		if ((bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str))
		{
			return sprintf('%.2F', $str);
		}

		return false;
	}

	public function check_email_unique($email, $u_id = FALSE)
	{
		// if users id is set
		if ($u_id !== FALSE)
		{
			// get users email from db
			$sql = 'SELECT
						u_email
					FROM
						user
					WHERE
						u_id = "' . (int)$u_id . '";';

			if ($row = $this->_CI->db->query($sql)->row_array())
			{
				// check to see if the new email they specified is not already in db
				// ignore their own current email
				$sql = 'SELECT
							u_id
						FROM
							user
						WHERE
							u_email != ?
							AND u_email = ?
							AND
								u_status != "Removed"';

				// if it is in db, return false, if it isn't, return true
				$this->set_message('check_email_unique', 'This e-mail is already registered.');
				return ($this->_CI->db->query($sql, array($row['u_email'], $email))->row_array() ? FALSE : TRUE);
			}
		}
		else
		{
			// check to see if the email they specified is not already in db
			$sql = 'SELECT
						u_id
					FROM
						user
					WHERE
						u_email = ?
					AND
						u_status != "Removed"';

			// if it is in db, return false, if it isn't, return true
			$this->set_message('check_email_unique', 'This e-mail is already registered.');
			return ($this->_CI->db->query($sql, $email)->row_array() ? FALSE : TRUE);
		}
	}


	// checks to see whether a given username is unique to the db
	public function check_username_unique($username, $u_id = FALSE)
	{
		// check to see if the username they specified is not already in db
		$sql = 'SELECT
					u_id
				FROM
					user
				WHERE
					u_uname = ?';
		if (@$u_id)
		{
			$sql .= ' AND u_id != '. (int)$u_id;
		}

		$query = $this->_CI->db->query($sql, $username);

		return ($query->num_rows() > 0) ? FALSE : TRUE;
	}


	// checks to see if password provided by user matches their current password
	public function check_current_password($password, $user_id)
	{
		$sql = 'SELECT
					u_id
				FROM
					user
				WHERE
					u_id = "' . (int)$user_id . '"
					AND u_pword = ?
					AND u_status = "Active";';

		$password = md5(sha1($this->_CI->config->item('encryption_key') . $password));

		return ($this->_CI->db->query($sql, $password)->row_array() ? true : false);
	}

}
