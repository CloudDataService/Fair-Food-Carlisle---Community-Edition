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

class Ajax extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Produces JSON results for autocomplete form of prduct name entry
	 * Used on both the member side (fancy search) and admin side (find products to add to a members order)
	 *
	 **/
	public function product_search($query=null)
	{
		//get the searce term and some other parameters that might have come with this query
		if ($query == null)
		{
			$query = $this->input->get('term');
		}
		//get other parameters and set some defaults
		$params = $this->input->get();
		if (element('return_seasons', $params) == null)
		{
			$params['return_seasons'] = 'yes';
		}

		$this->load->model('products_model');
		$search_params = array(
			'p_name' => $query,
			'p_status' => 'Active',
			'include_supplier' => true,
			'extended_detail' => true
			);
		$products = $this->products_model->get_products( $search_params );

		// do some tidying for ajax before you send it back
		$product_view = 'products/partial_product_item';
		foreach($products as $key => $p)
		{
			if ($params['return_seasons'] == 'yes')
			{
				$products[$key]['seasons'] = $this->products_model->get_product_allowances($p['p_id']);
			}
			if ($params['return_html'] == 'yes')
			{
				$p_data = array(
					'p' => $p,
					'category' => array('cat_slug' => 'all')
					);
				$products[$key]['item_html'] = $this->load->view($product_view, $p_data, TRUE);
			}
		}

		$this->json = $products;
	}

	/**
	 * Produces a JSON object of available dates to populate the calendar. The key is milliseconds, and the value is true(available) or a stock amount.
	 *
	 * @param	$p_id	product id to check
	 */
	public function get_availability_dates($p_id)
	{
		$this->load->model('products_model');
		$seasons = $this->products_model->get_product_allowances($p_id);
		$availables = array();

		if (@$seasons)
		{
			foreach ($seasons as $s)
			{

				// Convert to date/time objects to get date range
				$start_date = new DateTime( substr($s['pc_period_start'], 0, 10) );
				$end_date = new DateTime($s['pc_period_end']);
				$interval = new DateInterval('P1D');
				$days = new DatePeriod($start_date, $interval, $end_date);

				//from pc_period_start, to pc_period_end in steps of days
				foreach ($days as $date)
				{
					if ($date->getTimestamp() > time())
					{
						$milisec = ($date->getTimestamp() * 1000);// + 3600000; //3600000=hour and adjusts for a timezone problem
						$availables["$milisec"] = true;
					}
				}
			}
			//print_r($availables);
		}

		//sort the dates, so we can get first and second (helpful for recurring orders)
		ksort($availables);
		$i = 0;
		foreach($availables as $nextdate => $true)
		{
			$availables[$i] = $nextdate;
			if ($i > 14)
			{
				break;
			}
			$i++;
		}

		//return it all as json
		$this->json = $availables;
	}


	/**
	 * Gets the next set of orders for a member
	 **/
	public function view_next_orders()
	{

		$this->auth->check_logged_in();
		$orders_filter = array( 'oi_u_id' => $this->session->userdata('u_id'),
								'awaiting_delivery' => true,
								'limit_dates' => 1,
								);
		$this->load->model('order_model');
		$this->data['orders'] = $this->order_model->get_customer_orders( $orders_filter );

		//notes too?
		$notes_where = " AND on_u_id = '". $this->session->userdata('u_id') ."' ";
		$notes_where .= " AND on_delivery_date LIKE '" . date('Y-m-d', strtotime($this->data['orders'][0]['oi_delivery_date'])) . "%' ";
		$this->data['order_notes'] = $this->order_model->get_notes( $notes_where );

		//display
		$this->load->vars($this->data);
		$this->view = 'order/ajax-list';
		echo $this->load->view($this->view, $this->data, TRUE);
		die();
	}


	/**
	 * check array of items for in-stock or not
	 **/
	public function check_items_availability($pids = array())
	{
		if ($pids == array() && $this->input->get('pids') != null)
		{
			$pids = $this->input->get('pids');
		}

		$products = array();
		foreach(explode(',', $pids) as $pid)
		{
			$this->load->model('products_model');
			$seasons = array();
			$seasons = $this->products_model->get_product_allowances($pid, 1);

			if ($seasons == array())
			{
				$products[$pid] = 'out';
			}
			else
			{
				$products[$pid] = 'in';
			}
		}

		$this->json = $products;
	}

	/**
	 * Gets a suitable and unique slug to use as a URL
	 *
	 * @param	$text string	The text to slugerize
	 * @param	$type string	The type of item to check for uniqueness (e.g. product)
	 */
	public function get_url_slug($text, $type)
	{
		//prepare config, dependant on type
		if ($type == 'product')
		{
			$config = array(
					'field' => 'p_slug',
					'table' => 'product',
					'id' => 'p_id',
					'title' => $text
			);
		}
		else
		{
			die();
		}
		//our own fiddling
		$text = str_replace('%20', '-', $text);
		$pattern = '(%[0-9]+)';
		$replacement = '${1}';
		$text = preg_replace($pattern, $replacement, $text);

		//find a good slug
		$this->load->library('slug', $config);
		$data['slug'] = $this->slug->create_uri($text);

		$this->json = $data;
	}

	/**
	 * Get todays date for javascript to use, because we don't trust the client clock
	 */
	public function get_date_today()
	{
		$data = array(
			'year' => date('Y'),
			'month' => date('m'),
			'day' => date('d'),
			'hours' => date('H'),
			'minutes' => date('i'),
		);
		$this->json = $data;
	}

	/**
	 * Get details about a specific permission, in order to help admins setting them to users.
	 */
	public function permissions_info($pg_id)
	{
		$this->load->model('permissions_model');
		$pg = $this->permissions_model->get_group_details($pg_id);

		if (!$pg)
		{
			$data['htmltext'] = 'Permission group could not be found in the system.';
		}
		elseif (count($pg) == 1 && $pg[0]['pi_name'] == '')
		{
			$data['htmltext'] = 'Permissions for <em>'. $pg[0]['pg_name'] .'</em>...';
			$data['htmltext'] .= '<br />No permissions granted.';
		}
		else
		{
			$data['htmltext'] = 'Permissions for <em>'. $pg[0]['pg_name'] .'</em>...';
			foreach($pg as $pi)
			{
				if ($pi['pg2pi_value'] == 1)
				{
					$data['htmltext'] .= '<br />'. $pi['pi_name'] .'.';
				}
				if ($pi['pg2pi_bg_value'] == 1)
				{
					$data['htmltext'] .= '<br />'. $pi['pi_name'] .' for members of their buying group.';
				}
				if ($pi['pg2pi_s_value'] == 1)
				{
					$data['htmltext'] .= '<br />'. $pi['pi_name'] .' for produce that they supply.';
				}
			}
		}

		$this->json = $data;
	}


}
