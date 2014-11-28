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

class Users extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		if ( !$this->auth->is_allowed_to('view_members', 'any') && !$this->auth->is_allowed_to('manage_members', 'any'))
		{
			show_error('You do not have permission to view this part of the website.');
		}
	}

	public function index($page=0)
	{
		$params = array(

		);
		$params = array_merge($params, $_GET);

		$this->load->model('users_model');

		// load pagination library
		$this->load->library('pagination');
		$config['base_url'] = '/admin/users/index/';
		$config['total_rows'] = $this->users_model->get_total_users($params);
		$config['per_page'] = (@$_GET['pp'] ? $_GET['pp'] : $_GET['pp'] = 25);
		$config['uri_segment'] = 4;
		$config['suffix'] = '?' . @http_build_query($this->input->get());
		$this->pagination->initialize($config);

		$params['start'] = $page;
		$params['limit'] = $config['per_page'];

		//permission override?
		if ( !$this->auth->is_allowed_to('view_members', 'all') && !$this->auth->is_allowed_to('manage_members', 'all') )
		{
			if ($this->auth->is_allowed_to('view_members', 'bg', $this->session->userdata('u_bg_id')) || $this->auth->is_allowed_to('manage_members', 'bg', $this->session->userdata('u_bg_id')))
			{
				$params['u_bg_id'] = $this->session->userdata('u_bg_id');
			}
			else
			{
				show_error('You do not have permission to view this part of the website.');
			}
		}

		// get questions
		$this->data['users'] = $this->users_model->get_users($params);

		//get datasets (to create list to search on)
		$this->load->config('datasets');
		$this->data['user_fields'] = $this->config->item('user');
		$this->load->model('groups_model');
		$this->data['user_groups'] = $this->groups_model->get_groups_list();
		$this->load->model('permissions_model');
		$this->data['user_fields']['permission_groups'] = $this->permissions_model->get_groups_list();

		// set total string
		$this->data['total'] = ($this->data['users'] ? 'Results ' . ($page + 1) . ' - ' . ($page + count($this->data['users'])) . ' of ' . $config['total_rows'] . '.' : '0 results');

		// set sort string for sorting links
		$this->data['sort'] = '&amp;sort=' . (@$_GET['sort'] == 'asc' ? 'desc' : 'asc') . '&amp;pp=' . $_GET['pp'] . '&amp;date_from=' . @$_GET['date_from'];

		$this->data['pp'] = array('10', '20', '50', '100', '200');


		$this->layout->set_title('Manage Members and Staff')->set_breadcrumb('Manage Members and Staff');
		//$this->layout->set_js('views/admin/users/index');
		$this->load->vars($this->data);
		$this->view = 'admin/users/index';
	}

	public function set($u_id = null, $action=null) {

		if ( !$this->auth->is_allowed_to('manage_members', 'any'))
		{
			show_error('You do not have permission to view this part of the website.');
		}

		$this->load->model('users_model');

		if (@$u_id)
		{
			//attempt to get user
			$this->data['user'] = $this->users_model->get_user($u_id);
			if (!$this->data['user'])
			{
				show_404();
			}
			$title = 'Edit';
		}
		else
		{
			$title = 'Add';
		}

		if (isset($this->data['user']) && $this->data['user']['u_type'] == 'Admin')
		{
			$title .= ' Staff';
		}
		else
		{
			$title .= ' Member';
		}
		$this->data['title'] = $title;


		//permission override?
		if ( !$this->auth->is_allowed_to('view_members', 'all') && !$this->auth->is_allowed_to('manage_members', 'all') )
		{
			if (!$this->auth->is_allowed_to('view_members', 'bg', $this->data['user']['u_bg_id']) && !$this->auth->is_allowed_to('manage_members', 'bg', $this->data['user']['u_bg_id']))
			{
				show_error('You do not have permission to view details of this member.');
			}
		}

		//did we want to delete the user?
		$this->data['action_confirm'] = $action;
		if ($action == 'delete' && @$this->input->post('action-confirm') == 'delete')
		{
			if ( !$this->auth->is_allowed_to('manage_members', 'all') && !$this->auth->is_allowed_to('manage_members', 'bg', $this->data['user']['u_bg_id']) )
			{
				show_error('You do not have permission to edit details of this member.');
			}
			$result = $this->users_model->delete_user($u_id);
			if (@$result)
			{
				$this->flash->set('action', 'User was deleted.', TRUE);
				return redirect(site_url().'admin/users');
			}
			else
			{
				$this->flash->set('action', 'Error occured when deleting the user.', TRUE);
				return redirect(site_url().'admin/users');
			}
		}

		if ($this->input->post())
		{
			if ( !$this->auth->is_allowed_to('manage_members', 'all') && !$this->auth->is_allowed_to('manage_members', 'bg', $this->data['user']['u_bg_id']) )
			{
				show_error('You do not have permission to edit details of this member.');
			}
			// load form validation library
			$this->load->library('form_validation');

			// set rules
			$this->form_validation
								  ->set_rules('u_title', '', 'strip_tags|max_length[8]|ucfirst')
								  ->set_rules('u_fname', '', 'strip_tags|ucwords')
								  ->set_rules('u_sname', '', 'strip_tags|ucwords')
								  ->set_rules('u_bg_id', '', 'required|integer')
								  ->set_rules('u_type', '', 'required|strip_tags')
								  ;
			if (!$u_id)
			{
				//more rules!
				$this->form_validation->set_rules('u_email', '', 'required|valid_email|check_email_unique')
								  ->set_rules('u_email_confirm', '', 'required|matches[u_email]')
								  ->set_rules('u_pword', '', 'required|password_restrict')
								  ->set_rules('u_password_confirm', '', 'required|matches[u_pword]')
								  ;
			}

			// if form validates
			if ($this->form_validation->run() == true)
			{
				$result = $this->users_model->update_user($u_id);
				if (@$result)
				{
					$this->flash->set('action', 'User added/updated.', TRUE);
					return redirect(site_url().'admin/users');
				}
				else
				{
					$this->flash->set('error', 'User not added/updated.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
				//redirect(current_url());
			}
		}

		$this->load->config('datasets');
		$this->data['user_fields'] = $this->config->item('user');
		$this->load->model('permissions_model');
		$this->data['user_fields']['permission_groups'] = $this->permissions_model->get_groups_list();
		$this->load->model('suppliers_model');
		$this->data['user_fields']['suppliers'] = $this->suppliers_model->get_suppliers();
		$this->load->model('groups_model');
		$this->data['user_fields']['groups'] = $this->groups_model->get_groups_list();

		//info on logged-in user's permissions, for the view
		$this->data['loggedin_user_can']['manage_member_permissions'] = $this->auth->is_allowed_to('manage_member_permissions', 'all');

		$this->layout->set_title('Manage Members and Staff')->set_breadcrumb('Manage Members and Staff', 'admin/users');
		$this->layout->set_title($title)->set_breadcrumb($title);
		$this->layout->set_js(array('views/admin/users/set', 'plugins/jquery.validate'));
		$this->load->vars($this->data);
		$this->view = 'admin/users/set';
	}


	public function orders($u_id=null)
	{
		if ( !$this->auth->is_allowed_to('manage_members', 'all') )
		{
			show_error('You do not have permission to view this part of the website.');
		}
		if (!$u_id)
		{
			show_404();
		}

		$this->load->model('users_model');
		$this->load->model('order_model');
		$orders_filter = array( 'oi_u_id' => $u_id,
								'oi_status' => 'all'
								);
		$this->data['orders'] = $this->order_model->get_customer_orders( $orders_filter );
		$this->data['customer'] = $this->users_model->get_user( $u_id );

		if (!$this->data['customer'])
		{
			show_404();
		}

		if (@$this->input->post())
		{
			if ($this->input->post('oi_new_status') == 'Confirmed')
			{
				if ($this->input->post('oi_id') == 'all')
				{
					$result = $this->order_model->confirm_stock($u_id);
					if (@$result)
					{
						$this->flash->set('action', 'All items updated, please check below that they were all confirmed.', TRUE);
						return redirect(current_url());
					}
				}
				else
				{
					$result = $this->order_model->confirm_stock($u_id, $this->input->post('oi_id'));
					if (@$result)
					{
						$this->flash->set('action', 'Item was updated.', TRUE);
						return redirect(current_url());
					}
				}
			}
			elseif ($this->input->post('oi_new_status') == 'Cancelled' || $this->input->post('oi_new_status') == 'Rejected')
			{
				$result = $this->order_model->update_status($this->input->post('oi_id'), $this->input->post('oi_new_status'));
				if (@$result)
				{
					$this->flash->set('action', 'Item '. $this->input->post('oi_new_status') .'.', TRUE);
					return redirect(current_url());
				}
			}
			elseif ($this->input->post('p_id') != null)
			{
				//order produce on behalf of a member
				$this->load->model('products_model');
				$product = $this->products_model->get_product_full($this->input->post('p_id'), null);
				if (!$product)
				{
					$this->flash->set('error', 'Item could not found for ordering.', TRUE);
					return redirect(current_url());
				}
				elseif ($this->input->post('order_date') == null || $this->input->post('oi_quantity') == null)
				{
					$this->flash->set('error', 'Please enter all the details for the order.', TRUE);
					return redirect(current_url());
				}
				$details = array(
					'u_id' => $u_id,
					's_id' => $product['supplier_id'],
					'p_id' => $this->input->post('p_id'),
					'unit_price' => $product['p_price'],
					'unit_cost' => $product['p_cost'],
					'source_id' => $this->session->userdata('u_id'),
					'source_type' => 'admin',
					'status' => 'Confirmed'
				);
				$this->load->helper('dates_helper');
				$full_order = array(
					other_english_date_to_mysql_date($this->input->post('order_date')) => $this->input->post('oi_quantity')
				);
				$this->load->model('order_model');
				$results = $this->order_model->add_items($details, $full_order);
				if ($results > 0)
				{
					$this->flash->set('action', 'Item ordered.', TRUE);
					return redirect(current_url());
				}
			}
			// some form sent, but we didn't like it
			$this->flash->set('error', 'Items could not be updated.', TRUE);
			return redirect(current_url());
		}

		$this->layout->set_title('Orders')->set_breadcrumb('Orders', 'admin/users');
		$this->layout->set_title($this->data['customer']['u_fullname'])->set_breadcrumb($this->data['customer']['u_fullname']);
		$this->layout->set_js(array('views/admin/users/orders'));
		$this->load->vars($this->data);
		$this->view = 'admin/users/orders';
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
