<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to generate reports for admins
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


class Reports_model extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Build the sql to add to any queries for order reports
	 *
	 * @author GM
	 * @param Array $filters	The filters asked for, usually post data
	 * @return String		Suitable for appending to WHERE 1=1
	 */
	public function get_orders_filter($filter)
	{
		$sql_and = '';
		$sql_joins = array();
		if (isset($filter['delivery_from']) && $filter['delivery_from'] != '')
		{
			$sql_and .= ' AND oi_delivery_date >= '. $this->db->escape($filter['delivery_from']) .' ';
		}
		if (isset($filter['delivery_to']) && $filter['delivery_to'] != '')
		{
			$sql_and .= ' AND oi_delivery_date <= '. $this->db->escape($filter['delivery_to']) .' ';
		}
		if (isset($filter['bg_id']) && $filter['bg_id'] != '')
		{
			$sql_and .= ' AND u_bg_id = '. (int)$filter['bg_id'] .' ';
			$sql_joins['user'] = ' LEFT JOIN user ON oi_u_id = u_id ';
		}
		if (isset($filter['s_id']) && $filter['s_id'] != '')
		{
			$sql_and .= ' AND oi_s_id = '. (int)$filter['s_id'] .' ';
		}
		return array('and' => $sql_and, 'joins' => implode(' ', $sql_joins));
	}

	/**
	 * Get the data for the Overview report
	*/
	public function get_overview_data($sql_filter)
	{
		$sql = "SELECT
					DATE_FORMAT(oi_delivery_date, '%D %b %Y') AS 'Delivery Date',
					COUNT(oi_id) AS 'Items Ordered',
					COUNT(DISTINCT oi_u_id) AS 'Customers Ordering',
					ROUND((COUNT(oi_id) / COUNT(DISTINCT oi_u_id)), 2) AS 'Avg Orders Per Customer'
				FROM
					orderitem
					". $sql_filter['joins'] ."
				WHERE
					oi_status = 'Confirmed'
					". $sql_filter['and'] ."
				GROUP BY
					oi_delivery_date
				;";
		$result = $this->db->query($sql)->result_array();
		//type cast
		foreach($result as $key => $row)
		{
			$result[$key]['Items Ordered'] = (int)$row['Items Ordered'];
			$result[$key]['Customers Ordering'] = (int)$row['Customers Ordering'];
			$result[$key]['Avg Orders Per Customer'] = (float)$row['Avg Orders Per Customer'];
		}
		return $result;
	}

	public function get_ordering_data($sql_filter)
	{
		$sql = "SELECT
					DATE_FORMAT(oi_ordered_date, '%D %b %Y') AS 'Date Customer Made The Ordered',
					COUNT(oi_id) AS 'Items Ordered',
					COUNT(DISTINCT oi_u_id) AS 'Customers Ordering',
					ROUND((COUNT(oi_id) / COUNT(DISTINCT oi_u_id)), 2) AS 'Avg Orders Made Per Customer'
				FROM
					orderitem
					". $sql_filter['joins'] ."
				WHERE
					oi_status = 'Confirmed'
					". $sql_filter['and'] ."
				GROUP BY
					DATE_FORMAT(oi_ordered_date, '%D %b %Y')
				ORDER BY
					oi_ordered_date
				;";
		$result = $this->db->query($sql)->result_array();
		//type cast
		foreach($result as $key => $row)
		{
			$result[$key]['Items Ordered'] = (int)$row['Items Ordered'];
			$result[$key]['Customers Ordering'] = (int)$row['Customers Ordering'];
			$result[$key]['Avg Orders Made Per Customer'] = (float)$row['Avg Orders Made Per Customer'];
		}
		return $result;
	}

	/**
	 * Get the data for the Value of Orders report
	*/
	public function get_value_data($sql_filter)
	{
		$sql = "SELECT
					DATE_FORMAT(oi_delivery_date, '%D %b %Y') AS 'Delivery Date',
					SUM(oi_price) AS 'Produce Price',
					SUM(oi_cost) AS 'Produce Cost',
					(SUM(oi_price) - SUM(oi_cost)) AS 'Profit'
				FROM
					orderitem
					". $sql_filter['joins'] ."
				WHERE
					oi_status = 'Confirmed'
					". $sql_filter['and'] ."
				GROUP BY
					oi_delivery_date
				;";
		$result = $this->db->query($sql)->result_array();
		//type cast
		foreach($result as $key => $row)
		{
			$result[$key]['Produce Price'] = (float)$row['Produce Price'];
			$result[$key]['Produce Cost'] = (float)$row['Produce Cost'];
			$result[$key]['Profit'] = (float)$row['Profit'];
		}
		return $result;
	}
}
?>
