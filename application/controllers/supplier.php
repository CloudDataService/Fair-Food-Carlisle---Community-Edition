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

class Supplier extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('suppliers_model');
	}


	/*
	 * If we have a supplier to view
	 */
	public function index($s_id=null)
	{
		if (!$s_id)
		{
			//we have no supplier index?
			return redirect('products');
		}

		$this->data['supplier'] = $this->suppliers_model->get_supplier($s_id);
		if (!$this->data['supplier'])
		{
			show_404();
		}


		//build the breadcrumb
		$this->layout->set_title('Suppliers')->set_breadcrumb('Suppliers', '/supplier');

		$this->layout->set_title($this->data['supplier']['s_name'])->set_breadcrumb($this->data['supplier']['s_name'], '/supplier/'.$s_id);

		$this->load->vars($this->data);
		$this->view = 'suppliers/index';
	}


}

/* End of file */
