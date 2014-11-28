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

class Bills extends Admin_Controller {


	public function __construct()
	{
		parent::__construct();

		if ( !$this->auth->is_allowed_to('view_member_bills', 'any') )
		{
			show_error('You do not have permission to view this part of the website.');
		}
	}



	public function index($page=0)
	{
		$params = array(
			//any defaults?
			'order' => 'bg_id',
			'sort' => 'desc'
		);
		$params = array_merge($params, $_GET); //get will override any defaults

		//permission override?
		if ( !$this->auth->is_allowed_to('view_member_bills', 'all') )
		{
			$params['bg_id'] = $this->session->userdata('u_bg_id');
		}

		$this->load->model('order_model');

		// load pagination library
		$this->load->library('pagination');
		$config['base_url'] = '/admin/bills/index/';
		$config['total_rows'] = $this->order_model->get_total_bills($params);
		$config['per_page'] = (@$_GET['pp'] ? $_GET['pp'] : $_GET['pp'] = 25);
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);

		$params['start'] = $page;
		$params['limit'] = $config['per_page'];

		// get Bills
		$this->data['bills'] = $this->order_model->get_bills($params);

		// set total string
		$this->data['total'] = ($this->data['bills'] ? 'Results ' . ($page + 1) . ' - ' . ($page + count($this->data['bills'])) . ' of ' . $config['total_rows'] . '.' : '0 results');

		// set sort string for sorting links
		$this->data['sort'] = '&amp;sort=' . (@$_GET['sort'] == 'asc' ? 'desc' : 'asc') . '&amp;pp=' . $_GET['pp'] . '&amp;date_from=' . @$_GET['date_from'];

		$this->data['pp'] = array('10', '20', '50', '100', '200');

		//Get some field options
		$this->data['bill_fields'] = config_item('bill_fields');
		$this->load->model('groups_model');
		$this->data['buying_groups'] = $this->groups_model->get_groups(array('bg_status' => 'Active'));

		//this helpful for knowing the truth that was sorted on (eg if permissions/defaults overrid the GET params
		$this->data['params'] = $params;

		$this->layout->set_title('View Bills')->set_breadcrumb('View Bills', 'admin/bills/index');
		//$this->layout->set_js('views/admin/users/index');
		$this->load->vars($this->data);
		$this->view = 'admin/bills/index';
	}

	public function view($b_id)
	{
		$this->load->model('order_model');
		$this->data['bill'] = $this->order_model->get_bill($b_id);
		if (!$this->data['bill'])
		{
			show_404();
		}

		//permission override?
		if ( !$this->auth->is_allowed_to('view_member_bills', 'bg', $this->data['bill']['u_bg_id']) )
		{
			show_error('You are not allowed to view this bill.');
		}

		if (@$this->input->post('b_status'))
		{
			//change the status of the bill
			//e-mail the sustomer to get them to pay?
		}

		if (@$this->input->post('ba_description'))
		{
			//add a bill adjustment
			$this->load->library('form_validation');

			// set rules
			$this->form_validation
								  ->set_rules('ba_description', '', 'required|strip_tags')
								  ->set_rules('ba_price', '', 'required|strip_tags|numeric')
								  ;
			if ($this->form_validation->run() == true)
			{
				$data = $this->input->post();
				$data['ba_b_id'] = $b_id;
				$data['ba_applied_u_id'] = $this->session->userdata('u_id');
				$result = $this->order_model->add_bill_adjustment($data);
				if ($result)
				{
					$this->flash->set('action', 'Adjustment added to the bill.', TRUE);
					return redirect(current_url());
				}
				else
				{
					$this->flash->set('error', 'A problem occured adding the adjustment.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information to adjust the bill.'.validation_errors(), TRUE);
			}
		}
		elseif (@$this->input->post('bd_description'))
		{
			//add a bill adjustment
			$this->load->library('form_validation');

			// set rules
			$this->form_validation
								  ->set_rules('bd_description', '', 'required|strip_tags')
								  ->set_rules('bd_price', '', 'required|strip_tags|numeric')
								  ->set_rules('bd_total', '', 'required|strip_tags|numeric')
								  ;
			if ($this->form_validation->run() == true)
			{
				$data['ba_description'] = $this->input->post('bd_description') .' '.$this->input->post('bd_price').'&#37; off';
				$data['ba_price'] = 0 - (($this->input->post('bd_total')/100) * $this->input->post('bd_price'));
				$data['ba_b_id'] = $b_id;
				$data['ba_applied_u_id'] = $this->session->userdata('u_id');
				$result = $this->order_model->add_bill_adjustment($data);
				if ($result)
				{
					$this->flash->set('action', 'Discount applied to the bill.', TRUE);
					return redirect(current_url());
				}
				else
				{
					$this->flash->set('error', 'A problem occured applying the discount.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information to adjust the bill.'.validation_errors(), TRUE);
				//redirect(current_url());
			}
		}
		elseif ($this->input->post('bill_due'))
		{
			$result = $this->order_model->make_bill_due_and_pay($b_id, $this->data['bill']['b_u_id'], $this->data['bill']['b_price']);
			//$result = $this->order_model->change_bill_status($b_id, "Pending");
			if ($result['success'] == TRUE)
			{
				$this->flash->set('action', $result['description'], TRUE);
				return redirect(site_url('admin/bills'));
			}
			else
			{
				$this->flash->set('error', $result['description'], TRUE);
				return redirect(current_url());
			}
		}
		elseif (@$this->input->post('b_payment_method'))
		{
			//mark the bill as paid
			$this->load->library('form_validation');

			// set rules
			$this->form_validation
								  ->set_rules('b_payment_method', '', 'required|strip_tags')
								  ;
			if ($this->form_validation->run() == true)
			{
				$result = $this->order_model->mark_bill_paid($b_id, $this->input->post('b_payment_method'));

				if ($result)
				{
							//email them about it
							$this->load->model('users_model');
							$member = $this->users_model->get_user( $this->data['bill']['b_u_id'] );
							$subject = config_item('site_name') .' Bill paid. ';
							$message = '<p>Hello '. $member['u_title'] .' '. $member['u_fname'] .' '. $member['u_sname'] .',</p>';
							$message .= '<p>Bill #'. $b_id .', has been recorded as paid.';
							$message .= '<p>Thank you, <br /> '. config_item('site_name') .'</p>';

							$eq[] = array('eq_email' => $member['u_email'],
										  'eq_subject' => $subject,
										  'eq_body' => $message);
							// load emails queue model
							$this->load->model('emails_queue_model');
							$this->emails_queue_model->set_queue($eq);
					$this->flash->set('action', 'Bill marked as paid.', TRUE);
					return redirect(current_url());
				}
				else
				{
					$this->flash->set('error', 'A problem occured paying the bill.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'There was an error paying the bill. '.validation_errors(), TRUE);
			}
		}

		$this->data['items'] = $this->order_model->get_bill_items($b_id);
		$this->data['adjustments'] = $this->order_model->get_bill_adjustments($b_id);

		$this->layout->set_title('View Bills')->set_breadcrumb('View Bills', 'admin/bills/index');
		$this->layout->set_title('Bill '. $b_id)->set_breadcrumb('Bill '. $b_id, 'admin/bills/view/'.$b_id);
		//$this->layout->set_js('views/members/group-info');
		$this->load->vars($this->data);
		$this->view = 'admin/bills/view';
	}


}

/* End of file */
