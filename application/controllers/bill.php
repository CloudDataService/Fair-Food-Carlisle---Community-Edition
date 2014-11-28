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

class Bill extends MY_Controller {

	public function index($page=0)
	{
		//Members(customers) see bills on their order page
		return redirect(site_url('order'));

		$params = array(
			'b_u_id' => $this->session->userdata('u_id')
		);
		$params = array_merge($params, $_GET);

		$this->load->model('order_model');

		// load pagination library
		$this->load->library('pagination');
		$config['base_url'] = '/bill/index/';
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

		$this->layout->set_title('View Bills')->set_breadcrumb('View Bills', 'admin/bills/index');
		//$this->layout->set_js('views/admin/users/index');
		$this->load->vars($this->data);
		$this->view = 'bill/index';
	}

	public function view($b_id)
	{
		$this->auth->check_logged_in();
		$this->load->model('order_model');
		$this->data['bill'] = $this->order_model->get_bill($b_id);

		if (!$this->data['bill'])
		{
			show_404();
		}

		$this->data['items'] = $this->order_model->get_bill_items($b_id);
		$this->data['adjustments'] = $this->order_model->get_bill_adjustments($b_id);

		$this->layout->set_title('View Bills')->set_breadcrumb('View Bills', 'bill/index');
		$this->layout->set_title('Bill '. $b_id)->set_breadcrumb('Bill '. $b_id, 'bill/view/'.$b_id);
		$this->load->vars($this->data);
		$this->view = 'bill/view';
	}

	public function gocardless_payment($b_id)
	{
		//get the bill to pay.
		$this->load->model('order_model');
		$bill = $this->order_model->get_bill($b_id);

		if (!$bill)
		{
			show_404();
		}

		//are we ready to pay it?
		if ($bill['b_status'] != 'Pending')
		{
			$this->flash->set('error', 'The payment for this bill can not be made right now.', TRUE);
			return redirect('bill/view/'.$b_id);
		}

		//is it enough for Go Cardless?
		if ($bill['b_price'] < 1)
		{
			$this->flash->set('error', 'The bill is below the Go Cardless minimum spend.', TRUE);
			return redirect('bill/view/'.$b_id);
		}

		//get going with Go Cardless
		require_once( APPPATH . '/third_party/GoCardless.php' );
		GoCardless::$environment = config_item('gocardless_environment');
		GoCardless::set_account_details(config_item('gocardless_account'));

		if ($this->input->post('automate_payments') == 1)
		{
			//set up an authorization
			if ( !$this->input->post('gc_max_payment') || !is_numeric($this->input->post('gc_max_payment')) )
			{
				$this->flash->set('error', 'The maximum value must be a number', TRUE);
				return redirect('bill/view/'.$b_id);
			}

			if ( $this->input->post('gc_max_payment') < $bill['b_price'] )
			{
				$this->flash->set('error', 'That authorization will not be enough to pay this bill.', TRUE);
				return redirect('bill/view/'.$b_id);
			}

			$payment_details = array(
				'redirect_uri'    => site_url('bill/gocardless_complete/'.$b_id),
				'cancel_uri'      => site_url('bill/gocardless_cancelled/'.$b_id),
				'state'           => $b_id,
				'max_amount'      => $this->input->post('gc_max_payment'),
				'name'            => 'Weekly Delivery',
				'interval_length' => 1,
				'interval_unit'   => 'week'
			);

			$gc_url = GoCardless::new_pre_authorization_url($payment_details);
		}
		elseif ($this->input->post('make_payment') == 1)
		{
			//make one off payment
			$payment_details = array(
				'redirect_uri' => site_url('bill/gocardless_complete/'.$b_id),
				'cancel_uri'   => site_url('bill/gocardless_cancelled/'.$b_id),
				'state'        => $b_id,
				'amount'       => $bill['b_price'],
				'name'         => 'Bill #'. $b_id,
			);

			$gc_url = GoCardless::new_bill_url($payment_details);
		}

		//send them to Go Cardless
		if (!$gc_url)
		{
			$this->flash->set('error', 'The payment was not recognised.', TRUE);
			return redirect('bill/view/'.$b_id);
		}
		else
		{
			return redirect($gc_url);
		}
	}

