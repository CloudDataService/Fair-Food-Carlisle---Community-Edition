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

class Categories extends Admin_Controller {


	public function __construct()
	{
		parent::__construct();

		if ( !$this->auth->is_allowed_to('manage_categories', 'all') )
		{
			show_error('You do not have permission to view this part of the website.');
		}
	}

	public function index($page=0)
	{
		$params = array(

		);
		$params = array_merge($params, $_GET);

		$this->load->model('categories_model');

		// load pagination library
		$this->load->library('pagination');
		$config['base_url'] = '/admin/categories/index/';
		$config['total_rows'] = $this->categories_model->get_total_categories($params);
		$config['per_page'] = (@$_GET['pp'] ? $_GET['pp'] : $_GET['pp'] = 25);
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);

		$params['start'] = $page;
		$params['limit'] = $config['per_page'];

		// get cats
		$this->data['categories'] = $this->categories_model->get_categories($params);

		//get datasets (to create list to search on)
		$this->load->config('datasets');
		//$this->data['user_fields'] = $this->config->item('user');

		// set total string
		$this->data['total'] = ($this->data['categories'] ? 'Results ' . ($page + 1) . ' - ' . ($page + count($this->data['categories'])) . ' of ' . $config['total_rows'] . '.' : '0 results');

		// set sort string for sorting links
		$this->data['sort'] = '&amp;sort=' . (@$_GET['sort'] == 'asc' ? 'desc' : 'asc') . '&amp;pp=' . $_GET['pp'] . '&amp;date_from=' . @$_GET['date_from'];

		$this->data['pp'] = array('10', '20', '50', '100', '200');


		$this->layout->set_title('Manage Categories')->set_breadcrumb('Manage Categories');
		//$this->layout->set_js('views/admin/categories/index');
		$this->load->vars($this->data);
		$this->view = 'admin/categories/index';
	}

	public function set($cat_id = null, $action = null) {
		$this->load->model('categories_model');

		if (isset($cat_id))
		{
			//attempt to get cat
			$this->data['category'] = $this->categories_model->get_category($cat_id);
			if (!$this->data['category'])
			{
				show_404();
			}
			$title = 'Edit Category';
		}
		else
		{
			$title = 'Add category';
		}
		$this->data['title'] = $title;



		//did we want to delete the user?
		$this->data['action_confirm'] = $action;
		if ($action == 'delete' && @$this->input->post('action-confirm') == 'delete')
		{
			$result = $this->categories_model->delete_category($cat_id);
			if (@$result)
			{
				$this->flash->set('action', 'Category was deleted.', TRUE);
				return redirect(site_url().'admin/categories');
			}
			else
			{
				$this->flash->set('action', 'Error occured when deleting the category.', TRUE);
				return redirect(site_url().'admin/categories');
			}
		}

		if ($this->input->post())
		{

			// load form validation library
			$this->load->library(array('form_validation'));


			$this->load->helper('url');

			// set rules
			$this->form_validation
			  ->set_rules('cat_name', '', 'required|strip_tags')
			  ->set_rules('cat_slug', '', 'required|strip_tags|callback__cds_check_duplicate')
			 ;
				$this->form_validation->set_message('_cds_check_duplicate','Short Name should be unique');
			// if form validates
			if ($this->form_validation->run() == true)
			{

				//if image uploaded
				if (@$_FILES['cat_image']['name'])
				{
					$this->load->helper('image_upload_helper');
					$img_config = array('width'=>142, 'height'=>142);
					$img_data = image_upload($cat_id, 'cat_image', @$this->data['category']['cat_image'], 'categories', $img_config);

					if (@$img_data['result'] === true)
					{
						$result = $this->categories_model->update_img($cat_id, $img_data['file_name']);
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

				//update category details
				$result = $this->categories_model->update_category($cat_id);
				if (@$result)
				{
					$this->flash->set('action', 'Category added/updated.', TRUE);
					return redirect(site_url().'admin/categories');
				}
				else
				{
					$this->flash->set('error', 'Category not added/updated.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{

				$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
			}
		}

		//if we want to make kittens, we need some cats
		if ($this->input->post()) {
			foreach($this->input->post() as $key => $value) {
				$this->data['category'][$key] = $value;
			}
		}
			$this->data['categories'] = $this->categories_model->get_categories();


		$this->layout->set_title('Manage Categories')->set_breadcrumb('Manage Categories', 'admin/categories');
		$this->layout->set_title($title)->set_breadcrumb($title);
		$this->layout->set_js(array('views/admin/categories/set', 'plugins/jquery.validate'));
		$this->load->vars($this->data);
		$this->view = 'admin/categories/set';
	}

	public function _cds_check_duplicate($string) {
		$config = array(
		    'table' => 'category',
		    'id' => 'cat_id',
		    'field' => 'cat_slug',
		    'title' => 'cat_slug',
		    'replacement' => 'dash' // Either dash or underscore
		);
		// load form validation library
		$this->load->library(array('slug'));
		$this->slug->set_config($config);
		$slug = $this->slug->create_slug($string);
		if ($this->uri->segment(4) != null) {
			$update = 1;
		} else
			$update = 0;

		if (!$this->slug->_check_duplicate($slug,$update)) {

			return FALSE;
		} else{
			return TRUE;
		}
	}

}





/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
