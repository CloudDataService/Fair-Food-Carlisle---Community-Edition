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

class Groups extends Admin_Controller {


	public function __construct()
	{
		parent::__construct();

		if ( !$this->auth->is_allowed_to('view_buying_groups', 'all') )
		{
			show_error('You do not have permission to view this part of the website.');
		}
	}

	public function index($page=0)
	{
		$params = array(

		);
		$params = array_merge($params, $_GET);

		$this->load->model('groups_model');

		// load pagination library
		$this->load->library('pagination');
		$config['base_url'] = '/admin/groups/index/';
		$config['total_rows'] = $this->groups_model->get_total_groups($params);
		$config['per_page'] = (@$_GET['pp'] ? $_GET['pp'] : $_GET['pp'] = 25);
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);

		$params['start'] = $page;
		$params['limit'] = $config['per_page'];

		// get Bee Gees
		$this->data['groups'] = $this->groups_model->get_groups($params);

		// set total string
		$this->data['total'] = ($this->data['groups'] ? 'Results ' . ($page + 1) . ' - ' . ($page + count($this->data['groups'])) . ' of ' . $config['total_rows'] . '.' : '0 results');

		// set sort string for sorting links
		$this->data['sort'] = '&amp;sort=' . (@$_GET['sort'] == 'asc' ? 'desc' : 'asc') . '&amp;pp=' . $_GET['pp'] . '&amp;date_from=' . @$_GET['date_from'];

		$this->data['pp'] = array('10', '20', '50', '100', '200');


		$this->layout->set_title('Manage Buying Groups')->set_breadcrumb('Manage Buying Groups');
		$this->load->vars($this->data);
		$this->view = 'admin/groups/index';
	}

	public function create()
	{
		if ($this->input->post())
		{
			$this->load->library('form_validation');

			// set rules
			$this->form_validation
								  ->set_rules('u_title', '', 'strip_tags|max_length[8]|ucfirst')
								  ->set_rules('u_fname', '', 'required|strip_tags|ucfirst')
								  ->set_rules('u_sname', '', 'strip_tags|ucfirst')
								  ->set_rules('u_email', '', 'required|strip_tags')
								  ->set_rules('bg_name', '', 'required|strip_tags')
								  ->set_rules('bg_deliveryday', '', 'strip_tags')
								  ;
			if ($this->form_validation->run() == true)
			{
				$this->load->model(array('groups_model', 'users_model'));

				$data = $this->input->post();
				//add the group
				$data['bg_id'] = $this->groups_model->update_group(null, $data);
				if (!$data['bg_id'])
				{
					$this->flash->set('error', 'Group not added.', TRUE);
					return redirect(current_url());
				}

				//add the new user & email them
				$u_id = $this->users_model->create_user($data);
				if (!$u_id)
				{
					$this->flash->set('error', 'Group added.#, but error adding buying advocate.', TRUE);
					return redirect(current_url());
				}
				$this->flash->set('action', 'Buying group added, and advocate e-mailed.', TRUE);
				return redirect(site_url('admin/groups'));
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
			}
		}


		$this->layout->set_title('Manage Buying Groups')->set_breadcrumb('Manage Buying Groups');
		$this->layout->set_title('New Group')->set_breadcrumb('New Group');
		$this->load->vars($this->data);
		$this->view = 'admin/groups/new';
	}

	public function view($bg_id)
	{
		$this->load->model('groups_model');
		$this->data['group'] = $this->groups_model->get_group($bg_id);
		if (!$this->data['group'])
		{
			show_404();
		}

		$this->load->model('order_model');
		$this->data['orders'] = $this->order_model->get_group_orders($bg_id);

		$this->layout->set_title('Buying Groups')->set_breadcrumb('Buying Groups', 'admin/groups');
		$this->layout->set_title($this->data['group']['bg_name'])->set_breadcrumb($this->data['group']['bg_name'], 'admin/groups/view/'.$bg_id);
		$this->load->vars($this->data);
		$this->view = 'admin/groups/view';
	}

	public function set($bg_id = null)
	{
		$this->load->model('groups_model');

		if (@$bg_id)
		{
			//attempt to get user
			$this->data['group'] = $this->groups_model->get_group($bg_id);
			if (!$this->data['group'])
			{
				show_404();
			}
			$title = 'Edit Buying Group';
		}
		else
		{
			show_404();
		}

		if ($this->input->post())
		{
			// load form validation library
			$this->load->library('form_validation');

			// set rules
			$this->form_validation->set_rules('bg_name', '', 'required|strip_tags')
							  ->set_rules('bg_addr_line1', '', 'strip_tags')
							  ->set_rules('bg_addr_line2', '', 'strip_tags')
							  ->set_rules('bg_addr_city', '', 'strip_tags')
							  ->set_rules('bg_addr_pcode', '', 'strip_tags|max_length[9]')
							  ->set_rules('bg_addr_note', '', 'strip_tags')
							  ->set_rules('bg_deliveryday', '', 'required|strip_tags')
							  ->set_rules('bg_status', '', 'required|strip_tags')
							  ;

			// if form validates
			if ($this->form_validation->run() == true)
			{
				$result = $this->groups_model->update_group($bg_id, $this->input->post());
				if (@$result)
				{
					$this->flash->set('action', 'Group updated.', TRUE);
					return redirect(site_url().'/admin/groups');
				}
				else
				{
					$this->flash->set('error', 'Group not added/updated.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
			}
		}

		$this->layout->set_title('Manage Groups')->set_breadcrumb('Manage Groups', 'admin/groups');
		$this->layout->set_title($title)->set_breadcrumb($title);
		$this->layout->set_js(array('views/admin/groups/set', 'plugins/jquery.validate'));
		$this->load->vars($this->data);
		$this->view = 'admin/groups/set';
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