	public function gocardless_test()
	{
		if (ENVIRONMENT !== 'development') {
			show_404();
		}

		//get going with Go Cardless
		require_once( APPPATH . '/third_party/GoCardless.php' );
		GoCardless::$environment = config_item('gocardless_environment');
		GoCardless::set_account_details(config_item('gocardless_account'));

		header('Content-Type: text/plain');
		var_dump(GoCardless_Merchant::find($this->config->item('merchant_id', 'gocardless_account')));
		exit;
	}

	public function gocardless_complete()
	{
		$flash_success = $this->session->flashdata(__FUNCTION__ . '_success');

		if ($flash_success) {
			$this->session->keep_flashdata('notice');
			return redirect('order');
		}

		//get going with Go Cardless
		require_once( APPPATH . '/third_party/GoCardless.php' );
		GoCardless::$environment = config_item('gocardless_environment');
		GoCardless::set_account_details(config_item('gocardless_account'));

		//we've come back from Go Cardless
		//finalize the process
		$confirm_params = array(
		  'resource_uri'  => $this->input->get('resource_uri'),
		  'resource_id'   => $this->input->get('resource_id'),
		  'resource_type' => $this->input->get('resource_type'),
		  'signature'     => $this->input->get('signature'),
		  'state'         => $this->input->get('state')
		);

		// Returns the confirmed resource if successful, otherwise throws an exception
		$confirm_result = GoCardless::confirm_resource($confirm_params);

		if (!$confirm_result)
		{
			$this->flash->set('error', 'There was an error processing with Go Cardless.', TRUE);
			return redirect('bill/view/'. $this->input->get('state'));
		}

		//check what to do now
		//var_export($this->input->get()); die();
		if ($this->input->get('resource_type') == 'bill')
		{
			//a single bill was paid, update it
			$this->load->model('order_model');
			$result = $this->order_model->mark_bill_paid($this->input->get('state'), 'Go Cardless');

			//send the user on
			if (!$result)
			{
				$this->flash->set('error', 'The payment was taken, but an error occured updating the bill. Please contact website staff.', TRUE);
			}
			else
			{
				$this->flash->set(__FUNCTION__ . '_success', TRUE, TRUE);
				$this->flash->set('notice', 'Thank you for paying with Go Cardless.', TRUE);
			}

			return redirect('order');
		}
		else if ($this->input->get('resource_type')  == 'pre_authorization')
		{
			//Go Cardless is authorized to take payments.
			//make a note
			$this->load->model('order_model');
			$this->order_model->gc_save_preauth_id($this->session->userdata('u_id'), $this->input->get('resource_id'));

			//Pay a bill we were on?
			if ( is_numeric($this->input->get('state')) && $this->input->get('state') > 0)
			{
				//get the bill to pay.
				$this->load->model('order_model');
				$bill = $this->order_model->get_bill( $this->input->get('state') );

				if (isset($bill))
				{
					//pay the bill we were on...
					$pre_auth = GoCardless_PreAuthorization::find( $this->input->get('resource_id') );
					$gc_bill = $pre_auth->create_bill(array(
						'name'   => 'Bill #'. $bill['b_id'],
						'amount' => $bill['b_price']
					));
				}

				//send the user on
				if (!$gc_bill)
				{
					$this->flash->set('error', 'Go Cardless was authorised for futue payments, but an error occured paying this bill.', TRUE);
				}
				else
				{
					//after authorizing bill was paid, update it
					$this->load->model('order_model');
					$result = $this->order_model->mark_bill_paid($this->input->get('state'), 'Go Cardless');

					//send the user on
					if (!$result)
					{
						$this->flash->set('error', 'Payment was taken, but an error occured updating the bill. Please contact website staff.', TRUE);
					}
					else
					{
						$this->flash->set('notice', 'Thank you for paying with Go Cardless. Future payments will be made automatically.', TRUE);
					}
				}

				return redirect('order');
			}
			else
			{
				$this->flash->set('success', 'Go Cardless has been set up for future bills.', TRUE);
				return redirect('order'); //send them where?
			}
		}
		else
		{
			$this->flash->set('error', 'Payment process not recognised.', TRUE);
			return redirect('order');
		}
	}

	public function gocardless_cancelled()
	{
		$this->flash->set('error', 'The payment process was cancelled.', TRUE);
		return redirect('order');
	}
}

/* End of file */
