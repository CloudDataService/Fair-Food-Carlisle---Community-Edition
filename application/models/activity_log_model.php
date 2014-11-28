<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handle activity log entries
 *
 * @package Healthy Heart
 * @subpackage Models
 * @author CR
 */

class Activity_log_model extends MY_Model
{
	
	
	protected $_table = 'activity_log';
	protected $_primary = 'al_id';
	protected $_pct_key = 'al_pct_id';
	
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('user_agent');
	}
	
	
	
	
	public function add($type = '', $data = array())
	{
		// Attempt to get description text for this type of event
		$description = $this->_get_description($type, $data);
		
		// Data to insert into database for this entry
		$entry = array(
			'al_pct_id' => 0,
			'al_u_id' => $this->session->userdata('u_id'),
			'al_type' => $type,
			'al_area' => element('area', $data),
			'al_uri' => $this->uri->uri_string(),
			'al_description' => $description,
			'al_datetime' => date('Y-m-d H:i:s'),
			'al_ua' => $this->agent->agent_string(),
			'al_browser' => $this->agent->browser() . ' ' . $this->agent->version(),
			'al_ip' => $this->input->ip_address(),
		);
		
		return parent::insert($entry);
	}
	
	
	
	
	/**
	 * Get the description text for a given activity type
	 *
	 * @param string $type		Type of activity being logged
	 * @param array $data		Optional data relating to the activity
	 * @return string		HTML-formatted string of the activity description
	 */
	private function _get_description($type = '', $data = array())
	{
		// Method name to call
		$method = "_type_$type";
		
		// Initial description
		$description = '';
		
		if (method_exists($this, $method))
		{
			// Requested method exists in the class - run it
			$description = call_user_func_array(array($this, $method), $data);
		}
		
		return $description;
	}
	
	
	
	
	/**
	 * Login event type
	 */
	public function _type_login($data = array())
	{
		return 'Logged in';
	}
	
	
	/**
	 * Logout event type
	 */
	public function _type_logout($data = array())
	{
		return 'Logged out';
	}
	
	
	/**
	 * Access denied event type
	 */
	public function _type_access_denied($data = array())
	{
		return 'Access denied';
	}
	
	
	
	
}

/* End of file: ./application/models/activity_log_model.php */