<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handle user accounts within the system
 *
 * @package Fair-Food Carlisle
 * @subpackage Models
 * @author GM
 *
 * Fair-Food Carlisle <http://fairfoodcarlisle.co.uk/>
 * Copyright (c) Cloud Data Service Ltd <http://clouddataservice.co.uk/>
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt. It is also available
 * through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 */


class Users_model extends MY_Model
{


	protected $_table = 'users';
	protected $_primary = 'u_id';


	public function __construct()
	{
		parent::__construct();
	}




	/**
	 * Get a user with valid login details
	 *
	 * @param string $email		Email address of user to login
	 * @return mixed		DB row array on success, FALSE on failure
	 */
	public function get_for_login($email = '')
	{
		$sql = 'SELECT
					user.*,
					bg_id,
					bg_code,
					bg_name,
					bg_status,
					bg_deliveryday
				FROM
					user
				LEFT JOIN
					buyinggroup ON user.u_bg_id = buyinggroup.bg_id
				WHERE
					u_email = ?
				AND
					u_status = "Active"
				LIMIT 1';

		return $this->db->query($sql, array($email))->row_array();
	}




	/**
	 * Update a user setting their last login date/time to now
	 *
	 * @param int $u_id		ID of user to update
	 * @return bool
	 */
	public function set_last_login($u_id = 0)
	{
		$sql = 'UPDATE user SET u_datetime_last_login = ? WHERE u_id = ? LIMIT 1';
		$datetime = date('Y-m-d H:i:s');
		return $this->db->query($sql, array($datetime, $u_id));
	}

	/**
	 * Get a single user, and many details about them
	 *
	 * @author GM
	 * @param int $u_id		the user id requested
	 * @return mixed		DB result row on success, FALSE on failure
	 */
	public function get_user($u_id)
	{
		$sql = 'SELECT
					u_id,
					u_email,
					IF(u_gc_preauth_id IS NULL, "No", "Yes") AS u_gc_setup,
					u_title,
					u_fname,
					u_sname,
					CONCAT(u_title, " ", u_fname, " ", u_sname) AS "u_fullname",
					u_telephone,
					u_delivery_type,
					u_addr_line1,
					u_addr_line2,
					u_addr_city,
					u_addr_pcode,
					u_type,
					u_pg_id,
					u_status,
					u_bg_id,
					bg_code,
					bg_name,
					u_s_id
				FROM
					user
				LEFT JOIN
					buyinggroup ON user.u_bg_id = buyinggroup.bg_id
				WHERE u_id = '. (int)$u_id.'
				LIMIT 1
				';
		return $this->db->query($sql)->row_array();
	}

	/**
	 *
	 * @return mixed	u_id on success, false on failure
	 */
	public function update_user($u_id=null, $data=null)
	{
		if (!$data)
		{
			$data = $this->input->post();
		}

		if (!isset($data['u_status']))
		{
			$data['u_status'] = 'Active';
		}

		if (!$u_id)
		{
			$sql = 'INSERT
					INTO
						user
						(u_email, u_pword, u_type, u_status)
					VALUES
						(?, ?, ?, ?)';
			$password = md5(sha1($this->config->item('encryption_key') . $data['u_pword']));
			$sql_data = array(
						 $data['u_email'],
						 $password,
						 $data['u_type'],
						 $data['u_status']
						  );
			$result = $this->db->query($sql, $sql_data);
			if (!$result)
			{
				return false;
			}
			else
			{
				$u_id = $this->db->insert_id();
			}
		}
		//should have u_id by now, update with any fields we missed. Or update if the requested user
		if ($u_id)
		{
			$sql = 'UPDATE
						user
					SET
						u_title = ?,
						u_fname = ?,
						u_sname = '. $this->db->escape($data['u_sname']);
			if (@$data['u_type'])
			{
				$sql .= ', u_type = '. $this->db->escape($data['u_type']);
			}
			if (@$data['u_status'])
			{
				$sql .= ', u_status = '. $this->db->escape($data['u_status']);
			}
			if (@$data['u_email'])
			{
				$sql .= ', u_email = '. $this->db->escape($data['u_email']);
			}
			if (@$data['u_pword'])
			{
				$password = md5(sha1($this->config->item('encryption_key') . $data['u_pword']));
				$sql .= ', u_pword = '. $this->db->escape($password);
				$sql .= ', u_pword_change = 0';
			}
			if (@$data['u_telephone'])
			{
				$sql .= ', u_telephone = '. $this->db->escape($data['u_telephone']);
			}
			if (@$data['u_pg_id'])
			{
				$sql .= ', u_pg_id = "'. (int)$data['u_pg_id'] .'"';
			}
			if (@$data['u_bg_id'])
			{
				$sql .= ', u_bg_id = "'. (int)$data['u_bg_id'] .'"';
			}
			if (@$data['u_s_id'])
			{
				$sql .= ', u_s_id = "'. (int)$data['u_s_id'] .'"';
			}
			if (element('u_delivery_type', $data) != null)
			{
				$sql .= ', u_delivery_type = "'. $data['u_delivery_type'] .'"';
			}
			if (element('u_addr_line1', $data) != null)
			{
				$sql .= ', u_addr_line1 = "'. $data['u_addr_line1'] .'"';
			}
			if (element('u_addr_line2', $data) != null)
			{
				$sql .= ', u_addr_line2 = "'. $data['u_addr_line2'] .'"';
			}
			if (element('u_addr_city', $data) != null)
			{
				$sql .= ', u_addr_city = "'. $data['u_addr_city'] .'"';
			}
			if (element('u_addr_pcode', $data) != null)
			{
				$sql .= ', u_addr_pcode = "'. $data['u_addr_pcode'] .'"';
			}
			$sql .= '
					WHERE
						u_id = ?';
			$sql_data = array(
						 $data['u_title'],
						 $data['u_fname'],
						 $u_id
						  );
		}

		//run query
		$result = $this->db->query($sql, $sql_data);

		//all done
		if ($result && $u_id)
		{
			return $u_id;
		}
		else if ($result)
		{
			return $this->db->insert_id();
		}
		else
		{
			return false;
		}

	}

