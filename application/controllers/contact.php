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

class Contact extends MY_Controller {


	public function __construct()
	{
		parent::__construct();
	}


	public function index($reason=null)
	{
		//Carlisle have their own contact form
		if (config_item('site_abbr') == 'FFC')
		{
			return redirect('http://fairfoodcarlisle.org/contact/');
		}

		$this->data['link_reason'] = $reason;

		if ($this->input->post())
		{
			// load form validation library
			$this->load->library('form_validation');

			// set rules
			$this->form_validation
								  ->set_rules('cf_name', '', 'required|strip_tags')
								  ->set_rules('cf_email', '', 'required|strip_tags')
								  ->set_rules('cf_phone', '', 'required|strip_tags')
								  ->set_rules('cf_subject', '', 'required|strip_tags')
								  ->set_rules('cf_message', '', 'required|strip_tags')
								  ;

			// if form validates
			if ($this->form_validation->run() == true)
			{
				$topics = config_item('contact_reasons');
				$subject = 'A message via the '. config_item('site_abbr') .' RE: ' . $topics[$this->input->post('cf_subject')] . '';
				$body = '<p>A message was sent via the '. config_item('site_abbr') .' with regards to '. $topics[$this->input->post('cf_subject')] .'<br />';
				$body .= '<strong>From: </strong>'. $this->input->post('cf_name') .'<br />';
				$body .= '<strong>Telephone number: </strong>'. $this->input->post('cf_phone') .'<br />';
				$body .= '<strong>E-mail address: </strong>'. $this->input->post('cf_email') .'<br />';
				$body .= '</p><p>';
				$body .= '<strong>Message: </strong>'. $this->input->post('cf_message') .'<br />';
				$body .= '</p>';
				$eq[] = array('eq_email' => config_item('contact_email'),
							  'eq_subject' => $subject,
							  'eq_body' => $body);
				// load emails queue model
				$this->load->model('emails_queue_model');
				$this->emails_queue_model->set_queue($eq);
				//email done

				$this->flash->set('action', 'Thank you for contacting us, your message has been sent.', TRUE);
				return redirect(site_url());
			}
			else
			{
				$this->data['error'] = 'Please fill out the required fields.';
				$this->data['input'] = $this->input->post();
			}
		}

		$this->view = 'default/contact';
		$this->layout->set_title('Contact Us');
		$this->load->vars($this->data);
	}

}

?>
