<?php

class Emails_queue_model extends CI_Model
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * queues an array of emails for sending
	 */
	public function set_queue(array $eq)
	{
		// use active record to generate batch insert query
		$this->db->insert_batch('emails_queue', $eq);
	}
	
	/**
	 * returns current queue of emails
	 */
	public function get_queue($limit = 100)
	{
		$sql = 'SELECT
					*
				FROM
					emails_queue
				ORDER BY
					eq_id ASC
				LIMIT ' . (int)$limit;
				
		return $this->db->query($sql)->result_array();
	}
	
	/**
	 * deletes all rows with a PK less than the last id specified
	 * effectively removing sent emails
	 */
	public function delete_queue($last_eq_id)
	{
		$sql = 'DELETE FROM
					emails_queue
				WHERE
					eq_id <= "' . (int)$last_eq_id . '";';
		
		$this->db->query($sql);
	}
	
}