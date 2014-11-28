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

class Seasons extends Admin_Controller {


	public function __construct()
	{
		parent::__construct();

		if ( !$this->auth->is_allowed_to('manage_seasons', 'all' ))
		{
			show_error('You do not have permission to view this part of the website.');
		}
	}

	public function index($page=0)
	{
		$params = array(

		);
		$params = array_merge($params, $_GET);

		$this->load->model('seasons_model');

		// load pagination library
		$this->load->library('pagination');
		$config['base_url'] = '/admin/seasons/index/';
		$config['total_rows'] = $this->seasons_model->get_total_named_seasons($params);
		$config['per_page'] = (@$_GET['pp'] ? $_GET['pp'] : $_GET['pp'] = 25);
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);

		$params['start'] = $page;
		$params['limit'] = $config['per_page'];

		// get cats
		$this->data['seasons'] = $this->seasons_model->get_named_seasons($params);

		// set total string
		$this->data['total'] = ($this->data['seasons'] ? 'Results ' . ($page + 1) . ' - ' . ($page + count($this->data['seasons'])) . ' of ' . $config['total_rows'] . '.' : '0 results');

		// set sort string for sorting links
		$this->data['sort'] = '&amp;sort=' . (@$_GET['sort'] == 'asc' ? 'desc' : 'asc') . '&amp;pp=' . $_GET['pp'] . '&amp;future=1';

		$this->data['pp'] = array('10', '20', '50', '100', '200');


		$this->layout->set_title('Manage Seasons')->set_breadcrumb('Manage Seasons');
		$this->load->vars($this->data);
		$this->view = 'admin/seasons/index';
	}

	public function set($pc_id = null, $action = null) {
		$this->load->model('seasons_model');

		if (@$pc_id)
		{
			//attempt to get cat
			$this->data['season'] = $this->seasons_model->get_season($pc_id);
			if (!$this->data['season'])
			{
				show_404();
			}
			$title = 'Edit Season';
		}
		else
		{
			$title = 'Add Season';
		}
		$this->data['title'] = $title;



		//did we want to delete the user?
		/* cant delete seasons, they just end */

		$this->data['action_confirm'] = $action;
		if ($action == 'delete' && @$this->input->post('action-confirm') == 'delete')
		{
			$result = $this->seasons_model->delete_season($pc_id);
			if (@$result)
			{
				$this->flash->set('action', 'Season was season.', TRUE);
				return redirect(site_url().'admin/seasons');
			}
			else
			{
				$this->flash->set('action', 'Error occured when deleting the season.', TRUE);
				return redirect(site_url().'admin/seasons');
			}
		}


		if ($this->input->post())
		{
			// load form validation library
			$this->load->library('form_validation');

			// set rules
			$this->form_validation
								  ->set_rules('pc_name', '', 'required|strip_tags')
								  ->set_rules('pc_period_start', '', 'required')
								  ->set_rules('pc_period_end', '', 'required')
								  ;

			// if form validates
			if ($this->form_validation->run() == true)
			{

				//update season details
				$result = $this->seasons_model->update_season($pc_id);
				if (@$result)
				{
					$this->flash->set('action', 'Season added/updated.', TRUE);
					return redirect(site_url().'admin/seasons');
				}
				else
				{
					$this->flash->set('error', 'Season not added/updated.', TRUE);
					return redirect(current_url());
				}
			}
			else
			{
				$this->flash->set('error', 'Please correct the missing or wrong information.'.validation_errors(), TRUE);
			}
		}

		$this->layout->set_title('Manage Seasons')->set_breadcrumb('Manage Seasons', 'admin/seasons');
		$this->layout->set_title($title)->set_breadcrumb($title);
		$this->layout->set_js(array('views/admin/seasons/set', 'plugins/jquery.validate'));
		$this->load->vars($this->data);
		$this->view = 'admin/seasons/set';
	}


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
