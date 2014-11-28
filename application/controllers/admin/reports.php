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

class Reports extends Admin_Controller {


	public function __construct()
	{
		parent::__construct();

		if ( !$this->auth->is_allowed_to('view_reports', 'any') )
		{
			show_error('You do not have permission to view this part of the website.');
		}
	}

	public function index($page=0)
	{
		$this->layout->set_title('Reports')->set_breadcrumb('Reports', 'admin/reports');
		$this->load->vars($this->data);
		$this->view = 'admin/reports/index';
	}

	public function orders()
	{
		//needed files
		$this->load->model('reports_model');
		require_once( APPPATH . '/third_party/GoogleChart.php' );

		//get filters that were posted
		$params = $this->input->post();

		//filters from permisisons
		if ( !$this->auth->is_allowed_to('view_reports', 'all') )
		{
			if ($this->auth->is_allowed_to('view_reports', 'bg', $this->session->userdata('u_bg_id')))
			{
				$params['bg_id'] = $this->session->userdata('u_bg_id');
			}
			if ($this->auth->is_allowed_to('view_reports', 's', $this->session->userdata('u_s_id')))
			{
				$params['s_id'] = $this->session->userdata('u_s_id');
			}
		}

		//filter to go back into form/display
		$this->data['filter'] = $params;

		//create sql from filter
		$this->load->helper('dates_helper');
		$filter = english_dates_array_to_mysql_dates($params);
		$sql_filter = $this->reports_model->get_orders_filter( $filter );

		//get report data
		$this->data['reports']['overview'] = $this->reports_model->get_overview_data($sql_filter);
		$this->data['reports']['orders'] = $this->reports_model->get_ordering_data($sql_filter);
		$this->data['reports']['value'] = $this->reports_model->get_value_data($sql_filter);


		//get filter options
		$this->load->model('groups_model');
		$this->data['buying_groups'] = $this->groups_model->get_groups(array('bg_status' => 'Active'));


		//ready for the page
		$this->layout->set_title('Reports')->set_breadcrumb('Reports', 'admin/reports');
		$this->layout->set_title('Orders')->set_breadcrumb('Orders');
		$this->load->vars($this->data);
		$this->layout->set_js(array('views/admin/reports/default', 'plugins/jquery.validate', '//www.google.com/jsapi'));
		$this->view = 'admin/reports/orders';
	}

	public function produce()
	{
		//needed files
		$this->load->model('reports/produce_reports_model', 'reports_model');
		require_once( APPPATH . '/third_party/GoogleChart.php' );

		//get filters that were posted
		$params = $this->input->post();

		//filters from permisisons
		if ( !$this->auth->is_allowed_to('view_reports', 'all') )
		{
			if ($this->auth->is_allowed_to('view_reports', 'bg', $this->session->userdata('u_bg_id')))
			{
				$params['bg_id'] = $this->session->userdata('u_bg_id');
			}
			if ($this->auth->is_allowed_to('view_reports', 's', $this->session->userdata('u_s_id')))
			{
				$params['p_s_id'] = $this->session->userdata('u_s_id');
			}
		}

		//filter to go back into form/display
		$this->data['filter'] = $params;

		//create sql from filter
		$this->load->helper('dates_helper');
		$filter = english_dates_array_to_mysql_dates($params);
		$sql_filter = $this->reports_model->get_filter( $filter );

		//needed data for filter options
		$this->data['categories'] = $this->reports_model->get_category_array();
		$this->data['suppliers'] = $this->reports_model->get_supplier_array();

		//get report data
		$this->data['reports']['top_sellers'] = $this->reports_model->get_top_sellers_data($sql_filter);
		$this->data['reports']['bottom_sellers'] = $this->reports_model->get_bottom_sellers_data($sql_filter);


		//ready for the page
		$this->layout->set_title('Reports')->set_breadcrumb('Reports', 'admin/reports');
		$this->layout->set_title('Produce')->set_breadcrumb('Produce');
		$this->load->vars($this->data);
		$this->layout->set_js(array('views/admin/reports/default', 'plugins/jquery.validate', '//www.google.com/jsapi'));
		$this->view = 'admin/reports/produce';
	}

	public function members()
	{
		$this->layout->set_title('Reports')->set_breadcrumb('Reports', 'admin/reports');
		$this->layout->set_title('Members')->set_breadcrumb('Members');
		$this->load->vars($this->data);
		$this->view = 'admin/reports/members';
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
