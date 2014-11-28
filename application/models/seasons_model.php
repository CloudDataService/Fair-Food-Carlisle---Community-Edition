<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handle seasons within the system
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


class Seasons_model extends MY_Model
{


	protected $_table = 'productcommitment';
	protected $_primary = 'pc_id';


	public function __construct()
	{
		parent::__construct();
	}




	/**
	 * Get a single season, and many details about it
	 *
	 * @author GM
	 * @param int $pc_id		the productcommitment id requested(int)
	 * @return mixed		DB result row on success, FALSE on failure
	 */
	public function get_season($pc_id)
	{
		$sql = 'SELECT
					productcommitment.*
				FROM
					productcommitment
				WHERE
					pc_id = '. (int)$pc_id .'
				AND
					pc_status <> 0
				LIMIT 1
				';
		return $this->db->query($sql)
					->row_array();
	}

	/**
	 *
	 * @return mixed	pc_id on success, false on failure
	 */
	public function update_season($pc_id)
	{
		if ($pc_id)
		{
			$sql = 'UPDATE
						productcommitment
					SET
						pc_name = ?,
						pc_period_start = ?,
						pc_period_end = ?,
						pc_preseason_gap = ?,
						pc_predelivery_gap = ?
					WHERE
						pc_id = ?';
			$sql_data = array($this->input->post('pc_name'),
						 date("Y-m-d", strtotime(str_replace('/', '-', $this->input->post('pc_period_start'))) ),
						 date("Y-m-d", strtotime(str_replace('/', '-', $this->input->post('pc_period_end'))) ),
						 $this->input->post('pc_preseason_gap'),
						 $this->input->post('pc_predelivery_gap'),
						 $pc_id
						  );
		}
		else
		{
			$sql = 'INSERT
					INTO
						productcommitment
						(pc_name, pc_period_start, pc_period_end, pc_preseason_gap, pc_predelivery_gap)
					VALUES
						(?, ?, ?, ?, ?)';
			$sql_data = array($this->input->post('pc_name'),
							  date("Y-m-d", strtotime(str_replace('/', '-', $this->input->post('pc_period_start'))) ),
							  date("Y-m-d", strtotime(str_replace('/', '-', $this->input->post('pc_period_end'))) ),
							  $this->input->post('pc_preseason_gap'),
							  $this->input->post('pc_predelivery_gap'),
						  );
		}

		//run query
		$result = $this->db->query($sql, $sql_data);

		//all done
		if ($result && $pc_id)
		{
			return $pc_id;
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
	 * Get a list of seasons that have names (unnamed seasons are the old way of working)
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_named_seasons($params=null)
	{
		if ( !$params) {
			$params = array('order' => 'pc_id');
		}

		if ( ! in_array(@$params['order'], array('pc_id', 'pc_name', 'pc_period_start', 'pc_period_end', 'pc_predelivery_gap'))) {
			$params['order'] = 'pc_id';
		}

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') {
			$params['sort'] = 'desc';
		}

		$sql = 'SELECT
					productcommitment.*,
					pc_period_start AS pc_period_start_format,
					pc_period_end AS pc_period_end_format
				FROM
					productcommitment
				LEFT JOIN
					product
					ON pc_p_id = p_id
				';

		$sql .= ' WHERE pc_name IS NOT NULL
				 ';

		$sql .= ' AND pc_status != 0
				 ';

		if (isset($params['future']) && $params['future'] == TRUE) {
			$sql .= ' AND pc_period_end > ' . date('Y-m-d') . ' ';
		}

		// set order by clause
		$sql .= ' ORDER BY ' . $params['order'] . ' ' . $params['sort'] . ' ';

		// if a limit has been set
		if (@$params['limit'] != FALSE) {
			$sql .= ' LIMIT ' . (int)$params['start'] . ', ' . (int)$params['limit'];
		}

		$seasons = $this->db->query($sql)->result_array();

		return $seasons;
	}

	/**
	 * Get the total count of seasons, ignoring the limit in filter.
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return int	the number of seasons found
	 */
	public function get_total_named_seasons($params=null)
	{
		if ( !$params) $params = array('order' => 'pc_id');

		$sql = 'SELECT
					COUNT(pc_id) AS total
				FROM
					productcommitment
				';

		$sql .= ' WHERE pc_name IS NOT NULL AND pc_status != 0';

		if (isset($params['future']) && $params['future'] == TRUE) {
			$sql .= ' AND pc_period_end > ' . date('Y-m-d') . ' ';
		}

		$result = $this->db->query($sql)->row_array();
		return $result['total'];
	}

	/**
	 * Sets a season status to Removed, so they should not be displayed in lists.
	 */
	public function delete_season($pc_id)
	{
			$sql = 'UPDATE
						productcommitment
					SET
						pc_status = 0
					WHERE
						pc_id = '. (int)$pc_id .'
					LIMIT 1;';
			$result = $this->db->query($sql);
			if (@$result)
			{
				return true;
			}
			else
			{
				return false;
			}
	}
}

/* End of file. */
