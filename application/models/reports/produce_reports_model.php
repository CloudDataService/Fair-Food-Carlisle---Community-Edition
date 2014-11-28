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


class Produce_reports_model extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Build the sql to add to any queries for these reports
	 *
	 * @author GM
	 * @param Array $filters	The filters asked for, usually post data
	 * @return String		Suitable for appending to WHERE 1=1
	 */
	public function get_filter($filter)
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
		if (isset($filter['p_cat_id']) && $filter['p_cat_id'] != '')
		{
			$sql_and .= ' AND p2cat_cat_id = '. $this->db->escape($filter['p_cat_id']) .' ';
		}
		if (isset($filter['bg_id']) && $filter['bg_id'] != '')
		{
			$sql_and .= ' AND u_bg_id = '. (int)$filter['bg_id'] .' ';
			$sql_joins['user'] = ' LEFT JOIN user ON oi_u_id = u_id ';
		}
		if (isset($filter['p_s_id']) && $filter['p_s_id'] != '')
		{
			$sql_and .= ' AND p_s_id = '. $this->db->escape($filter['p_s_id']) .' ';
		}
		return array('and' => $sql_and, 'joins' => implode(' ', $sql_joins));
	}

	/**
	 * Get the data needed for a category list
	 */
	public function get_category_array()
	{
		$sql = 'SELECT
					cat_id,
					cat_name
				FROM
					category
				WHERE
					category.cat_status = "Active" ';
		$result = $this->db->query($sql)->result_array();
		return $result;
	}

	/**
	 * Get the data needed for a supplier filter list
	 */
	public function get_supplier_array()
	{
		$sql = 'SELECT
					s_id,
					s_name
				FROM
					supplier
				WHERE
					1 = 1';
		$result = $this->db->query($sql)->result_array();
		return $result;
	}

	/**
	 * Get the data for the Overview report
	*/
	public function get_top_sellers_data($sql_filter)
	{
		$sql = "SELECT
					p_name AS 'Product Name',
					COUNT(DISTINCT oi_u_id) AS 'Customers Ordering',
					COUNT(oi_id) AS 'Total Orders'
				FROM
					orderitem
				LEFT JOIN
					product ON oi_p_id = p_id
				LEFT JOIN
					p2cat ON p_id = p2cat_p_id
					". $sql_filter['joins'] ."
				WHERE
					oi_status = 'Confirmed'
					". $sql_filter['and'] ."
				GROUP BY
					p_name
				ORDER BY COUNT(DISTINCT oi_u_id) DESC
				LIMIT 0, 15
				;";
		$result = $this->db->query($sql)->result_array();
		//type cast
		$i = 1;
		foreach($result as $key => $row)
		{
			$result[$key]['Total Orders'] = (int)$row['Total Orders'];
			$result[$key]['Customers Ordering'] = (int)$row['Customers Ordering'];
		}
		return $result;
	}

	/**
	 * Get the data for the Overview report
	*/
	public function get_bottom_sellers_data($sql_filter)
	{
		$sql = "SELECT
					p_name AS 'Product Name',
					COUNT(DISTINCT oi_u_id) AS 'Customers Ordering',
					COUNT(oi_id) AS 'Total Orders'
				FROM
					orderitem
				LEFT JOIN
					product ON oi_p_id = p_id
				LEFT JOIN
					p2cat ON p_id = p2cat_p_id
					". $sql_filter['joins'] ."
				WHERE
					oi_status = 'Confirmed'
					". $sql_filter['and'] ."
				GROUP BY
					p_name
				ORDER BY COUNT(DISTINCT oi_u_id) ASC
				LIMIT 0, 15
				;";
		$result = $this->db->query($sql)->result_array();
		//type cast
		$i = 1;
		foreach($result as $key => $row)
		{
			$result[$key]['Total Orders'] = (int)$row['Total Orders'];
			$result[$key]['Customers Ordering'] = (int)$row['Customers Ordering'];
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
				LEFT JOIN
					product ON oi_p_id = p_id
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
}
?>
