<?php

class Cron extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	/*
	 * Reads the emails_queue db table and sends them on their way.
	 * should be run about every 5mins.
	 * /usr/bin/wget "http://products.fairfoodcarlisle.org/cron/emails_queue" >/dev/null 2>&1
	 */
	public function emails_queue()
	{
		// load emails queue model
		$this->load->model('emails_queue_model');

		// if emails are returned
		if ($emails = $this->emails_queue_model->get_queue())
		{
			// load email library
			$this->load->library('email');

			// loop over each email
			foreach($emails as $e)
			{
				$config['mailtype'] = 'html';

				$message = '<div style="background-color:#428B73; font-family:Arial, Helvetica, sans-serif; font-size:13px; color:#333; padding:30px; padding-top:190px;">
								<div style="margin:0 auto; background-color:#fff; width:600px; padding:30px; padding-top:35px; position:relative;">
									<img src="' . site_url('img/logo.png') .'" style="position:absolute; top:-170px; left:-20px;" alt="'. $this->config->item('site_name') .' logo" />
									' . $e['eq_body'] . '
									<p>Regards, ' . $this->config->item('site_name') . '</p>
								</div>

								<div style="margin:0 auto; padding-top:15px; width:600px; text-align:right;">
								</div>
							</div>';

				// set email params
				$this->email->clear(TRUE)
							->initialize($config)
							->from($this->config->item('auto_email'), $this->config->item('site_name'))
							->to($e['eq_email'])
							->subject($e['eq_subject'])
							->message($message);

				// if an attachment is set
				if ($e['eq_attachment'] != NULL)
				{
					// attach file
					$this->email->attach(FCPATH . 'files/' . $e['eq_attachment']);
				}

				// if email sends ok
				$this->email->send();

				$last_eq_id = $e['eq_id'];
			}
			// delete all sent emails
			$this->emails_queue_model->delete_queue($last_eq_id);
		}

		exit;
	}

	/**
	 * Gets reserved order_items that have been there for a while and unreserves them
	 * Should be run about every 30mins?
	 *
	 */
	public function unreserve_order_items()
	{
		$this->load->model('order_model');
		$orders = $this->order_model->get_unconfirmed_items( date('Y-m-d H:i:s', strtotime('now - '. $this->config->item('item_reserved_timeout'))) );

		if (@$orders)
		{
			$this->load->model('products_model');

			foreach($orders as $oi)
			{
				$this->products_model->update_product_stock($oi['oi_p_id'], '+', $oi['oi_qty'], $oi['oi_delivery_date']);
				$this->order_model->change_order_status($oi['oi_id'], "Expired");
			}
		}

		exit;
	}

	/*
	 * Checks order_items and puts them into bills for staff to confirm
	 * Should be run about 1am each day
	 * /usr/bin/wget "http://products.fairfoodcarlisle.org/cron/generate_bills" >/dev/null 2>&1
	 */
	public function generate_bills()
	{
		$this->load->model('order_model');
		$orderinfo = $this->order_model->get_unbilled_items( date('Y-m-d')); //returns them grouped(fields concatenated or added up) by u_id

		if (isset($orderinfo))
		{
			foreach($orderinfo as $oi)
			{
				if ($oi['oi_value'] >= 1) //bills need to be at least £1 or Go Cardless spazzes out (value is the total of the unbilled items with a user)
				{
					$bill_data = array('b_u_id' => $oi['oi_u_id']);
					$b_id = $this->order_model->create_bill($bill_data);

					if ($b_id)
					{
						//put the orders in it
						$items = explode(',', $oi['oi_ids']);
						$this->order_model->add_to_bill($b_id, $items);
					}
				}
			}

			print_r($orderinfo);
		}

		exit;
	}

	/*
	 * Checks order_recurring for those that might need some new order_items to be created
	 * Should be run before generate_bills each day? or just some time each day
	 * /usr/bin/wget "http://products.fairfoodcarlisle.org/cron/auto_order" >/dev/null 2>&1
	 */
	public function auto_order()
	{
		$generate_to = strtotime('now +'. config_item('recuring_order_buffer'));

		//get the orders we need
		$sql = 'SELECT
					or_id,
					or_u_id,
					or_s_id,
					or_p_id,
					or_qty,
					or_frequency,
					or_status,
					or_started_date,
					or_latest_date,
					or_finished_date
				FROM
					order_recurring
				WHERE
					or_status = "Confirmed"
					AND (
						or_latest_date IS NOT NULL
						AND or_latest_date < "'. date('Y-m-d', $generate_to) .'"
					)
				';

		$ors = $this->db->query($sql)->result_array();

		if (isset($ors))
		{
			$this->load->model('order_recurring_model');

			foreach($ors as $or)
			{
				$this->order_recurring_model->create_auto_orders($or['or_id'], $or);
			}
		}

		exit;
	}
}
