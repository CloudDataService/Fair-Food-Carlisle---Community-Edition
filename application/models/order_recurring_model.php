<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handle recurring orders
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


class Order_recurring_model extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Add a new schedule of recurring order
	 *
	 * @author GM
	 * @param Array $details	Some values about the order item and recurringness
	 * @return int		number of dates that the item was reserved for.
	 */
	public function add_schedule($details)
	{
		//add some fixed details
		$details['or_status'] = 'Pending';
		if ($details['or_frequency'] != 'weekly' && $details['or_frequency'] != 'monthly')
		{
			$details['or_frequency'] = 'fortnightly';
		}

		$sql = $this->db->insert_string('order_recurring', $details);
		$result = $this->db->query($sql);
		if (@$result)
		{
			return 1;
		}
		return false;

	}

	public function get_single_schedule($or_id)
	{
		$sql = 'SELECT
					or_id,
					or_u_id,
					or_s_id,
					or_p_id,
					or_qty,
					or_frequency,
					or_status,
					or_started_date,
					or_latest_date,
					or_finished_date
				FROM
					order_recurring
				WHERE
					or_id = "'. (int)$or_id .'"
				LIMIT 1;';
		return $this->db->query($sql)->row_array();
	}

	/**
	 * Get recurring_orders
	 *
	 * @author GM
	 * @param Array $params		fields to filter on
	 * @return Array			The orders
	 */
	public function get_recurring_orders($params)
	{
		$sql = 'SELECT
					or_id,
					or_u_id,
					or_s_id,
					or_p_id,
					p_name,
					pu_plural,
					pu_short_plural,
					or_qty,
					or_frequency,
					or_status,
					or_started_date,
					or_latest_date,
					or_finished_date
				FROM
					order_recurring
				LEFT JOIN
					product ON or_p_id = p_id
				LEFT JOIN
					productunit ON p_pu_id = pu_id
				';
		$sql .= 'WHERE
					1=1 ';
		if (@$params['or_u_id'])
		{
			$sql .= ' AND or_u_id = '. $this->db->escape($params['or_u_id']);
		}
		if (@$params['oi_delivery_date'])
		{
			$sql .= ' AND oi_delivery_date = '. $this->db->escape($params['oi_delivery_date']);
		}
		if (@$params['awaiting_delivery'])
		{
			$sql .= ' AND oi_delivery_date >= NOW() ';
		}
		if (@$params['or_status'] == 'all')
		{
		}
		elseif (@$params['or_status'])
		{
			$sql .= ' AND or_status = '. $this->db->escape($params['or_status']);
		}
		else
		{
			$sql .= ' AND
						or_status != "Cancelled"
					';
		}
		$sql .= ' ORDER BY or_latest_date ASC, or_frequency ASC, or_started_date ASC;';

		$data = $this->db->query($sql)->result_array();
		return $data;
	}


	/**
	 * Cancel a recurring order
	 *
	 * @author GM
	 * @param int $or_id 	The or id to confirm
	 * @param int $u_id 	The id of the user the or belongs to
	 * @param String $new_status	Optional. The new status desired. If not provided, then the new status will be based on which user is logged in.
	 * @return Boolean		TRUE for success
	 */
	public function cancel_schedule($or_id, $u_id, $new_status=null)
	{
		//who is making the update?
		if ($new_status != null)
		{
			$status = $new_status;
		}
		else if ($u_id == $this->session->userdata('u_id'))
		{
			$status = "Stopped";
		}
		else
		{
			$status = "Finished";
		}

		//update the or
		$sql = 'UPDATE
					order_recurring
				SET
					or_status = "'. $status .'",
					or_finished_date = "'. date('Y-m-d') .'"
				WHERE
					or_id = "'. (int)$or_id .'"
					AND or_u_id = "'. (int)$u_id .'"
				LIMIT 1;';
		$result = $this->db->query($sql);
		if (!$result)
		{
			return FALSE;
		}

		//all good, thank you
		return true;
	}


	/**
	 * Confirm a recurring order, and auto-create the first few orders
	 *
	 * @author GM
	 * @param int $or_id 	The or id to confirm
	 * @param int $u_id 	The id of the user the or belongs to
	 * @return Array			The orders
	 */
	public function confirm_schedule($or_id, $u_id)
	{
		//update the or to set it as confirmed
		$sql = 'UPDATE
					order_recurring
				SET
					or_status = "Confirmed"
				WHERE
					or_id = "'. (int)$or_id .'"
					AND or_u_id = "'. (int)$u_id .'"
				LIMIT 1;';
		$result = $this->db->query($sql);
		if (!$result)
		{
			return FALSE;
		}

		//auto-order the first few
		$result = $this->create_auto_orders($or_id);

		//all good, thank you
		return true;
	}

	/**
	 * Look at an order_recurring, and create order_items in the future if needed
	 * TODO: remove all thoughts of milliseconds here, use a yyyy-mm-dd string.
	 *
	 * @author	GM
	 * @param	int $or_id	The id of the or
	 * @param	Array $or	(optional) data about the OR
	 * @return	int	success or fail?
	 */
	public function create_auto_orders($or_id, $or=null)
	{
	 	//get info about the or
		if ($or == null)
		{
			$or = $this->get_single_schedule($or_id);
		}
		if (!$or)
		{
			return false;
		}

		//work out the next date
		if ($or['or_latest_date'] != null)
		{
			$next_date = strtotime($or['or_latest_date']);

			//don't do the last date an order was made, do the next one they want
			if ($or['or_frequency'] == 'weekly')
			{
				$next_date = strtotime('+1 week', $next_date);
			}
			else if ($or['or_frequency'] == 'fortnightly')
			{
				$next_date = strtotime('+2 weeks', $next_date);
			}
			else if ($or['or_frequency'] == 'monthly')
			{
				$next_date = $this->_calculate_next_month($next_date);
			}
		}
		else
		{
			$next_date = strtotime($or['or_started_date']);
		}

		//just get the date from the time - new and better than timestamps (which are over sensitive)
		//$next_date = date('Y-m-d', $next_date);

		$order_date_limit = strtotime(config_item('recuring_order_buffer'));
		$order_items = array();
		$i = 1;
		//TODO: remove the need for millisecs inside this loop
		while($i < 30) //avoid an infinate loop, thats crazy
		{
			//do we want to create orders this far in the future?
			if ( $order_date_limit < $next_date)
			{
				break;
			}
			else
			{
				//add date to the order, so we can order it
				$millisecs = ($next_date*1000);
				//safety check we don't order in the past
				if ($millisecs >= (time()*1000))
				{
					$order_items[ "$millisecs" ] = $or['or_qty'];
				}
				$last_date = $next_date; //if we break out, remember the last one we did (for updating or table
			}

			//whats the next date to make an order for?
			if ($or['or_frequency'] == 'weekly')
			{
				$next_date = strtotime('+1 week', $next_date);
			}
			else if ($or['or_frequency'] == 'fortnightly')
			{
				$next_date = strtotime('+2 weeks', $next_date);
			}
			else if ($or['or_frequency'] == 'monthly')
			{
				$next_date = $this->_calculate_next_month($next_date);
			}
			$i++;
		}

		//okay, were there any dates we wanted to make orders for?
		if ($order_items == null)
		{
			return 1;
		}
		else
		{
			//as we're ordering, lets check the latest price
			$sql = 'SELECT
						p_price,
						p_cost
					FROM
						product
					WHERE
						p_id = "'. (int)$or['or_p_id'] .'"
					LIMIT 1;';
			$product = $this->db->query($sql)->row_array();
			if (!$product)
			{
				return -1; //ah, no product to be ordered!
			}

			//prepare data and make orders
			$this->load->model('order_model');
			$details = array('u_id' => $or['or_u_id'],
							 'p_id' => $or['or_p_id'],
							 's_id' => $or['or_s_id'],
							 'unit_price' => $product['p_price'],
							 'unit_cost' => $product['p_cost'],
							 'source_or' => $or['or_id'],
							 'status' => 'Confirmed'
							 );
			$ordered = $this->order_model->add_items($details, $order_items);

			//update the order_recurring, so we know we've made more orders
			$sql = 'UPDATE
						order_recurring
					SET
						or_latest_date = "'. date('Y-m-d', $last_date) .'"
					WHERE
						or_id = "'. (int)$or_id .'"
					LIMIT 1;';
			$result = $this->db->query($sql);
			if (!$result)
			{
				return FALSE;
			}

			return $ordered;
		}
	}


	/**
	 * Calculate a suitable date in a months time
	 **/
	private function _calculate_next_month($next_date)
	{
		// We'll look at a month's time. If it's a $Tuesday, yay. Otherwise get the Tuesday before/after and see which is closest.
		// TODO: replace 'Tuesday' with the allowed buying group delivery day
		$next_date = strtotime('+1 month', $next_date);
		if ( date('l', $next_date) != 'Tuesday' )
		{
			$next_plus = strtotime('next Tuesday', $next_date);
			$next_back = strtotime('previous Tuesday', $next_date);
			//check they are both actually in the next month and not slipped over
			if ( date('m', $next_plus) != date('m', $next_date) )
			{
				$next_date = $next_back;
			}
			else if ( date('m', $next_back) != date('m', $next_date) )
			{
				$next_date = $next_plus;
			}
			else
			{
				//ok, we'll have to see which one is closest to the date we wanted
				if ( ($next_date - $next_back) < ($next_plus - $next_date) )
				{
					$next_date = $next_back; //this waqs closest
				}
				else
				{
					$next_date = $next_plus;
				}
			}
		}
		//else $next_date was Tuesday so all is good
			return $next_date;
	}

}
