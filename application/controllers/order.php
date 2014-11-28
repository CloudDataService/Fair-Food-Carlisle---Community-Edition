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

class Order extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('order_model');
	}


	/*
	 * Not sure what goes here
	 */
	public function index()
	{
		$this->auth->check_logged_in();
		$orders_filter = array( 'oi_u_id' => $this->session->userdata('u_id'),
								'awaiting_delivery' => true,
								'limit_dates' => 1,
								);
		$this->data['orders'] = $this->order_model->get_customer_orders( $orders_filter );
			$this->load->model('products_model');
			//do they have time to cancel any?
			if ($this->data['orders'])
			{
				foreach($this->data['orders'] as $k => $o)
				{
					$this->data['orders'][$k]['can_cancel'] = $this->products_model->check_product_allowance($o['oi_p_id'], $o['oi_delivery_date'], null);
				}
			}

		$bills_filter = array( 'b_u_id' => $this->session->userdata('u_id'),
								'b_status' => 'Pending'
								);
		$this->data['bills'] = $this->order_model->get_bills( $bills_filter );

		$this->load->model('order_recurring_model');
		$orders_recurring_filter = array( 'or_u_id' => $this->session->userdata('u_id'),
										  'or_status' => 'Pending');
		$this->data['orders_recurring'] = $this->order_recurring_model->get_recurring_orders( $orders_recurring_filter );


		if (@$this->input->post())
		{
			if ($this->input->post('oi_new_status') == 'Confirmed')
			{
				if ($this->input->post('oi_id') == 'all')
				{
					$result = $this->order_model->confirm_stock($this->session->userdata('u_id'));
					if (@$result)
					{
						$this->flash->set('action', 'All items updated, please check below that they were all confirmed.', TRUE);
						return redirect(current_url());
					}
				}
				else
				{
					$result = $this->order_model->confirm_stock($this->session->userdata('u_id'), $this->input->post('oi_id'));
					if (@$result)
					{
						$this->flash->set('action', 'Item was updated.', TRUE);
						return redirect(current_url());
					}
				}
			}
			elseif ($this->input->post('oi_new_status') == 'Cancelled')
			{
				$result = $this->order_model->update_status($this->input->post('oi_id'), $this->input->post('oi_new_status'));
				if (@$result)
				{
					$this->flash->set('action', 'Item cancelled.', TRUE);
					return redirect(current_url());
				}
			}
			elseif ($this->input->post('or_new_status') == 'Confirmed')
			{
				$result = $this->order_recurring_model->confirm_schedule($this->input->post('or_id'), $this->session->userdata('u_id'));
				if ($result)
				{
					$this->flash->set('action', 'Ongoing order was confirmed and started.', TRUE);
					return redirect(current_url());
				}
			}
			elseif ($this->input->post('or_new_status') == 'Stopped' || $this->input->post('or_new_status') == 'Cancelled')
			{
				$result = $this->order_recurring_model->cancel_schedule($this->input->post('or_id'), $this->session->userdata('u_id'), $this->input->post('or_new_status'));
				if ($result)
				{
					$this->flash->set('action', 'The ongoing order was stopped.', TRUE);
					return redirect(current_url());
				}
			}
			elseif ($this->input->post('on_delivery_date') && $this->input->post('on_text'))
			{
				$note = array(
					'on_u_id' => $this->session->userdata('u_id'),
					'on_delivery_date' => $this->input->post('on_delivery_date'),
					'on_text' => $this->input->post('on_text'),
					'on_added_by' => $this->session->userdata('u_id'),
				);
				$result = $this->order_model->add_note($note);
				if (@$result)
				{
					$this->flash->set('action', 'Note added.', TRUE);
					return redirect(current_url());
				}
			}
			//form sent, but don't know what to do...
			$this->flash->set('error', 'Items could not be updated.', TRUE);
			return redirect(current_url());
		}

		$notes_where = " AND on_u_id = '". $this->session->userdata('u_id') ."' ";
		$this->data['order_notes'] = $this->order_model->get_notes( $notes_where );

		$this->layout->set_title('Orders and Bills')->set_breadcrumb('Orders and Bills');
		$this->load->vars($this->data);
		$this->view = 'order/index';
	}

	public function bills()
	{
		$this->auth->check_logged_in();

		$bills_filter = array( 'b_u_id' => $this->session->userdata('u_id')
								);
		$this->data['bills'] = $this->order_model->get_bills( $bills_filter );



		$this->layout->set_title('Orders and Bills')->set_breadcrumb('Orders and Bills', 'order');
		$this->layout->set_title('My Bills')->set_breadcrumb('My Bills');
		$this->load->vars($this->data);
		$this->view = 'order/bills';
	}

	public function orders()
	{
		$this->auth->check_logged_in();
		$orders_filter = array( 'oi_u_id' => $this->session->userdata('u_id'),
								'awaiting_delivery' => true
								);
		$this->data['orders'] = $this->order_model->get_customer_orders( $orders_filter );
			$this->load->model('products_model');
			//do they have time to cancel any?
			foreach($this->data['orders'] as $k => $o)
			{
				$this->data['orders'][$k]['can_cancel'] = $this->products_model->check_product_allowance($o['oi_p_id'], $o['oi_delivery_date'], null);
			}


		if (@$this->input->post())
		{
			if ($this->input->post('oi_new_status') == 'Confirmed')
			{
				if ($this->input->post('oi_id') == 'all')
				{
					$result = $this->order_model->confirm_stock($this->session->userdata('u_id'));
					if (@$result)
					{
						$this->flash->set('action', 'All items updated, please check below that they were all confirmed.', TRUE);
						return redirect(current_url());
					}
				}
				else
				{
					$result = $this->order_model->confirm_stock($this->session->userdata('u_id'), $this->input->post('oi_id'));
					if (@$result)
					{
						$this->flash->set('action', 'Item was updated.', TRUE);
						return redirect(current_url());
					}
				}
			}
			elseif ($this->input->post('oi_new_status') == 'Cancelled')
			{
				$result = $this->order_model->update_status($this->input->post('oi_id'), $this->input->post('oi_new_status'));
				if (isset($result))
				{
					$this->flash->set('action', 'Item cancelled.', TRUE);
					return redirect(current_url());
				}
			}
			elseif ($this->input->post('on_delivery_date') && $this->input->post('on_text'))
			{
				$note = array(
					'on_u_id' => $this->session->userdata('u_id'),
					'on_delivery_date' => $this->input->post('on_delivery_date'),
					'on_text' => $this->input->post('on_text'),
					'on_added_by' => $this->session->userdata('u_id'),
				);
				$result = $this->order_model->add_note($note);
				if (@$result)
				{
					$this->flash->set('action', 'Note added.', TRUE);
					return redirect(current_url());
				}
			}
			//form sent, but don't know what to do...
			$this->flash->set('error', 'Items could not be updated.', TRUE);
			return redirect(current_url());
		}

		$notes_where = " AND on_u_id = '". $this->session->userdata('u_id') ."' ";
		$this->data['order_notes'] = $this->order_model->get_notes( $notes_where );

		$this->layout->set_title('Orders and Bills')->set_breadcrumb('Orders and Bills', 'order');
		$this->layout->set_title('My Orders')->set_breadcrumb('My Orders');
		$this->load->vars($this->data);
		$this->view = 'order/orders';
	}

	public function ongoing()
	{

		$this->auth->check_logged_in();

		$this->load->model('order_recurring_model');
		$orders_recurring_filter = array( 'or_u_id' => $this->session->userdata('u_id'));
		$this->data['orders_recurring'] = $this->order_recurring_model->get_recurring_orders( $orders_recurring_filter );


		if (@$this->input->post())
		{
			if ($this->input->post('or_new_status') == 'Confirmed')
			{
				$result = $this->order_recurring_model->confirm_schedule($this->input->post('or_id'), $this->session->userdata('u_id'));
				if ($result)
				{
					$this->flash->set('action', 'Ongoing order was confirmed and started.', TRUE);
					return redirect(current_url());
				}
			}
			elseif ($this->input->post('or_new_status') == 'Stopped' || $this->input->post('or_new_status') == 'Cancelled')
			{
				$result = $this->order_recurring_model->cancel_schedule($this->input->post('or_id'), $this->session->userdata('u_id'), $this->input->post('or_new_status'));
				if ($result)
				{
					$this->flash->set('action', 'The ongoing order was stopped.', TRUE);
					return redirect(current_url());
				}
			}
			//form sent, but don't know what to do...
			$this->flash->set('error', 'Items could not be updated.', TRUE);
			return redirect(current_url());
		}

		$this->layout->set_title('Orders and Bills')->set_breadcrumb('Orders and Bills', 'order');
		$this->layout->set_title('My Ongoing Orders')->set_breadcrumb('My Ongoing Orders');
		$this->load->vars($this->data);
		$this->view = 'order/ongoing';
	}


}

/* End of file */
