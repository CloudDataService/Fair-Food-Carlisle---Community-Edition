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

class Home extends Admin_Controller {

	public function index()
	{
		$this->data['somevar'] = 'this is a var';

		$this->layout->set_title('Home')->set_breadcrumb('Home');
		$this->layout->set_title('Home');
		$this->load->vars($this->data);
		$this->view = 'admin/index';
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
