<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


// Include the phpass library for secure password hashing
require_once(APPPATH . '/third_party/phpass.php');


class Auth {


	protected $_CI;

	private $_phpass = NULL;		// store reference to phpass library
	private $_phpass_iteration_count = 8;		// Hash iteration count
	private $_phpass_portable = FALSE;		// Use portable hashes? Should be true when using PHP <= 5.2

	private $_hash_algorithm = 'sha256';		// Hashing algorithm to use


	function __construct()
	{
		$this->_CI =& get_instance();
	}




	/**
	 * Function to check that a user is logged in. If not, go to log in page.
	 */
	function check_logged_in()
	{
		// User must be logged in - ANY role is OK.
		if ( ! $this->_CI->session->userdata('u_id'))
		{
			// No user ID in session - redirect to login page
			$this->_CI->flash->set('error', 'You need to login to use this part of the website.', TRUE);
			$this->_CI->session->set_userdata('login_redirect', $this->_CI->uri->uri_string());
			return redirect('home/login');
		}

		return TRUE;
	}




	public function is_logged_in()
	{
		return ($this->_CI->session->userdata('u_id'));
	}




	/**
	 * Function to call to ensure the logged in user has a specific role. Shows error if not.
	 *
	 * @param string|array $role		Role name to check. Should be User, Admin, Master Admin. Or array of those.
	 */
	function is_role($role = '')
	{
		// Get role of logged in user
		$user_role = $this->_CI->session->userdata('u_type');
		// Check if the user's role matches supplied value (single or multiple) to check
		return (is_array($role)) ? in_array($user_role, $role) : $user_role === $role;
	}

	/**
	 * Function to return the permissions of the logged in user.
	 * This checks the permissions in the session data, generall that is only updated when they login. More secure (but heavier load) would be to check the Db.
	 *
	 */
	public function get_permissions()
	{
		$permissions = $this->_CI->session->userdata('permissions');

		return $permissions;
	}

	/**
	 * Checks an individual permission and if the logged in user has it granted.
	 * This checks the permissions in the session data, generall that is only updated when they login. More secure (but heavier load) would be to check the Db.
	 *
	 * @param $permission_key	The pi_key that the permission is referenced by (see permission_item table)
	 * @param $level	Optional. The level of access desired. Defaults to 'any'. Other options are 'all', 'bg', or 's'.
	 * @param $level_id	Optional. $level must also be specified. Checks the user is in the bg/supplier matching $level_id.
	 */
	public function is_allowed_to($permission_key, $level='any', $level_id=null)
	{
		//echo '<br />key:'. $permission_key .', level:'. $level .', id:'. $level_id .'.';
		$permissions = $this->_CI->session->userdata('permissions');

		if (isset($permissions['do_anything']['all']) && $permissions['do_anything']['all'] == 1)
		{
			return TRUE; //you so powerful
		}
		elseif (($level == 'bg' || $level == 's') && isset($level_id))
		{
			//you want to know if it's this bg/supplier that they can access
			if (isset($permissions[$permission_key][$level]) && $level_id == $permissions[$permission_key][$level])
			{
				//they are in the right bg or supplier
				return TRUE;
			}
			else
			{
				//they are not in the right supplier
				return FALSE;
			}
		}
		elseif (isset($permissions[$permission_key][$level]))
		{
			if ( $level != 'any' && $level != 'all')
			{
				return TRUE; //yes, you have permission to access a specific supplier or bg (dont care which)
			}
			elseif ( $permissions[$permission_key][$level] == 1)
			{
				return TRUE; //you can access some or all, go for it
			}
			else
			{
				return FALSE; //go home already
			}
		}
		else
		{
			return FALSE;
		}
		return FALSE; //may have givven bad number of parameters.
	}




	function restrict_role($role = '')
	{
		if ( ! $this->is_role($role))
		{
			$this->_CI->logger->add('access_denied');
			show_error('Sorry, your account does not have the correct access rights for this page.');
		}
	}




	/**
	 * Check if a user will be authenticated with provided email & password combination.
	 *
	 * On success, the user's last login time will be updated and the session will be created.
	 *
	 * @param string $email		Email address of user
	 * @param string $password		Plain-text password that user entered
	 * @return mixed		Array of user details on success, FALSE on auth failure
	 */
	public function login($email = '', $password = '')
	{
		$this->_CI->load->model('users_model');

		// Get the user details
		$user = $this->_CI->users_model->get_for_login($email);

		if ($user && $this->check_password($password, $user['u_pword']))
		{
			// Login successful!

			log_message('debug', 'Auth: login(): Successful password validation for ' . $email);

			// Update their last login time
			$this->_CI->users_model->set_last_login($user['u_id']);

			unset($user['u_pword']);

			//get permission data
			$this->_CI->load->model('permissions_model');
			$user['permissions'] = $this->_CI->permissions_model->get_user_permissions($user);

			// Create user session
			$this->create_session($user);

			return $user;
		}

		log_message('debug', 'Auth: login(): Failed password check for ' . $email);

		// Login failed :'(
		return FALSE;
	}




	public function create_session($user = array())
	{
		unset($user['u_password']);
		$this->_CI->session->set_userdata($user);
	}




	/**
	 * Log a user out and destory the session data
	 *
	 * @return bool
	 */
	public function logout()
	{
		return $this->_CI->session->sess_destroy();
	}




	/**
	 * Publicly-accessible function for hashing a supplied plaintext password
	 *
	 * @param string $password		Plain text password
	 * @return string		Hashed password
	 */
	public function hash_password($password = '')
	{
		$this->_init_phpass();
		return $this->_phpass->HashPassword($password);
	}




	/**
	 * Check if a given plaintext password matches the (expected) hash
	 */
	public function check_password($password = '', $stored_hash = '')
	{
		return (md5(sha1($this->_CI->config->item('encryption_key') . $password)) == $stored_hash);

		$this->_init_phpass();
		return $this->_phpass->CheckPassword($password, $stored_hash);
	}




	/**
	 * Function to initialise phpass when required by the hashing functions
	 */
	private function _init_phpass()
	{
		if ($this->_phpass === NULL)
		{
			$this->_phpass = new PasswordHash($this->_phpass_iteration_count, $this->_phpass_portable);
		}
	}




}
