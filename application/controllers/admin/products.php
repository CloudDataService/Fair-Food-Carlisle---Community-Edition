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

class Products extends Admin_Controller {


	public function __construct()
	{
		parent::__construct();

		if ( !$this->auth->is_allowed_to('manage_products', 'any') )
		{
			show_error('You do not have permission to view this part of the website.');
		}
	}

	public function index($page=0)
	{
		$params = array(

		);
		$params = array_merge($params, $_GET);

		//permission override?
		if ( !$this->auth->is_allowed_to('manage_products', 'all') )
		{
			if ($this->auth->is_allowed_to('manage_products', 's', $this->session->userdata('u_s_id')))
			{
				$params['p_s_id'] = $this->session->userdata('u_s_id');
			}
			else
			{
				show_error('You do not have permission to view this part of the website.');
			}
		}

		$this->load->model('products_model');

		// load pagination library
		$this->load->library('pagination');
		$config['base_url'] = site_url('/admin/products/index');
		$config['total_rows'] = $this->products_model->get_total_products($params);
		$config['per_page'] = (@$_GET['pp'] ? $_GET['pp'] : $_GET['pp'] = 25);
		$config['uri_segment'] = 4;
		$config['suffix'] = '?' . @http_build_query($params);
		$this->pagination->initialize($config);

		$params['start'] = $page;
		$params['limit'] = $config['per_page'];

		// get cats
		$this->data['products'] = $this->products_model->get_products($params);

		//get datasets (to create list to search on)
		$this->load->config('datasets');

		// set total string
		$this->data['total'] = ($this->data['products'] ? 'Results ' . ($page + 1) . ' - ' . ($page + count($this->data['products'])) . ' of ' . $config['total_rows'] . '.' : '0 results');

		// set sort string for sorting links
		$this->data['sort'] = '&amp;sort=' . (@$_GET['sort'] == 'asc' ? 'desc' : 'asc') . '&amp;pp=' . $_GET['pp'] . '&amp;date_from=' . @$_GET['date_from'];

		$this->data['pp'] = array('10', '20', '50', '100', '200');

		//Get some field options
		$this->data['product_fields'] = config_item('product_fields');
		$this->load->model('categories_model');
		$this->data['product_cats'] = $this->categories_model->get_categories();


		$this->layout->set_title('Manage Products')->set_breadcrumb('Manage Products');
		$this->load->vars($this->data);
		$this->view = 'admin/products/index';
	}

	public function set($p_id = null, $action = null) {
		$this->load->model('products_model');
		$this->load->helper('dates_helper');

		if (@$p_id)
		{
			//attempt to get cat
			$this->data['product'] = $this->products_model->get_product_full($p_id);

			if (!$this->data['product'])
			{
				show_404();
			}

			$title = 'Edit Product';
		}
		else
		{
			$title = 'Add Product';
		}

		$this->data['title'] = $title;

		//did we want to delete?
		$this->data['action_confirm'] = $action;

		if ($action == 'delete' && @$this->input->post('action-confirm') == 'delete')
		{
			$result = $this->products_model->delete_product($p_id);

			if (@$result)
			{
				$this->flash->set('action', 'Product was removed.', TRUE);
				return redirect(site_url() . '/admin/products');
			}
			else
			{
				$this->flash->set('action', 'Error occured when deleting the product.', TRUE);
				return redirect(site_url() . '/admin/products');
			}
		}

		if ($this->input->post())
		{
			// load form validation library
			$this->load->library('form_validation');

			$this->form_validation->set_rules(array(
				array(
					'field' => 'p_page_order',
					'label' => 'Page Order',
					'rules' => implode('|', array(
						'integer',
					)),
				),
				array(
					'field' => 'p_status',
					'label' => 'Status',
					'rules' => implode('|', array(
					)),
				),
				array(
					'field' => 'p_slug',
					'label' => 'Slug',
					'rules' => implode('|', array(
						'required',
					)),
				),
				array(
					'field' => 'p_code',
					'label' => 'Code',
					'rules' => implode('|', array(
					)),
				),
				array(
					'field' => 'p_s_id',
					'label' => 'Supplier',
					'rules' => implode('|', array(
						'required',
						'integer',
					)),
				),
				array(
					'field' => 'p_name',
					'label' => 'Name',
					'rules' => implode('|', array(
						'required',
					)),
				),
				array(
					'field' => 'p_description',
					'label' => 'Description',
					'rules' => implode('|', array(
					)),
				),
				array(
					'field' => 'p_pu_id',
					'label' => 'Product Unit',
					'rules' => implode('|', array(
						'integer',
					)),
				),
				array(
					'field' => 'p_price',
					'label' => 'Price',
					'rules' => implode('|', array(
						'required',
					)),
				),
				array(
					'field' => 'p_cost',
					'label' => 'Cost',
					'rules' => implode('|', array(
						'required',
					)),
				),
				array(
					'field' => 'p_stock_warning',
					'label' => 'Stock Warning',
					'rules' => implode('|', array(
						'integer',
					)),
				),
			));

			// if form validates
			if ($this->form_validation->run() == true)
			{
				//update product
				if (empty($p_id))
				{
					$p_id = $this->products_model->insert_product();
				}
				else
				{
					$p_id = $this->products_model->update_product($p_id);
				}

				if (@$p_id)
				{
					//if image uploaded
					if (@$_FILES['p_image']['name'])
					{
						$this->load->helper('image_upload_helper.php');
						$img_config = array('width'=>195, 'height'=>195);
						$img_data = image_upload($p_id, 'p_image', @$this->data['product']['p_image'], 'products', $img_config);

						if (@$img_data['result'] === true)
						{
							$result = $this->products_model->update_img($p_id, $img_data['file_name']);
						}
					}

					//if price/cost has changed
					if (!empty($this->data['product'])) {
						if (($this->input->post('p_price') != FALSE && $this->input->post('p_price') != $this->data['product']['p_price'])
							|| ($this->input->post('p_cost') != FALSE && $this->input->post('p_cost') != $this->data['product']['p_cost']))
						{
							//update orders
							$this->load->model('order_model');
							$this->order_model->update_product_prices($p_id, $this->input->post('p_price'), $this->input->post('p_cost'));
						}
					}

					$this->flash->set('action', 'Product added/updated.', TRUE);

					return redirect(site_url().'admin/products');
				}
				else
				{
					$this->flash->set('error', 'Product not added/updated.', TRUE);

					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
			}
		}

		//we need to know the possible field values
		$this->config->load('datasets');
		$this->data['frequencies'] = $this->config->item('commitment_frequencies');
		$this->data['periods'] = $this->config->item('commitment_periods');

		//we need some options from the auxillary tables
		$this->data['units'] = $this->products_model->get_product_units();
		$this->load->model('categories_model');
		$this->data['categories'] = $this->categories_model->get_categories();
		$this->load->model('suppliers_model');
		$this->data['suppliers'] = $this->suppliers_model->get_suppliers();
		$this->load->model('seasons_model');
		$this->data['named_seasons'] = $this->seasons_model->get_named_seasons();

		$this->layout->set_title('Manage Products')->set_breadcrumb('Manage Products', 'admin/products');
		$this->layout->set_title($title)->set_breadcrumb($title);
		$this->layout->set_js(array('views/admin/products/set', 'plugins/jquery.validate'));
		$this->load->vars($this->data);
		$this->view = 'admin/products/set';
	}


}

/* End of file */
