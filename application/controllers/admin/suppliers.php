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

class Suppliers extends Admin_Controller {


	public function __construct()
	{
		parent::__construct();

		if ( !$this->auth->is_allowed_to('manage_suppliers', 'any') )
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
		if ( !$this->auth->is_allowed_to('manage_suppliers', 'all') )
		{
			if ($this->auth->is_allowed_to('manage_suppliers', 's', $this->session->userdata('u_s_id')))
			{
				$params['s_id'] = $this->session->userdata('u_s_id');
			}
			else
			{
				show_error('You do not have permission to view this part of the website.');
			}
		}



		$this->load->model('suppliers_model');

		// load pagination library
		$this->load->library('pagination');
		$config['base_url'] = '/admin/suppliers/index/';
		$config['total_rows'] = $this->suppliers_model->get_total_suppliers($params);
		$config['per_page'] = (@$_GET['pp'] ? $_GET['pp'] : $_GET['pp'] = 25);
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);

		$params['start'] = $page;
		$params['limit'] = $config['per_page'];

		// get cats
		$this->data['suppliers'] = $this->suppliers_model->get_suppliers($params);

		//get datasets (to create list to search on)
		$this->load->config('datasets');

		// set total string
		$this->data['total'] = ($this->data['suppliers'] ? 'Results ' . ($page + 1) . ' - ' . ($page + count($this->data['suppliers'])) . ' of ' . $config['total_rows'] . '.' : '0 results');

		// set sort string for sorting links
		$this->data['sort'] = '&amp;sort=' . (@$_GET['sort'] == 'asc' ? 'desc' : 'asc') . '&amp;pp=' . $_GET['pp'] . '&amp;date_from=' . @$_GET['date_from'];

		$this->data['pp'] = array('10', '20', '50', '100', '200');


		$this->layout->set_title('Manage Producers')->set_breadcrumb('Manage Producers');
		$this->load->vars($this->data);
		$this->view = 'admin/suppliers/index';
	}

	public function set($s_id = null) {
		if (!$this->auth->is_allowed_to('manage_suppliers', 'all') && !$this->auth->is_allowed_to('manage_suppliers', 's', $s_id))
		{
			show_error('You do not have permission to manage this producer.');
		}

		$this->load->model('suppliers_model');

		if (@$s_id)
		{
			//attempt to get cat
			$this->data['supplier'] = $this->suppliers_model->get_supplier($s_id);
			if (!$this->data['supplier'])
			{
				show_404();
			}
			$title = 'Edit Producer';
		}
		else
		{
			$title = 'Add Producer';
		}

		if ($this->input->post())
		{
			// load form validation library
			$this->load->library('form_validation');

			// set rules
			$this->form_validation
								  ->set_rules('s_name', '', 'required|strip_tags')
								  ->set_rules('s_description', '', 'required|strip_tags')
								  ;

			// if form validates
			if ($this->form_validation->run() == true)
			{
				//update supplier
				$s_id = $this->suppliers_model->update_supplier($s_id);
				if (@$s_id)
				{
					//if image uploaded
					if (@$_FILES['s_image']['name'])
					{
						$this->load->helper('image_upload_helper.php');
						$img_config = array('width'=>142, 'height'=>142);
						$img_data = image_upload($s_id, 's_image', @$this->data['supplier']['s_image'], 'suppliers', $img_config);

						if (@$img_data['result'] === true)
						{
							$result = $this->suppliers_model->update_img($s_id, $img_data['file_name']);
						}
						else
						{
							$this->flash->set('error', 'Image was not uploaded.', TRUE);
						}
						if (!$result)
						{

							$this->flash->set('error', 'Image was not updated.', TRUE);
						}
					}

					$this->flash->set('action', 'Producer added/updated.', TRUE);
					return redirect(site_url().'admin/suppliers');
				}
				else
				{
					$this->flash->set('error', 'Producer not added/updated.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
			}
		}

		$this->layout->set_title('Manage Producers')->set_breadcrumb('Manage Producers', 'admin/suppliers');
		$this->layout->set_title($title)->set_breadcrumb($title);
		$this->layout->set_js(array('views/admin/suppliers/set', 'plugins/jquery.validate'));
		$this->load->vars($this->data);
		$this->view = 'admin/suppliers/set';
	}

	public function orders($s_id)
	{
		if (!$this->auth->is_allowed_to('manage_suppliers', 'all') && !$this->auth->is_allowed_to('manage_suppliers', 's', $s_id))
		{
			show_error('You do not have permission to view orders for this producer.');
		}
		$this->load->model('suppliers_model');
		$this->load->model('order_model');

		if (@$s_id)
		{
			//attempt to get cat
			$this->data['supplier'] = $this->suppliers_model->get_supplier($s_id);
			if (!$this->data['supplier'])
			{
				show_404();
			}
		}

		$this->data['orders'] = $this->order_model->get_supplier_orders( $s_id );

		$this->layout->set_title('Producer Orders')->set_breadcrumb('Producer Orders');
		$this->load->vars($this->data);
		$this->view = 'admin/suppliers/orders';
	}


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
