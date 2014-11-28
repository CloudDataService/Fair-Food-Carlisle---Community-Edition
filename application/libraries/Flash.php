<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library to handle flash messages of different styles
 *
 * @package Healthy Heart
 * @subpackage Libraries
 * @author CR
 */

class Flash
{
	
	
	protected $_CI;		// CodeIgntier object
	
	private $_msgs;		// Array of messages
	
	
	public function __construct()
	{
		$this->_CI =& get_instance();
	}
	
	
	
	
	/**
	 * Set a new flash message
	 */
	public function set($type = 'success', $text = '', $session = FALSE)
	{
		// Get the markup for this flash message and store in internal array
		$this->_msgs[] = $this->string($type, $text);
		
		if ($session == TRUE)
		{
			// Set the flashdata to all of the messages combined
			$this->_CI->session->set_flashdata('flash', implode('', $this->_msgs));
		}
		
	}
	
	
	
	
	/**
	 * Get the flash message
	 */
	public function get()
	{
		$msgs = '';
		if (empty($this->_msgs))
		{
			$msgs = $this->_CI->session->flashdata('flash');
		}
		else
		{
			$msgs = implode('', $this->_msgs);
		}
		return $msgs;
	}
	
	
	
	
	/**
	 * Get a markup-formatted flash message.
	 *
	 * Useful for showing inline in the page without redirecting
	 *
	 * @param string $type		Type of message (error|success|info)
	 * @param string $text		Text of message to display
	 * @return string		HTML markup for message
	 */
	public function string($type = 'success', $text = '')
	{
		$data = array(
			'type' => $type,
			'text' => $text,
		);
		
		$html = $this->_CI->load->view('partials/flash_message', $data, TRUE);
		
		return $html;
	}
	
	
}

/* End of file: ./application/libaries/Flash.php */