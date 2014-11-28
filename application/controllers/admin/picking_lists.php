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

class Picking_lists extends Admin_Controller {


	public function __construct()
	{
		parent::__construct();

		if ( !$this->auth->is_allowed_to('view_picking_lists', 'any') )
		{
			show_error('You do not have permission to view this part of the website.');
		}
	}

	public function index($date=null, $u_id=null)
	{
		if (@$this->input->post('picking_date'))
		{
			return redirect(site_url('admin/picking-lists/'. str_replace('/', '-', $this->input->post('picking_date'))));
		}

		$this->load->model('order_model');

		$this->layout->set_title('Picking Lists')->set_breadcrumb('Picking Lists', '/admin/picking-lists');

		$this->load->helper('dates_helper');


		if (isset($date) && isValidDate($date,'_'))
		{
			$this->data['date'] = strtotime( str_replace('_', '-', $date) );

			//get relevant confirmed orders
			$search = array('oi_delivery_date' => date('Y-m-d', $this->data['date']),
							'oi_status' => 'Confirmed',
							'order' => array(
										'bg_name',
										'u_sname',
										'u_fname'
										)
							);
			$notes_where = " AND on_delivery_date = '". date('Y-m-d', $this->data['date']) ."' ";
			if (isset($u_id))
			{
				$this->data['search_u_id'] = $u_id;
				$search['oi_u_id'] = $u_id;
				$notes_where .= " AND on_u_id = '". $u_id ."' ";
			}

			//permission override?
			if ( !$this->auth->is_allowed_to('view_picking_lists', 'all') )
			{
				if ($this->auth->is_allowed_to('view_picking_lists', 'bg', $this->session->userdata('u_bg_id')))
				{
					$search['u_bg_id'] = $this->session->userdata('u_bg_id');
				}
				if ($this->auth->is_allowed_to('view_picking_lists', 's', $this->session->userdata('u_s_id')))
				{
					$search['oi_s_id'] = $this->session->userdata('u_s_id');
				}
			}

			//get them...
			$this->data['orders'] = $this->order_model->get_orders($search);
			$this->data['search'] = $search; //for displaying what we've filtered on.
			$this->data['order_notes'] = $this->order_model->get_notes( $notes_where );

			$date_str = date('jS M Y', $this->data['date']);
			$this->layout->set_title($date_str)->set_breadcrumb($date_str, 'admin/picking-lists/'.$date);
			if (isset($u_id) ) {
				if (isset($this->data['orders'][0]))
				{
					$title = $this->data['orders'][0]['u_title'] .' '. $this->data['orders'][0]['u_fname'] .' '. $this->data['orders'][0]['u_sname'];
					$this->layout->set_title($title)->set_breadcrumb($title);
				}
				else
				{
					//oh crumbs, we don't have the user's name to put in the breadcrumb
					$this->load->model('users_model');
					$member = $this->users_model->get_user($u_id);
					$title = $member['u_title'] .' '. $member['u_fname'] .' '. $member['u_sname'];
					$this->layout->set_title($title)->set_breadcrumb($title);
				}
			}

			$this->load->vars($this->data);
			$this->view = 'admin/picking_lists/view';
		}
		else
		{
			if (isset($date) && !isValidDate($date,'_')):
				$this->data['error_message'] = "Not a valid date";
			endif;
			$this->layout->set_js(array('views/admin/picking_lists/index', 'plugins/jquery.validate'));
			$this->load->vars($this->data);
			$this->view = 'admin/picking_lists/index';
		}
	}

}
/* End of file */
