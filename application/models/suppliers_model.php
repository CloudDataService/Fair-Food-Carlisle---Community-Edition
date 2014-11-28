<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handle suppliers within the system
 *
 * @package Fair-Food Carlisle
 * @subpackage Models
 * @author GM
 *
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


class Suppliers_model extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
	}




	/**
	 * Get a single supplier, and many details about it
	 *
	 * @author GM
	 * @param int $s_id		the supplier id requested
	 * @return mixed		DB result row on success, FALSE on failure
	 */
	public function get_supplier($s_id)
	{
		$sql = 'SELECT
					s_id,
					s_name,
					s_image,
					s_description
				FROM
					supplier
				WHERE s_id = '. (int)$s_id.'
				LIMIT 1
				';
		return $this->db->query($sql)->row_array();
	}

	/**
	 *
	 * @return mixed	s_id on success, false on failure
	 */
	public function update_supplier($s_id)
	{
		//parse for urls
		$this->load->helper('text_helper');
		$description = auto_link_text($this->input->post('s_description'));

		if ($s_id)
		{
			$sql = 'UPDATE
						supplier
					SET
						s_name = ?,
						s_description = ?
					WHERE
						s_id = ?';
			$sql_data = array($this->input->post('s_name'),
						 $description,
						 $s_id
						  );
		}
		else
		{
			$sql = 'INSERT
					INTO
						supplier
						(s_name, s_description)
					VALUES
						(?, ?)';
			$sql_data = array($this->input->post('s_name'),
						 $description,
						  );
		}

		//run query
		$result = $this->db->query($sql, $sql_data);

		//all done
		if ($result && $s_id)
		{
			return $s_id;
		}
		else if ($result)
		{
			return $this->db->insert_id();
		}
		else
		{
			return false;
		}

	}

	/**
	 * Get a list of suppliers in the system
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_suppliers($params=null)
	{
		if ( !$params) $params = array('order' => 's_id');
		if ( ! in_array(@$params['order'], array('s_name')) ) $params['order'] = 's_id';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'desc';

		$sql = 'SELECT
					s_id,
					s_name
				FROM
					supplier
				';

		$sql .= ' WHERE 1 = 1 ';

		if (@$params['s_name'])
			$sql .= ' AND s_name = ' . $this->db->escape($params['s_name']) . ' ';

		if (@$params['s_id'])
			$sql .= ' AND s_id = ' . $this->db->escape($params['s_id']) . ' ';

		// set order by clause
		$sql .= ' ORDER BY ' . $params['order'] . ' ' . $params['sort'] . ' ';

		// if a limit has been set
		if (@$params['limit'] != FALSE)
			$sql .= ' LIMIT ' . (int)$params['start'] . ', ' . (int)$params['limit'];

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get the total count of supplier, ignoring the limit in filter.
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return int	the number of users found
	 */
	public function get_total_suppliers($params=null)
	{
		if ( !$params) $params = array('order' => 's_id');
		if ( ! in_array(@$params['order'], array('s_name')) ) $params['order'] = 's_id';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'desc';

		$sql = 'SELECT
					COUNT(s_id) AS total
				FROM
					supplier
				';

		$sql .= ' WHERE 1 = 1 ';

		if (@$params['s_name'])
			$sql .= ' AND s_name = ' . $this->db->escape($params['s_name']) . ' ';

		if (@$params['s_id'])
			$sql .= ' AND s_id = ' . $this->db->escape($params['s_id']) . ' ';

		$result = $this->db->query($sql)->row_array();
		return $result['total'];
	}

	/*
	 * Updates the image to one that has hopefully just been uploaded
	 */
	public function update_img($s_id, $filename)
	{
		$sql = 'UPDATE
					supplier
				SET
					s_image = ?
				WHERE
					s_id = "' . (int)$s_id . '";';

		return $this->db->query($sql, $filename);
	}


}

/* End of file */
