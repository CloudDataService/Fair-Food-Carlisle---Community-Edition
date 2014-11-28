<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
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

class Home extends MY_Controller {


	public function __construct()
	{
		parent::__construct();
	}


	public function index()
	{
		$this->layout->set_title('Home')->set_breadcrumb('Home');
		$this->view = 'default/home/index';
		$this->layout->set_js(array('views/stock-check', 'views/default/home/index'));
		$this->layout->set_title('Home');
		$this->load->vars($this->data);
	}

	/**
	 * If they have forgotten their password, we can send a new one
	 */
	public function forgotten_password()
	{
		if ($this->input->post())
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('email', 'Email address', 'required|trim');

			if ($this->form_validation->run())
			{
				$this->load->model('users_model');
				$user = $this->users_model->get_for_login($this->input->post('email'));
				if ($user)
				{
					//reset password
					$result = $this->users_model->reset_password($user['u_id'], $user['u_email']);
					if ($result)
					{
						$this->flash->set('error', 'An e-mail was sent with your new password.',  TRUE);
						return redirect(site_url('home/login'));
					}
				}
				$this->flash->set('error', 'Password could not be changed.',  TRUE);
				return redirect(current_url());
			}
		}

		$this->layout->set_title('Forgotten Password')->set_breadcrumb('Forgotten Password');
		$this->view = 'default/home/forgotten-password';
		$this->load->vars($this->data);
	}

	public function login()
	{
		// List of controllers matching user roles
		$controllers = config_item('roles_controllers');

		if ($this->auth->is_logged_in())
		{
			// Keep the flash data if there was any
			$this->session->keep_flashdata('flash');
			// Get the front controller to redirect to based on user role
			return redirect($controllers[$this->session->userdata('u_role')]);
		}

		if ($this->input->post())
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('email', 'Email address', 'required|trim')
								  ->set_rules('password', 'Password', 'required');

			if ($this->form_validation->run())
			{
				// Form fields OK - authenticate

				if ($user = $this->auth->login($this->input->post('email'), $this->input->post('password')))
				{
					log_message('debug', 'home/login: Successful login for ' . $this->input->post('email'));

					// Set flash message to be shown on redirect
					$this->flash->set('success', 'You are now logged in!',  TRUE);

					// Record the login activity
					$this->logger->add('login');

					// Home controller for user role
					$uri = $controllers[$user['u_type']];

					// Get URI from session userdata if present, if landing on another page
					if ($this->session->userdata('login_redirect'))
					{
						$uri = $this->session->userdata('login_redirect');
						$this->session->unset_userdata('login_redirect');
					}

					//if their password needs changing, set flashdata & redirect them
					if ($this->session->userdata('u_pword_change') == 1)
					{
						$this->flash->set('success', 'Your password needs to be changed.',  TRUE);
						$uri = site_url('members/account');
					}

					//if they are an advocate of a new buying group, they need to set some details of that
					if ($this->session->userdata('u_advocate') == 1 && $this->session->userdata('bg_status') == 'New')
					{
						$uri = site_url('members/group');
					}

					return redirect($uri);
				}
				else
				{
					// The login failed - bad email/password
					log_message('debug', 'home/login: Failed login for ' . $this->input->post('email') . '(email/password)');
					$this->flash->set('error', 'Incorrect email address or password', TRUE);
				}
			}
			else
			{
				// Form validation failed
				log_message('debug', 'home/login: Failed login for ' . $this->input->post('email') . '(form validation)');
				$this->flash->set('error', 'Incorrect email address or password', TRUE);
			}

			return redirect('home/login');
		}
		else
		{
			//do we have a redirect uri, or do we want to go back where we came from?
			if (!$this->session->userdata('login_redirect'))
			{
				if (substr($_SERVER['HTTP_REFERER'], 0, strlen(site_url())) == site_url())
				{
					$this->session->set_userdata('login_redirect', $_SERVER['HTTP_REFERER']);
				}
			}

			// If not POST request..
			if ($this->input->get('logged_out'))
			{
				$this->flash->set('success', 'You have been logged out.');
			}
		}

		$this->view = 'default/home/login';
	}




	public function logout()
	{
		$this->view = FALSE;
		$this->logger->add('logout');
		$this->auth->logout();
		return redirect('home/login?logged_out=1');
	}




	public function account()
	{
		if ( ! $this->auth->is_logged_in())
		{
			return redirect('home/login');
		}

		$this->layout->set_title('My Account');
		$this->view = 'default/home/account';
	}


	public function register()
	{
		$this->load->model('groups_model');
		$this->data['user'] = array();

		if ($this->input->post())
		{
			$data = $this->input->post();

			if (config_item('use_signup_code') == 1)
			{
				//is the code right?
				$bg_id = $this->groups_model->find_group($data['bg_code']);
				if (!$bg_id)
				{
						$this->flash->set('error', 'That is not a valid buying group code.', TRUE);
						return redirect(current_url());
				}
				else
				{
					$data['u_bg_id'] = $bg_id;
				}
			}
			elseif ($data['u_bg_id'] == null && config_item('default_signup_group') == null)
			{
				//I don't think this is actually needed, as form_validation will catch it?
				$this->flash->set('error', 'You must select a buying group.', TRUE);
				return redirect(current_url());
			}
			$data['u_type'] = 'Member';
			$data['u_status'] = 'Active';

			// load form validation library
			$this->load->library('form_validation');
			// set rules
			$this->form_validation
								  ->set_rules('u_title', '', 'strip_tags|max_length[8]|ucfirst')
								  ->set_rules('u_fname', '', 'required|strip_tags|ucwords')
								  ->set_rules('u_sname', '', 'required|strip_tags|ucwords')
								  ->set_rules('u_email', '', 'required|valid_email|check_email_unique')
								  ->set_rules('u_email_confirm', '', 'required|matches[u_email]')
								  ->set_rules('u_pword', '', 'required|password_restrict')
								  ->set_rules('u_password_confirm', '', 'required|matches[u_pword]');

			if (config_item('default_signup_group') == null && config_item('use_signup_code') == 1)
			{
				//group needs to be specified, using the bg code
				$this->form_validation->set_rules('bg_code', '', 'required|strip_tags');
			}
			elseif (config_item('default_signup_group') == null)
			{
				//group needs to be specified using the drop down list
				$this->form_validation->set_rules('u_bg_id', '', 'required|integer');
			}
			else
			{
				//group was not specified, so the default will be used
				$data['u_bg_id'] = config_item('default_signup_group');
			}

			// if form validates
			if ($this->form_validation->run() == true)
			{
				$this->load->model('users_model');
				$result = $this->users_model->update_user(null, $data);
				if (@$result)
				{
					//they've signed up, now send an e-mail
					$subject = 'Welcome to '. config_item('site_name');
					$message = '<p>Hello '. $data['u_title'] .' '. $data['u_fname'] .' '. $data['u_sname'] .',</p>';
					$message .= '<p>Welcome to '. config_item('site_name') .'. You can now log in and start buying from local suppliers for weekly deliveries to '. $this->session->userdata('bg_name') .'.';
					$message .= '<br /><br />Simply go to <a href="'. site_url('home/login') .'">'. site_url('home/login') .'</a> and take a look at the produce.</p>';
					$message .= '<p>Thank you, <br /> '. config_item('site_name') .'</p>';

					$eq[] = array('eq_email' => $data['u_email'],
								  'eq_subject' => $subject,
								  'eq_body' => $message);
					// load emails queue model
					$this->load->model('emails_queue_model');
					$this->emails_queue_model->set_queue($eq);

					//all good now
					$this->flash->set('action', 'You are now registered, you can login to begin ordering produce..', TRUE);
					$this->session->set_userdata('login_redirect', 'products');
					return redirect(site_url('home/login'));
				}
				else
				{
					$this->flash->set('error', 'Account not created.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
				return redirect(current_url());
			}
		}//form sent

		if (config_item('use_signup_code') == 0)
		{
			$this->data['groups_list'] = $this->groups_model->get_active_groups_list();
		}

		$this->layout->set_title('Register')->set_breadcrumb('Register');
		$this->layout->set_js(array('views/default/home/register', 'plugins/jquery.validate','plugins/pStrength.jquery'));
		$this->load->vars($this->data);
		$this->view = 'default/home/register';
	}


	/**
	 * For registration validation, this is called by ajax
	 */
	public function check_group_code()
	{
		$bg_code = $this->input->post('bg_code');
		$this->load->model('groups_model');
		$bg_id = $this->groups_model->find_group($bg_code);
		if (!$bg_code || !$bg_id)
		{
			die('false');
		}
		else
		{
			die('true');
		}
	}


	/**
	 * For registration validation, this is called by ajax
	 */
	public function check_email_unique($email=null)
	{
		if ($email == null)
		{
			$email = $this->input->post('u_email');
		}
		// check to see if the email they specified is not already in db
		$sql = 'SELECT
					u_id
				FROM
					user
				WHERE
					u_email = ?
				AND
					u_status != "Removed"
					';
		$result = $this->db->query($sql, $email)->row_array();
		if (!$result)
		{
			die('true');
		}
		else
		{
			die('false');
		}
	}


	/**
	 * For registration validation, this is called by ajax
	 */
	public function check_password_valid()
	{
		$password = $this->input->post('u_pword');
		die ( ! preg_match('/^.{7,}$/', $password) ? 'false' : 'true');
	}

}

/* End of file ./application/controllers/home.php */