	/**
	 * Sets a users' status to Removed, so they should not be displayed in lists or be able to login.
	 */
	public function delete_user($u_id)
	{
			$sql = 'UPDATE user
					SET u_status = "Removed"
					WHERE u_id = ?
					LIMIT 1;';

			$result = $this->db->query($sql, array((int)$u_id));

			if (@$result)
			{
				$sql = 'UPDATE orderitem
						SET oi_status = "Cancelled"
						WHERE oi_u_id = ?;';

				$result = $this->db->query($sql, array((int)$u_id));

				if (@$result)
				{
					return true;
				}
			}
			else
			{
				return false;
			}
	}

	/**
	 * Creates a new user, attach them to a buying group if provided, and send them with an autogenerated password.
	 *
	 * @author GM
	 * @param array $data	relevant info
	 * @return mixed		new user id on success, false on failure
	 */
	public function create_user($data)
	{
		//create a new password
		$password = $this->generate_password($data['u_email']);
		$password_encrypted = md5(sha1($this->config->item('encryption_key') . $password));
		//insert them in
		$sql = 'INSERT
				INTO
					user
					(u_email, u_pword, u_pword_change, u_bg_id, u_advocate, u_title, u_fname, u_sname, u_type, u_status)
				VALUES
					(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$sql_data = array(
						  $data['u_email'],
						  $password_encrypted,
						  1,
						  $data['bg_id'],
						  1,
						  $data['u_title'],
						  $data['u_fname'],
						  $data['u_sname'],
						  'Member',
						  'Active'
						  );
		$result = $this->db->query($sql, $sql_data);
		if (!$result)
		{
			return false;
		}
		else
		{
			$u_id = $this->db->insert_id();
			//email them about it
			$subject = config_item('site_name') .', setup the buying group';
			$message = '<p>Hello '. $data['u_title'] .' '. $data['u_fname'] .' '. $data['u_sname'] .',</p>';
			$message .= '<p>The buying group for '. $data['bg_name'] .', has been created on the website. Please login using the details below.';
			$message .= '<br /><br /><strong>Username:</strong> '. $data['u_email'];
				$message .= '<br /><strong>Password:</strong> '. $password;
				$message .= '<br /><strong>Website:</strong> <a href="'. site_url('home/login') .'">'. site_url('home/login') .'</a></p>';
			$message .= '<p>Thank you, <br /> '. config_item('site_name') .'</p>';

			$eq[] = array('eq_email' => $data['u_email'],
						  'eq_subject' => $subject,
						  'eq_body' => $message);
			// load emails queue model
			$this->load->model('emails_queue_model');
			$this->emails_queue_model->set_queue($eq);

			return $u_id;
		}
	}

	/**
	 * Get a list of users in the system
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_users($params=null)
	{
		if ( !$params) $params = array('order' => 'qa_id');
		if ( ! in_array(@$params['order'], array('u_email', 'u_type')) ) $params['order'] = 'u_id';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'desc';

		$sql = 'SELECT
					u_id,
					u_email,
					IF(u_gc_preauth_id IS NULL, "No", "Yes") AS u_gc_setup,
					u_title,
					u_fname,
					u_sname,
					u_type,
					u_status
				FROM
					user';

		$sql .= ' WHERE 1 = 1 ';

		if (@$params['u_type'])
			$sql .= ' AND u_type = ' . $this->db->escape($params['u_type']) . ' ';

		if (@$params['u_status'])
		{
			$sql .= ' AND u_status = ' . $this->db->escape($params['u_status']) . ' ';
		} else {
			$sql .= ' AND u_status = "Active" ';
		}
		if (isset($params['u_name']) && $params['u_name'] != "") {
			if (isset($params['u_s_type']) && $params['u_s_type'] != "") {
				switch ($params['u_s_type']) {
					case 'exact':
						$sql .= ' AND UCASE(concat_ws(" ", u_fname, u_sname)) = ' . strtoupper($this->db->escape($params['u_name'])) . ' ';
						break;
					case 'like':
						$sql .= ' AND UCASE(concat_ws(" ", u_fname, u_sname)) LIKE ' . strtoupper($this->db->escape('%'.$params['u_name']. '%')) . ' ';
						break;
					default:
						$sql .= ' AND UCASE(concat_ws(" ", u_fname, u_sname)) = ' . strtoupper($this->db->escape($params['u_name'])) . ' ';
				}
			}
		}
		if (@$params['u_bg_id'])
			$sql .= ' AND u_bg_id = ' . $this->db->escape($params['u_bg_id']) . ' ';

		if (@$params['u_pg_id'])
			$sql .= ' AND u_pg_id = ' . $this->db->escape($params['u_pg_id']) . ' ';

		// set order by clause
		$sql .= ' ORDER BY ' . $params['order'] . ' ' . $params['sort'] . ' ';

		// if a limit has been set
		if (@$params['limit'] != FALSE)
			$sql .= ' LIMIT ' . (int)$params['start'] . ', ' . (int)$params['limit'];

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get the total count of users, ignoring the limit in filter.
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return int	the number of users found
	*/
	public function get_total_users($params=null)
	{
		if ( !$params) $params = array('order' => 'qa_id');
		if ( ! in_array(@$params['order'], array('u_email', 'u_type')) ) $params['order'] = 'u_id';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'desc';

		$sql = 'SELECT
					COUNT(u_id) AS total
				FROM
					user';

		$sql .= ' WHERE 1 = 1 ';

		if (@$params['u_type'])
			$sql .= ' AND u_type = ' . $this->db->escape($params['u_type']) . ' ';

		if (@$params['u_status'])
		{
			$sql .= ' AND u_status = ' . $this->db->escape($params['u_status']) . ' ';
		}
		else
		{
			$sql .= ' AND u_status = "Active" ';
		}

		if (@$params['u_bg_id'])
			$sql .= ' AND u_bg_id = ' . $this->db->escape($params['u_bg_id']) . ' ';

		if (@$params['u_pg_id'])
			$sql .= ' AND u_pg_id = ' . $this->db->escape($params['u_pg_id']) . ' ';

		// set order by clause
		$sql .= ' ORDER BY ' . $params['order'] . ' ' . $params['sort'] . ' ';

		$result = $this->db->query($sql)->row_array();
		return $result['total'];
	}

	/**
	 * Change a users password to a new one, and e-mail them about it.
	 *
	 * @param u_id	the id of the user
	 * @param u_email	the e-mail address of the user
	 */
	public function reset_password($u_id, $u_email)
	{
		//create a new password
		$password = $this->generate_password($u_email);
		$password_encrypted = md5(sha1($this->config->item('encryption_key') . $password));
		//insert it in
		$sql = 'UPDATE
					user
				SET
					u_pword = "'. $password_encrypted .'",
					u_pword_change = 1
				WHERE
					u_id = '. (int)$u_id .'
				;';
		$result = $this->db->query($sql);
		if (!$result)
		{
			return false;
		}
		else
		{
			//email them about it
			$subject = config_item('site_name') .' password reset';
			$message = '<p>Hello,</p>';
			$message .= '<p>As requested your password has been changed. Your previous password will no longer work, however when you login you can set a more memorable password.';
			$message .= '<br /><br /><strong>E-mail:</strong> '. $u_email;
				$message .= '<br /><strong>Password:</strong> '. $password;
				$message .= '<br /><strong>Website:</strong> <a href="'. site_url('home/login') .'">'. site_url('home/login') .'</a></p>';

			$eq = array('eq_email' => $u_email,
						  'eq_subject' => $subject,
						  'eq_body' => $message);
			//skip the queue, send it now...
			$config['mailtype'] = 'html';
			$message = '<div style="background-color:#428B73; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333; padding:30px; padding-top:190px;">
							<div style="margin:0 auto; background-color:#fff; width:600px; padding:30px; padding-top:35px; position:relative;">
								<img src="' . site_url('img/logo.png') .'" style="position:absolute; top:-170px; left:-20px;" alt="'. $this->config->item('site_name') .' logo" />
								' . $eq['eq_body'] . '
								<p>Regards, ' . $this->config->item('site_name') . '</p>
							</div>
							<div style="margin:0 auto; padding-top:15px; width:600px; text-align:right;">
							</div>
						</div>';

			$this->load->library('email');
			// set email params
			$this->email->clear(TRUE)
						->initialize($config)
						->from($this->config->item('auto_email'), $this->config->item('site_name'))
						->to($eq['eq_email'])
						->subject($eq['eq_subject'])
						->message($message);

			// if email sends ok
			$this->email->send();

			// load emails queue model
			//$this->load->model('emails_queue_model');
			//$this->emails_queue_model->set_queue($eq);

			return $u_id;
		}
	}

	/**
	* Generate a password based on an email address
	*
	* current time + first part of email + length of full email
	*/
	private function generate_password($email = '')
	{
		$email = trim($email);
		$email_user = strtolower(substr($email, 0, strpos($email, '@')));
		$password = date('iH') . $email_user . strlen($email);
		return $password;
	}

}

/* End of file: ./application/models/users_model.php */
