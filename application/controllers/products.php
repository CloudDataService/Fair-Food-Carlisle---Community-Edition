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

class Products extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('categories_model');
		$this->load->model('products_model');
	}


	/*
	 * Depending how many valid parts we have in the url, display some kind of product page
	 */
	public function index($cat_slug = null, $p_slug = null)
	{
		if ($p_slug != null)
		{
			//is the product in this cat?
			$p_item = $this->products_model->find_product($p_slug, $cat_slug);
			if (@$p_item['cat_slug'] == $cat_slug)
			{
				//yay, spot on
				$this->view_product($p_slug, $cat_slug);
			}
			elseif (@$p_item && @$p_item['cat_slug'] != null)
			{
				//go to the category it is in
				return redirect(site_url(). 'products/'. $p_item['cat_slug'] . '/'. $p_slug);
			}
			elseif (@$p_item['cat_slug'] == null && $cat_slug == 'all')
			{
				//this will do
				$this->view_product($p_slug, $cat_slug);
			}
			elseif (@$p_item['cat_slug'] == null)
			{
				//no category, redirect to all products
				return redirect(site_url(). 'products/all/'. $p_slug);
			}
			else
			{
				//you were looking for a product that we can't find.
				show_404();
			}
		}
		elseif ($cat_slug != null)
		{
			//we deal with categories in a different way to the product index?
			$this->view_category($cat_slug);
		}
		else
		{
			//no product, no category, just show us some goods!
			$this->data['categories'] = $this->categories_model->get_categories(null, TRUE);

			$this->layout->set_title('Produce')->set_breadcrumb('Produce', '/products');
			$this->load->vars($this->data);
			$this->view = 'products/index';
		}
	}

	/*
	 * If we have a product to view
	 */
	public function view_product($p_slug, $cat_slug)
	{
		$this->data['product'] = $this->products_model->get_product_full(null, $p_slug);
		if (!$this->data['product'])
		{
			show_404();
		}

		if (@$this->input->post())
		{
			if ($this->session->userdata('bg_status') != 'Active')
			{
				$this->flash->set('error', 'At this time, orders cannot be made for the buying group you are part of.', TRUE);
				return redirect(current_url());
			}
			if ($this->input->post('oi_frequency') == 'selected')
			{
				$details = array(
					'u_id' => $this->session->userdata('u_id'),
					's_id' => $this->data['product']['p_s_id'],
					'p_id' => $this->data['product']['p_id'],
					'unit_price' => $this->data['product']['p_price'],
					'unit_cost' => $this->data['product']['p_cost']
				);

				//does order need confirmation?
				if (config_item('confirm_new_orders') == 1)
				{
					$details['status'] = 'Reserved';
				}
				else
				{
					$details['status'] = 'Confirmed';
				}

				$this->load->model('order_model');
				$results = $this->order_model->add_items($details, $this->input->post('orders'));
				if (!$results)
				{
					//what the?
					$this->flash->set('error', 'Items could not be ordered. There may not be enough available.', TRUE);
					return redirect(current_url());
				}
				elseif ($results < count($this->input->post('orders')))
				{
					//all good, carry on shopping
					$this->flash->set('warning', 'Items were not available for all dates, please <a href="'. site_url('order') .'">check and confirm your orders</a>.', TRUE);
					return redirect(site_url().'products/'.$cat_slug);
				}
				else
				{
					//all good, carry on shopping
					if ($details['status'] == 'Reserved')
					{
						$this->flash->set('action', 'Items added, <a href="'. site_url('order') .'">confirm your orders</a> or continue shopping below.', TRUE);
					}
					else
					{
						$this->flash->set('action', 'Items ordered, continue shopping below.', TRUE);
					}
					return redirect(site_url().'products/'.$cat_slug);
				}
			}
			else
			{
				//add to order_recurring table
				$details = array(
					'or_u_id' => $this->session->userdata('u_id'),
					'or_s_id' => $this->data['product']['p_s_id'],
					'or_p_id' => $this->data['product']['p_id'],
					'or_qty' => $this->input->post('oi_quantity'),
					'or_frequency' => $this->input->post('oi_frequency'),
					'or_started_date' => $this->input->post('or_startmilli')
				);
				$this->load->model('order_recurring_model');
				$result = $this->order_recurring_model->add_schedule($details);
				if (!$result)
				{
					//what the?
					$this->flash->set('error', 'Recurring order could not be made.', TRUE);
					return redirect(current_url());
				}
				else
				{
					//all good, carry on shopping
					$this->flash->set('action', 'Recurring order placed, <a href="'. site_url('order') .'">confirm your orders</a> or continue shopping below.', TRUE);
					return redirect(site_url().'products/'.$cat_slug);
				}
			}
		}

		//get allowances
		$this->config->load('datasets');
		$this->data['frequencies'] = $this->config->item('commitment_frequencies');
		$this->data['periods'] = $this->config->item('commitment_periods');

		$seasons = $this->products_model->get_product_allowances($this->data['product']['p_id']);
		$this->data['stock'] = $this->products_model->calculate_stock_remaining($seasons);

		$this->load->helper('foodshop_helper');
		$this->data['allowed_days'] = calninek_build_order_data('4 months', $seasons, $this->session->userdata('bg_deliveryday'));

		// dates for when delivery frequencies can start
		$this->data['available_one'] = current($this->data['allowed_days']['days']);
		if ($this->data['available_one'] != null) {
			$this->data['available_one'] = array_shift($this->data['available_one']);
		} else {
			$this->data['available_one'] = null;
		}

		$this->data['available_two'] = next($this->data['allowed_days']['days']);
		if ($this->data['available_two'] != null) {
			$this->data['available_two'] = array_shift($this->data['available_two']);
		} else {
			$this->data['available_two'] = null;
		}

		//get the category it is in
		$this->data['category'] = $this->categories_model->get_category($cat_slug);


		//build the breadcrumb
		$this->layout->set_title('Produce')->set_breadcrumb('Produce', '/products');
		if (@$this->data['category']['cat_parent_slug']) {
			$this->layout->set_title($this->data['category']['cat_parent_name'])->set_breadcrumb($this->data['category']['cat_parent_name'], '/products/'.$this->data['category']['cat_parent_slug']);
		}
		if (@$this->data['category']['cat_name'])
		{
			$this->layout->set_title($this->data['category']['cat_name'])->set_breadcrumb($this->data['category']['cat_name'], '/products/'.$this->data['category']['cat_slug']);
		}
		else
		{
			$this->layout->set_title('Categories')->set_breadcrumb('Categories', '/products/');
		}
		$this->layout->set_title($this->data['product']['p_name'])->set_breadcrumb($this->data['product']['p_name'], '/products/'.$this->data['product']['p_slug']);

		$this->layout->set_js(array('plugins/jquery.calendarPicker', 'views/products/calNineK', 'views/products/product'));
		$this->layout->set_css(array('jquery-ui/jquery.calendarPicker'));
		$this->load->vars($this->data);
		$this->view = 'products/product';
	}

	/*
	 * If we have a category to view
	 */
	public function view_category($cat_slug)
	{
		$this->data['category'] = $this->categories_model->get_category($cat_slug);
		if (!$this->data['category'])
		{
			show_404();
		}

		$this->data['products'] = $this->products_model->get_category_products( $this->data['category']['cat_id'] );



		$this->layout->set_title('Produce')->set_breadcrumb('Produce', '/products');
		if (@$this->data['category']['cat_parent_slug']) {
			$this->layout->set_title($this->data['category']['cat_parent_name'])->set_breadcrumb($this->data['category']['cat_parent_name'], '/products/'.$this->data['category']['cat_parent_slug']);
		}


		$this->layout->set_title($this->data['category']['cat_name'])->set_breadcrumb($this->data['category']['cat_name'], '/products/'.$this->data['category']['cat_slug']);
		$this->layout->set_js(array('views/stock-check', 'views/products/category'));
		$this->load->vars($this->data);
		$this->view = 'products/category';
	}


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
