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

class Members extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}


	/*
	 * What goes here?
	 */
	public function index()
	{
		$this->auth->check_logged_in();
		$this->layout->set_title('Members')->set_breadcrumb('Members');
		$this->load->vars($this->data);
		$this->view = 'members/index';
	}

	/*
	 * Allow users to edit their account
	 */
	public function account()
	{
		$this->auth->check_logged_in();
		$this->load->model('users_model');

		if ($this->input->post())
		{
			// load form validation library
			$this->load->library('form_validation');

			// set rules
			$this->form_validation
								  ->set_rules('u_title', '', 'strip_tags|max_length[8]|ucfirst')
								  ->set_rules('u_fname', '', 'required|strip_tags|ucwords')
								  ->set_rules('u_sname', '', 'required|strip_tags|ucwords')
								  ;
			if (@$this->input->post('u_email_confirm'))
			{
				$this->form_validation
								  ->set_rules('u_email', '', 'required|valid_email|check_email_unique['.$this->session->userdata('u_id').']')
								  ->set_rules('u_email_confirm', '', 'required|matches[u_email]');
			}
			if (@$this->input->post('u_pword'))
			{
				$this->form_validation
								  ->set_rules('u_pword', '', 'required|password_restrict')
								  ->set_rules('u_password_confirm', '', 'required|matches[u_pword]');
			}

			// if form validates
			if ($this->form_validation->run() == true)
			{
				$result = $this->users_model->update_user($this->session->userdata('u_id'));
				if (@$result)
				{
					$this->session->set_userdata($this->users_model->get_user( $this->session->userdata('u_id') )); //update with changes
					$this->flash->set('action', 'Your details were updated.', TRUE);
					return redirect(site_url().'members/account');
				}
				else
				{
					$this->flash->set('error', 'Details not added/updated.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
				return redirect(current_url());
			}
		}//form sent

		$this->data['user'] = $this->session->all_userdata(); //$this->users_model->get_user( $this->session->userdata('u_id') );

		$this->layout->set_title('Account')->set_breadcrumb('Account');
		$this->layout->set_js(array('views/members/account', 'plugins/jquery.validate', 'plugins/pStrength.jquery'));
		$this->load->vars($this->data);
		$this->view = 'members/account';
	}

	/*
	 * A page for users to view details about their group, and edit it if they are the group advocate.
	 */
	public function group()
	{
		$this->auth->check_logged_in();
		$this->load->model('groups_model');
		if ($this->session->userdata('u_advocate') == 1 && $this->session->userdata('bg_status') == 'New')
		{
			$this->data['group'] = $this->groups_model->get_group($this->session->userdata('bg_id'));
			if ($this->input->post())
			{
				//send data, on success set flashdata and redirect to current url
				$this->load->library('form_validation');
				// set rules
				$this->form_validation
									  ->set_rules('bg_name', '', 'required|strip_tags')
									  ->set_rules('bg_addr_line1', '', 'required|strip_tags')
									  ->set_rules('bg_addr_line2', '', 'strip_tags|ucfirst')
									  ->set_rules('bg_addr_city', '', 'required|strip_tags')
									  ->set_rules('bg_addr_pcode', '', 'required|max_length[9]|strip_tags')
									  ->set_rules('bg_addr_note', '', 'strip_tags')
									  ;
				if ($this->form_validation->run() == true)
				{
					$data = $this->input->post();
					$data['bg_status'] = 'Active';
					$success = $this->groups_model->update_group($this->session->userdata('bg_id'), $data);
					if ($success == true)
					{
						$this->session->set_userdata(array('bg_status' => 'Active'));
						$this->flash->set('success', 'Buying group details were updated..', TRUE);
						return redirect(current_url());
					}
					else
					{
						$this->flash->set('error', 'A problem occured, please try again.', TRUE);
						return redirect(current_url());
					}
				}
				else
				{
					$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
					return redirect(current_url());
				}
			}
			//we need them to provide some data about the buying group
			$this->layout->set_title('Buying Group')->set_breadcrumb('Buying Group');
			$this->layout->set_js('views/members/group-edit');
			$this->load->vars($this->data);
			$this->view = 'members/group-edit';
		}
		else
		{
			//just show them some info about the group
			$this->data['group'] = $this->groups_model->get_group($this->session->userdata('bg_id'));

			$this->layout->set_title('Buying Group')->set_breadcrumb('Buying Group');
			$this->layout->set_js('views/members/group-info');
			$this->load->vars($this->data);
			$this->view = 'members/group-info';
		}
	}

	/*
	 * Used by the validation js function when creating a user
	 */
	public function check_username_unique()
	{
		$this->load->library('form_validation'); // load form validation library
		$this->form_validation->set_rules('u_uname', '', 'check_username_unique'); // set rules

		$res = $this->form_validation->run();
		$text_result = ($res === TRUE) ? 'true' : 'false';
		die ($this->form_validation->run() ? 'true' : 'false'); // echo true/false json
	}


	/*
	 * Used by the validation js function when creating a user
	 */
	public function check_email_unique()
	{
		$this->load->library('form_validation'); // load form validation library

		$this->form_validation->set_rules('u_email', '', 'check_email_unique'); // set rules

		die ($this->form_validation->run() ? 'true' : 'false'); // echo true/false json
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
