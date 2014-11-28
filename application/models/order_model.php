<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handle orders and ordering
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


class Order_model extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Add items into an order.
	 *
	 * @author GM
	 * @param Array $details	Some default values about the order
	 * @param Array $items		The items to be ordered. key is date(yyyy-mm-dd format. depriciating format in millis) and value is the quantity
	 * @return int		number of dates that the item was reserved for.
	 */
	 public function add_items($details, $items)
	 {
	 	log_message('debug', 'adding items to an order');
		$good_dates = 0;
	 	foreach($items as $time => $qty)
		{
			log_message('debug', 'ORDER: for the time '.$time);

			//safety checks
			$orderis['old_style_time'] = (is_numeric($time) ? 1 : 0);
			if ($orderis['old_style_time'] == 1)
			{
				//old style, using timestamps. order_recurring_model->create_auto_orders (and anything else) still uses this. TODO: depreciate.
				$orderdate = new DateTime(date("c", ($time/1000)));
				$orderis['in_past'] = (($time / 1000) < time() ? 1 : 0);
				$orderis['not_on_tues'] = (date('N', ($time / 1000)) != 2 ? 1 : 0);
			}
			else
			{
				//time is in new/better yyy-mm-dd format, so checks are different
				$orderdate = new DateTime($time);
				$orderis['in_past'] = ($time < date('Y-m-d') ? 1 : 0);
				$orderis['not_on_tues'] = ($orderdate->format('l') != 'Tuesday' ? 1 : 0);
			}
			//now if the checks failed
			if ($orderis['in_past'] == 1 || $orderis['not_on_tues'] == 1)
			{
				//get some data
				$message = "Something very strange just tried to happen, a member made an order that failed a safety check(s).
							<br>User id: [". $details['u_id'] ."]
							<br>Product: [". $details['p_id'] ."]
							<br>Current time is ". time() ."
							<br>Delivery time requested [". $time . "] (ms)
							<br>Delivery day requested [". $orderdate->format('l') . "] (weekday)
							<br>Qty: [". $qty ."]
							<br>
							<br>Submitted details output follows
							<br>
							". print_r($details, TRUE) ."
							<br>Items details output follows
							<br>
							". print_r($items, TRUE) ."
							<br>Results of safety checks. The order is...
							<br>
							". print_r($orderis, TRUE) ."

							<br>--email ends--";
				$eq[] = array('eq_email' => $this->config->item('dev_email'),
							  'eq_subject' => 'Fair Food Carlisle Error: Adding items to an order',
							  'eq_body' => $message);
				// send the email
				$this->load->model('emails_queue_model');
				$this->emails_queue_model->set_queue($eq);
				//don't order this item
				show_error('Produce can not be ordered for that date. Fair Food Carlisle system administrators have been notified.');
				continue;
			}

			//check stock, and reduce/simmer
			$sql = 'SELECT
						pc_id,
						p2pc_id,
						IF(p2pc_stock IS NULL, pc_max_qty, p2pc_stock) AS pc_max_qty
					FROM
						productcommitment
					LEFT JOIN
						p2pc ON pc_id = p2pc_pc_id AND p2pc_p_id = '. (int)$details['p_id'] .'
					WHERE
						(
							pc_p_id = '. (int)$details['p_id'] .'
							OR p2pc_pc_id IS NOT NULL
						)
					AND
						(
							((pc_period_start - INTERVAL pc_preseason_gap DAY) <= "'. $orderdate->format('Y-m-d') .'")
						OR
							(("'. date('Y-m-d', time()). '" + INTERVAL pc_predelivery_gap DAY) <= "'. $orderdate->format('Y-m-d') .'")
						)
					AND
						pc_period_end >= "'. $orderdate->format('Y-m-d') .'"
					LIMIT 1;
						';
			$pseason = $this->db->query($sql)->row_array();

			if (!$pseason)
			{
				log_message('debug', 'ORDER: could not check product seasons.');
			}

			if (isset($pseason['pc_max_qty']) && $pseason['pc_max_qty'] >= $qty)
			{
				$qty = (int)$qty; //safety against strangeness
				$newstock = $pseason['pc_max_qty'] - $qty;
				//update the stock
				if ($pseason['p2pc_id'] == '')
				{
					$sql = 'UPDATE
								productcommitment
							SET
								pc_max_qty = '. (int)$newstock .'
							WHERE
								pc_id = '. (int)$pseason['pc_id'] .'
							;';
				}
				else
				{
					//new season method
					$sql = 'UPDATE
								p2pc
							SET
								p2pc_stock = '. (int)$newstock .'
							WHERE
								p2pc_id = '. (int)$pseason['p2pc_id'] .'
							;';
				}
				$this->db->query($sql);

				//add the item to the order_item table
				$data = array(
					'oi_b_id' => NULL,
					'oi_u_id' => $details['u_id'],
					'oi_s_id' => $details['s_id'],
					'oi_p_id' => $details['p_id'],
					'oi_qty' => $qty,
					'oi_price' => ($details['unit_price'] * $qty),
					'oi_cost' => ($details['unit_cost'] * $qty),
					'oi_delivery_date' => $orderdate->format('Y-m-d 00:00:00'),
					'oi_ordered_date' => date('Y-m-d H:i:s', time()),
					'oi_status' => $details['status']
				);

				// did it come from an ongoing order or admin?
				if ( isset($details['source_or']) )
				{
					$data['oi_source_id'] = $details['source_or'];
				}
				elseif ( isset($details['source_id']) )
				{
					$data['oi_source_id'] = $details['source_id'];
				}
				if ( isset($details['source_type']) )
				{
					$data['oi_source_type'] = $details['source_type'];
				}

				$sql = $this->db->insert_string('orderitem', $data);
				$result = $this->db->query($sql);
				if (@$result)
				{
					log_message('debug', 'ORDER: okay');
					$good_dates++;
				}
				else
				{
					log_message('debug', 'ORDER: did not get added, sql error?');
				}
			}
			else
			{
				log_message('debug', 'ORDER: date isnt suitable');
				//that was a bad date, sorry luv.
			}
		}
		return $good_dates; //change this
	}


	/**
	 * Gets the orders, ordered by delivery_date so they can be viewed for a calendar.
	 *
	 * @author GM
	 * @param Array $params		fields to filter on
	 * @return Array			The orders
	 */
	public function get_orders($params)
	{
		$sql = 'SELECT
					oi_id,
					oi_u_id,
				';
		if (@$params['order'] && in_array('bg_name', $params['order']))
		{
			$sql .= 'u_title,
					 u_fname,
					 u_sname,
					 u_bg_id,
					 u_delivery_type,
					 u_addr_line1,
					 u_addr_line2,
					 u_addr_city,
					 u_addr_pcode,
					 bg_name,
				';
		}
		//if (@$params['order'] && in_array('oi_s_id', $params['order']))
		{
			$sql .= 's_name,
				';
		}
		$sql .= '	oi_b_id,
					oi_p_id,
					p_name,
					pu_plural,
					pu_short_plural,
					oi_qty,
					oi_price,
					oi_cost,
					oi_delivery_date,
					oi_ordered_date,
					oi_status
				FROM
					orderitem
				LEFT JOIN
					product ON oi_p_id = p_id
				LEFT JOIN
					productunit ON p_pu_id = pu_id
				LEFT JOIN
					user ON oi_u_id = u_id
				LEFT JOIN
				 	buyinggroup ON u_bg_id = bg_id
				';
		//if (@$params['order'] && in_array('oi_s_id', $params['order']))
		{
			$sql .= 'LEFT JOIN
						supplier ON oi_s_id = s_id
				';
		}
		$sql .= 'WHERE
					1=1 ';
		if (@$params['oi_u_id'])
		{
			$sql .= ' AND oi_u_id = '. $this->db->escape($params['oi_u_id']);
		}
		if (@$params['u_bg_id'])
		{
			$sql .= ' AND u_bg_id = '. $this->db->escape($params['u_bg_id']);
		}
		if (@$params['oi_s_id'])
		{
			$sql .= ' AND oi_s_id = '. $this->db->escape($params['oi_s_id']);
		}
		if (isset($params['oi_delivery_date']) && $params['oi_delivery_date'] != '1970-01-01')
		{
			$sql .= ' AND oi_delivery_date = '. $this->db->escape($params['oi_delivery_date']);
		}
		if (@$params['oi_status'])
		{
			$sql .= ' AND oi_status = '. $this->db->escape($params['oi_status']);
		}
		else
		{
			$sql .= ' AND
						oi_status != "Expired"
					  AND
					  	oi_status != "Cancelled"
					  AND
					  	oi_status != "Rejected" ';
		}
		$sql .= ' GROUP BY oi_id ';
		if (@$params['order'] && is_array($params['order']))
		{
			$sql .= ' ORDER BY '. implode(', ', $params['order']) .';';
		}
		elseif (@$params['order'])
		{
			$sql .= ' ORDER BY '. $this->db->escape($params['order']) .';';
		}
		else
		{
			$sql .= ' ORDER BY oi_delivery_date;';
		}

		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	/**
	 * Get orders to be display to the customer
	 *
	 * @author GM
	 * @param Array $params		fields to filter on
	 * @return Array			The orders
	 */
	public function get_customer_orders($params)
	{
		//first, a sepcial filter option requires us to check the db
		if (@$params['limit_dates'])
		{
			$sql = 'SELECT
						oi_delivery_date
					FROM
						orderitem
					';
			$sql .= 'WHERE
						1=1 ';
			if (@$params['oi_u_id'])
			{
				$sql .= ' AND oi_u_id = '. $this->db->escape($params['oi_u_id']);
			}
			if (@$params['oi_delivery_date'])
			{
				$sql .= ' AND oi_delivery_date = '. $this->db->escape($params['oi_delivery_date']);
			}
			if (@$params['awaiting_delivery'])
			{
				$sql .= ' AND oi_delivery_date >= NOW() ';
			}
			if (@$params['oi_status'] == 'all')
			{
			}
			elseif (@$params['oi_status'])
			{
				$sql .= ' AND oi_status = '. $this->db->escape($params['oi_status']);
			}
			else
			{
				$sql .= ' AND
							oi_status != "Cancelled"
						  AND
							oi_status != "Rejected" ';
			}
			$sql .= '
				GROUP BY
					oi_delivery_date
				ORDER BY oi_delivery_date
				LIMIT 0, '. (int)$params['limit_dates'] .';';

			$initial_data = $this->db->query($sql)->result_array();
			if (!$initial_data)
			{
				return false;
			}
			elseif (count($initial_data) > 1)
			{
				//sort out list of delivery dates that we want to find
			}
			else
			{
				//set up a delivery date to filter on
				$params['oi_delivery_date'] = $initial_data[0]['oi_delivery_date'];
			}
		}

		//now get the orders based on params...
		$sql = 'SELECT
					oi_id,
					oi_u_id,
					oi_b_id,
					oi_p_id,
					p_name,
					p_slug,
					pu_plural,
					pu_short_plural,
					oi_qty,
					oi_price,
					oi_cost,
					oi_source_id,
					oi_source_id AS oi_source_or,
					oi_source_type,
					oi_delivery_date,
					oi_ordered_date,
					oi_status
				FROM
					orderitem
				LEFT JOIN
					product ON oi_p_id = p_id
				LEFT JOIN
					productunit ON p_pu_id = pu_id
				';
		$sql .= 'WHERE
					1=1 ';
		if (@$params['oi_u_id'])
		{
			$sql .= ' AND oi_u_id = '. $this->db->escape($params['oi_u_id']);
		}
		if (@$params['oi_delivery_date'])
		{
			$sql .= ' AND oi_delivery_date = '. $this->db->escape($params['oi_delivery_date']);
		}
		if (@$params['awaiting_delivery'])
		{
			$sql .= ' AND oi_delivery_date >= NOW() ';
		}
		if (@$params['oi_status'] == 'all')
		{
		}
		elseif (@$params['oi_status'])
		{
			$sql .= ' AND oi_status = '. $this->db->escape($params['oi_status']);
		}
		else
		{
			$sql .= ' AND
						oi_status != "Cancelled"
					  AND
						oi_status != "Rejected" ';
		}
		$sql .= ' ORDER BY oi_delivery_date;';

		$data = $this->db->query($sql)->result_array();
		return $data;
	}



	/**
	 * Gets the orders, ordered by delivery_date so they can be viewed for a calendar. Grouped by product & status.
	 *
	 * @author GM
	 * @param int $s_id		The supplier in question
	 * @return Array		The orders
	 */
	public function get_supplier_orders($s_id)
	{
		$sql = 'SELECT
					COUNT(oi_id) AS o_customers,
					oi_p_id,
					p_name,
					pu_plural,
					pu_short_plural,
					SUM(oi_qty) as o_qty,
					SUM(oi_cost) as o_cost,
					oi_delivery_date,
					oi_status
				FROM
					orderitem
				LEFT JOIN
					product ON oi_p_id = p_id
				LEFT JOIN
					productunit ON p_pu_id = pu_id
				WHERE
					oi_s_id = '. (int)$s_id .'
				AND
					(oi_status = "Reserved"
					OR
					oi_status = "Confirmed")
				GROUP BY
					oi_delivery_date, oi_p_id, oi_status
				ORDER BY
					oi_delivery_date;';

		$data = $this->db->query($sql)->result_array();
		return $data;
	}


	/**
	 * Gets the orders for the group
	 *
	 * @author GM
	 * @param int $bg_id	The group in question
	 * @return Array		The orders
	 */
	public function get_group_orders($bg_id)
	{
		$sql = 'SELECT
					COUNT(oi_id) AS o_customers,
					COUNT(oi_p_id) AS o_products,
					COUNT(oi_s_id) AS o_suppliers,
					SUM(oi_price) as o_price,
					SUM(oi_cost) as o_cost,
					oi_delivery_date,
					oi_status
				FROM
					orderitem
				LEFT JOIN
					product ON oi_p_id = p_id
				LEFT JOIN
					productunit ON p_pu_id = pu_id
				LEFT JOIN
					supplier ON p_s_id = s_id
				LEFT JOIN
					user ON oi_u_id = u_id
				WHERE
					u_bg_id = '. (int)$bg_id .'
				AND
					(oi_status = "Reserved"
					OR
					oi_status = "Confirmed")
				GROUP BY
					oi_delivery_date, oi_p_id, oi_status, oi_s_id
				ORDER BY
					oi_delivery_date;';

		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	/**
	 * Get items that have been reserved but not confirmed. Group by user so we can put them in a bill.
	 *
	 * @author	GM
	 * @param	string $upto	items that were ordered before this date (so not items just added)
	 */
	public function get_unconfirmed_items($upto)
	{
		$sql = 'SELECT
					oi_id,
					oi_u_id,
					oi_p_id,
					oi_qty,
					oi_delivery_date
				FROM
					orderitem
				WHERE
					oi_ordered_date <= '. $this->db->escape($upto) .'
				AND
					oi_status = "Reserved";
					';
		return $this->db->query($sql)->result_array();
	}

	/**
	 * Change the status of an order item
	 */
	public function change_order_status($oi_id, $new_state)
	{
		$sql = 'UPDATE
					orderitem
				SET
					oi_status = '. $this->db->escape($new_state) .'
				WHERE
					oi_id = '. (int)$oi_id .'
				;';
		return $this->db->query($sql);
	}

	/*
	 * Get the order items up to a certain date that need to be put into a bill
	 */
	public function get_unbilled_items($delivery_date)
	{
		$sql = 'SELECT
					GROUP_CONCAT(oi_id) AS oi_ids,
					SUM(oi_price) AS oi_value,
					oi_u_id,
					oi_delivery_date
				FROM
					orderitem
				LEFT JOIN user
					ON oi_u_id = u_id
				WHERE
					oi_b_id IS NULL
				AND
					oi_delivery_date <= '. $this->db->escape($delivery_date) .'
				AND
					oi_status = "Confirmed"
				AND
					u_status = "Active"
				GROUP BY
					oi_u_id
				ORDER BY
					oi_price DESC
				;';
		return $this->db->query($sql)->result_array();
	}

	/*
	 * Get items that have been added to a specified bill
	 */
	public function get_bill_items($b_id)
	{
		$sql = 'SELECT
					oi_id,
					s_name,
					oi_p_id,
					p_name,
					pu_plural,
					pu_short_plural,
					oi_qty,
					oi_price,
					oi_cost,
					oi_delivery_date,
					oi_status
				FROM
					orderitem
				LEFT JOIN
					product ON oi_p_id = p_id
				LEFT JOIN
					productunit ON p_pu_id = pu_id
				LEFT JOIN
					supplier ON oi_s_id = s_id
				WHERE
					oi_b_id = '. (int)$b_id .'
				;';
		return $this->db->query($sql)->result_array();
	}

	/*
	 * Change all orders for a user that are a certain status
	 */
	public function update_user_statuses($u_id, $old_status, $new_status)
	{
		$sql = 'UPDATE
					orderitem
				SET
					oi_status = '. $this->db->escape($new_status) .'
				WHERE
					oi_u_id = '. (int)$u_id .'
				AND
					oi_status = '. $this->db->escape($old_status) .'
				;';
		return $this->db->query($sql);
	}


	/*
	 * Update the status of an order item
	 */
	public function update_status($oi_id, $new_status)
	{
		$sql = 'UPDATE
					orderitem
				SET
					oi_status = '. $this->db->escape($new_status) .'
				WHERE
					oi_id = '. (int)$oi_id .'
				LIMIT 1;';

		if ($this->db->query($sql)) {
			if ($new_status == 'Cancelled') {
				if ($stockItem = $this->_replace_stock($oi_id)) {
					return  $this->db->last_query();
				}
				return  $this->db->last_query();
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Sets orderitems status to Confirmed. If stock was not Reserved, it checks availability and takes it
	 *
	 * @param $u_id		The user to change the orders of.
	 * @param $oi_id	Optional, a single item to confirm.
	 */
	public function confirm_stock($u_id, $oi_id=null)
	{
		$sql = 'SELECT
					oi_id,
					oi_p_id,
					oi_qty,
					oi_status,
					oi_delivery_date,
					pc_id,
					pc_max_qty
				FROM
					orderitem
				LEFT JOIN
					productcommitment
				ON
					oi_p_id = pc_p_id
				AND
					pc_period_start <= oi_delivery_date
				AND
					pc_period_end >= oi_delivery_date
				WHERE
					oi_u_id = '. (int)$u_id .'
				AND
					(oi_status = "Reserved"
					 OR oi_status = "Expired"
					)
				';
		if (@$oi_id)
		{
			$sql .= ' AND oi_id = '. (int)$oi_id .' ';
		}

		$items = $this->db->query($sql)->result_array();

		if (@$items)
		{
			foreach($items as $item)
			{
				if ($item['oi_status'] == 'Reserved')
				{
					$this->update_status($item['oi_id'], 'Confirmed');
				}
				elseif (($item['oi_status'] == 'Expired') && ($item['pc_max_qty'] >= $item['oi_qty']))
				{
					//update stock first
					$this->load->model('products_model');
					$this->products_model->update_product_stock($item['oi_p_id'], '-', $item['oi_qty'], $item['oi_delivery_date']);
					$this->update_status($item['oi_id'], 'Confirmed');
				}
				else
				{
					$this->update_status($item['oi_id'], 'Unavailable');
				}
			}
		}
		return true;
	}

	/*
	 * Update the price and cost of a product, for orders not yet billed
	 */
	public function update_product_prices($p_id, $p_price=null, $p_cost=null)
	{
		if ($p_price == null && $p_cost == null)
		{
			//one of them needs to have been submitted.
			return FALSE;
		}

		$sql = 'UPDATE
					orderitem
				SET
				';
		if ($p_price != null)
		{
			$sql .= ' oi_price = oi_qty * '. $this->db->escape( (float)$p_price );
			if ($p_cost != null)
			{
				$sql .= ', ';
			}
		}
		if ($p_cost != null)
		{
			$sql .= ' oi_cost = oi_qty * '. $this->db->escape( (float)$p_cost );
		}
		$sql .= '
				WHERE
					oi_p_id = '. (int)$p_id .'
				AND
					oi_b_id IS NULL
				;';
		return $this->db->query($sql);
	}


	/* Some billing functions, potentiall should be in amodel of their own? */

	/**
	 * Creates a new bill, with no items in it
	 *
	 * @return $b_id int	The id of the bill that was created
	 */
	public function create_bill($data=null)
	{
		//these items start at 0.
		$data['b_price'] = 0;
		$data['b_cost'] = 0;

		//build and run the query
		$sql = $this->db->insert_string('bill', $data);
		$result = $this->db->query($sql);
		if ($result)
		{
			$b_id = $this->db->insert_id();
			return $b_id;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Checks all the items in a bill and recalulates values such as total price
	 * TODO: do it all in one SQL query.
	 */
	public function refresh_bill_data($b_id)
	{
		$sql = 'SELECT
					COUNT(oi_id) AS total_items,
					SUM(oi_price) AS total_price,
					SUM(oi_cost) AS total_cost
				FROM
					orderitem
				WHERE
					oi_b_id = '. $this->db->escape($b_id) .'
				GROUP BY
					oi_b_id
				LIMIT 1;';
		$result = $this->db->query($sql)->row_array();
		if (!$result)
		{
			return false;
		}

		//any adjustments to the bill?
		$sql = 'SELECT
					SUM(ba_price) AS adjust_price
				FROM
					bill_adjustment
				WHERE
					ba_b_id = '. $this->db->escape($b_id) .'
				GROUP BY
					ba_b_id
				LIMIT 1;';
		$adjusts = $this->db->query($sql)->row_array();

		if (@$adjusts)
		{
			$total_price = $result['total_price'] + $adjusts['adjust_price'];
		}
		else
		{
			$total_price = $result['total_price'];
		}

		$sql = 'UPDATE
					bill
				SET
					b_items = '. $this->db->escape( $result['total_items'] ) .',
					b_price = '. $this->db->escape( $total_price ) .',
					b_cost = '. $this->db->escape( $result['total_cost'] ) .'
				WHERE
					b_id = '. (int)$b_id .'
				;';

		return $this->db->query($sql);
	}

	/**
	 * Adds an item, or array of items to a bill. It finishes by calling refresh_bill_data
	 *
	 * @author GM
	 * @param $b_id	int	the id of the bill
	 * @param $oi_id mixed	the id of the item to add, or an array of ids
	 */
	public function add_to_bill($b_id, $oi_id)
	{
		$sql = 'UPDATE
					orderitem
				SET
					oi_b_id = '. (int)$b_id .'
				WHERE
					';
		if (is_array($oi_id))
		{
			$list = array();
			foreach($oi_id as $item)
			{
				$list[] = $this->db->escape($item);
			}
			$sql .= 'oi_id IN ('. implode(',', $list) .')
			';
		}
		else
		{
			$sql .= 'oi_id = '. $this->db->escape($oi_id) .'
			';
		}
		$sql .= ';';

		$result = $this->db->query($sql);
		//all good?
		if ($result)
		{
			$this->refresh_bill_data($b_id);
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get a list of bills in the system, based on a search
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_bills($params=null)
	{
		if ( !$params) $params = array('order' => 'b_id');
		if ( ! in_array(@$params['order'], array('customer_name', 'bg_name', 'b_items', 'b_price', 'b_status')) ) $params['order'] = 'b_id';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'desc';

		$sql = 'SELECT
					b_id,
					CONCAT(user.u_title, " ", user.u_fname, " ", user.u_sname) AS customer_name,
					bg_name,
					b_items,
					b_price,
					b_status
				FROM
					bill
				LEFT JOIN
					user ON b_u_id = u_id
				LEFT JOIN
					buyinggroup ON u_bg_id = bg_id
				';

		$sql .= ' WHERE 1 = 1 ';

		if (@$params['b_status'])
			$sql .= ' AND b_status = ' . $this->db->escape($params['b_status']) . ' ';

		if (isset($params['b_u_id']))
			$sql .= ' AND b_u_id = ' . $this->db->escape($params['b_u_id']) . ' ';

		if (isset($params['b_id']) && $params['b_id'] != '')
			$sql .= ' AND b_id = ' . $this->db->escape($params['b_id']) . ' ';

		if (isset($params['bg_id']) && $params['bg_id'] != '')
			$sql .= ' AND bg_id = ' . $this->db->escape($params['bg_id']) . ' ';


		// set order by clause
		$sql .= ' ORDER BY ' . $params['order'] . ' ' . $params['sort'] . ' ';

		// if a limit has been set
		if (@$params['limit'] != FALSE)
			$sql .= ' LIMIT ' . (int)$params['start'] . ', ' . (int)$params['limit'];

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get the total count of bills, ignoring the limit in filter.
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return int	the number of bills found
	 */
	public function get_total_bills($params=null)
	{
		if ( !$params) $params = array('order' => 'b_id');
		if ( ! in_array(@$params['order'], array('b_items', 'b_price', 'b_status')) ) $params['order'] = 'b_id';

		$sql = 'SELECT
					COUNT(b_id) AS total
				FROM
					bill';
		if (isset($params['bg_id']))
		{
			$sql .= ' LEFT JOIN user ON u_id = b_u_id ';
		}

		$sql .= ' WHERE 1 = 1 ';

		if (@$params['b_status'])
			$sql .= ' AND b_status = ' . $this->db->escape($params['b_status']) . ' ';

		if (isset($params['b_u_id']))
			$sql .= ' AND b_u_id = ' . $this->db->escape($params['b_u_id']) . ' ';

		if (isset($params['b_id']))
			$sql .= ' AND b_id = ' . $this->db->escape($params['b_id']) . ' ';

		if (isset($params['bg_id']) && $params['bg_id'] != '')
			$sql .= ' AND u_bg_id = ' . $this->db->escape($params['bg_id']) . ' ';

		$result = $this->db->query($sql)->row_array();
		return $result['total'];
	}


	/**
	 * Get a single bill, and many details about it
	 *
	 * @author GM
	 * @param int $bg_id		the bill id requested
	 * @return mixed		DB result row on success, FALSE on failure
	 */
	public function get_bill($b_id)
	{
		$sql = 'SELECT
					b_id,
					b_u_id,
					b_items,
					b_price,
					b_cost,
					b_payment_method,
					b_status,
					b_note,
					CONCAT(user.u_title, " ", user.u_fname, " ", user.u_sname) AS customer_name,
					user.u_email AS customer_email,
					user.u_telephone AS customer_phone,
					u_bg_id
				FROM
					bill
				LEFT JOIN
					user ON b_u_id = u_id
				WHERE b_id = '. (int)$b_id.'
				';
		if ($this->session->userdata('u_type') == 'Member')
		{
			$sql .= ' AND b_u_id = '. $this->session->userdata('u_id');
		}
		$sql .= ' LIMIT 1 ';

		return $this->db->query($sql)->row_array();
	}

	/**
	 * Get items that adjust the bill totals
	 */
	public function get_bill_adjustments($b_id)
	{
		$sql = 'SELECT
					ba_id,
					ba_description,
					ba_price
				FROM
					bill_adjustment
				WHERE
					ba_b_id = '. (int)$b_id .'
				;';
		return $this->db->query($sql)->result_array();
	}


	/**
	 *	Add an adjustment item to the bill. Refresh the bill data afterwards
	 */
	public function add_bill_adjustment($data)
	{
		$sql = 'INSERT INTO
					bill_adjustment
				SET
					ba_b_id = '. $this->db->escape($data['ba_b_id']) .',
					ba_description = '. $this->db->escape($data['ba_description']) .',
					ba_price = '. $this->db->escape($data['ba_price']) .',
					ba_applied_date = NOW(),
					ba_applied_u_id = '. $this->db->escape($data['ba_applied_u_id']) .'
				;';
		$result = $this->db->query($sql);
		if (@$result)
		{
			$ba_id = $this->db->insert_id();
			$this->refresh_bill_data($data['ba_b_id']);
			return $ba_id;
		}
	}

	/**
	 * Change the status of a bill
	 *
	 * @author GM
	 * @param $b_id	int	the id of the bill
	 * @param $new_status the status to change it to
	 */
	public function change_bill_status($b_id, $new_status)
	{
		$sql = 'UPDATE
					bill
				SET
					b_status = '. $this->db->escape($new_status) .'
				WHERE
					b_id = '. (int)$b_id .'
					;';

		return $this->db->query($sql);
	}

	/**
	 * Marks a bill as paid
	 *
	 * @author GM
	 * @param $b_id	int	the id of the bill
	 * @param $payment_type	the method used
	 */
	public function mark_bill_paid($b_id, $payment_type)
	{
		$sql = 'UPDATE
					bill
				SET
					b_payment_method = '. $this->db->escape($payment_type) .',
					b_payment_date = NOW(),
					b_status = "Paid"
				WHERE
					b_id = '. (int)$b_id .'
					;';

		$result = $this->db->query($sql);
		return $result;
	}

	/**
	 * Tries to auto pay a bill with Go Cardless, o e-mails the user, and updates the bill status as relevant
	 *
	 * @author GM
	 * @param $b_id	int	the id of the bill
	 * @param $u_id	int	the id of the user that needs to pay
	 * @param $b_price	float	the price of the bill that we want to debit from them
	 */
	public function make_bill_due_and_pay($b_id, $u_id, $b_price)
	{
		//error avoidance
		if ($b_price <= 0)
		{
			return array('success'=>FALSE, 'description'=>'Amount due must be positive, Bill #'.$b_id.' not marked as due.');
		}

		//have they authorised Go Cardless in the past?
		$pre_auth_id = $this->gc_get_preauth_id($u_id);
		if (!$pre_auth_id)
		{
			//set bill as due, e-mail them asking to pay
			$result = $this->change_bill_status($b_id, "Pending");

			if ($result)
			{
				//email them about it
				$this->load->model('users_model');
				$member = $this->users_model->get_user($u_id);
				$subject = config_item('site_name') .' Payment is due. ';
				$message = '<p>Hello '. $member['u_title'] .' '. $member['u_fname'] .' '. $member['u_sname'] .',</p>';
				$message .= '<p>Bill #'. $b_id .', is now ready for you to pay.';
				$message .= '<br />The amount for your recent delivery is <em>&pound;'.$b_price.'</em>.';
				$message .= '<br />You can view details of this bill and pay online at <a href="'. site_url('bill/view/'.$b_id) .'">'. site_url('bill/view/'.$b_id) .'</a>.</p>';
				$message .= '<p>Thank you, <br /> '. config_item('site_name') .'</p>';

				$eq[] = array('eq_email' => $member['u_email'],
							  'eq_subject' => $subject,
							  'eq_body' => $message);
				// load emails queue model
				$this->load->model('emails_queue_model');
				$this->emails_queue_model->set_queue($eq);

				return array('success'=>TRUE, 'description'=>'The member has been notified that the bill is due.');
			}
			else
			{
				return array('success'=>FALSE, 'description'=>'The bill status could not be updated.');
			}
		}
		else
		{
			//is Go Cardless working & enough?
			require_once( APPPATH . '/third_party/GoCardless.php' );
			GoCardless::$environment = config_item('gocardless_environment');
			GoCardless::set_account_details(config_item('gocardless_account'));
			$pre_auth = GoCardless_PreAuthorization::find( $pre_auth_id );

			if (isset($pre_auth) && $pre_auth->status == 'active' && $pre_auth->remaining_amount >= $b_price)
			{
				//try paying it
				$bill_details = array(
				  'name'    => 'Bill #'. $b_id,
				  'amount'  => $b_price
				);
				$gc_bill = $pre_auth->create_bill($bill_details);
				if ($gc_bill)
				{
					//mark it as paid
					$result = $this->mark_bill_paid($b_id, 'Go Cardless Pre-Auth');
					if (!$result)
					{
						return array('success'=>FALSE, 'description'=>'Bill '.$b_id.' was paid, but an error caused it not to be marked as such.');
					}
					else
					{
						return array('success'=>TRUE, 'description'=>'The bill was paid through Go Cardless pre-authorisation.');
					}
				}
			}

			//set bill as due, e-mail them asking to pay, because GC couldn't be used (or was not enough)
			$result = $this->change_bill_status($b_id, "Pending");

			if ($result)
			{
				//email them about it
				$this->load->model('users_model');
				$member = $this->users_model->get_user($u_id);
				$subject = config_item('site_name') .' Payment is due. ';
				$message = '<p>Hello '. $member['u_title'] .' '. $member['u_fname'] .' '. $member['u_sname'] .',</p>';
				$message .= '<p>Bill #'. $b_id .', is now ready for you to pay.';
				$message .= '<br />The amount for your recent delivery is <em>&pound;'.$b_price.'</em>.';
				$message .= '<br />On this occasion, we were unable to debit the amount from your bank through the Go Cardless system.';
				$message .= '<br />You can view details of this bill and pay online at <a href="'. site_url('bill/view/'.$b_id) .'">'. site_url('bill/view/'.$b_id) .'</a>.</p>';
				$message .= '<p>Thank you, <br /> '. config_item('site_name') .'</p>';

				$eq[] = array('eq_email' => $member['u_email'],
							  'eq_subject' => $subject,
							  'eq_body' => $message);
				// load emails queue model
				$this->load->model('emails_queue_model');
				$this->emails_queue_model->set_queue($eq);

				return array('success'=>TRUE, 'description'=>'The member has been notified that the bill is due.');
			}
			else
			{
				return array('success'=>FALSE, 'description'=>'The bill status could not be updated.');
			}
		}
	}


	/**
	 * Saves the pre-authorization resource_id from Go Cardless.
	 *
	 * @author GM
	 * @param $u_id int of the user to update
	 * @param $r_id String the id we get from Go Cardless when the user set up the pre-authorization
	 */
	public function gc_save_preauth_id($u_id, $r_id)
	{
		$sql = 'UPDATE
					user
				SET
					u_gc_preauth_id = '. $this->db->escape($r_id) .'
				WHERE
					u_id = '. (int)$u_id .'
					;';

		return $this->db->query($sql);
	}


	/**
	 * Gets the pre-authorization resource_id from Go Cardless. This is needed to charge the user without asking them.
	 *
	 * @author GM
	 * @param $u_id int of the user to bill
	 * @return $r_id String the id we got from Go Cardless
	 */
	public function gc_get_preauth_id($u_id)
	{
		$sql = 'SELECT
					u_gc_preauth_id
				FROM
					user
				WHERE
					u_id = '. (int)$u_id .'
					;';

		$data = $this->db->query($sql)->row_array();
		return $data['u_gc_preauth_id'];
	}

	/**
	 * Add a note to an order.
	 *
	 * @author GM
	 * @param Array $details	Some values about the note
	 * @return int	confirmation
	 */
	 public function add_note($data)
	 {

	 	//prep data
	 	$orderdate = new DateTime($data['on_delivery_date']);
		$data['on_delivery_date'] =$orderdate->format('Y-m-d 00:00:00');
		$data['on_added_on'] = date('Y-m-d H:i:s', time());

		//add the note to the order_note table
		$sql = $this->db->insert_string('order_note', $data);
		$result = $this->db->query($sql);

		if ($result != null)
		{
			log_message('debug', 'Order note added.');
		}
		else
		{
			log_message('debug', 'Order note: did not get added, sql error?');
		}
		return $result; //change this
	}


	/**
	 * Gets the notes sorted by delivery_date so they can be viewed for a calendar.
	 *
	 * @author GM
	 * @param Array $where_extra		fields to filter on
	 * @return Array			The order notes
	 */
	public function get_notes($where_extra = null)
	{
		$sql = 'SELECT
					on_id,
					on_u_id,
					on_delivery_date,
					on_text,
					on_added_on
				FROM
					order_note
				WHERE
					1=1 ';
		if ($where_extra != null)
		{
			$sql .= $where_extra;
		}
		$sql .= ' ORDER BY on_delivery_date;';

		$result = $this->db->query($sql)->result_array();

		//sort into dates
		$data = array();
		foreach($result as $row)
		{
			$data[ $row['on_delivery_date'] ][] = $row;
		}


		return $data;
	}

	/**
	 * Update stock through cancelled orders.
	 *
	 * @var $order_id - int - The order id
	 * @version 15-08-2014
	 */
	private function _replace_stock($order_id) {
		$sql = "UPDATE
					p2pc
				LEFT JOIN
					productcommitment AS prodcom
						ON prodcom.pc_id = p2pc.p2pc_pc_id
				LEFT JOIN
					product AS prod
						ON prod.p_id = p2pc.p2pc_p_id
				LEFT JOIN
					orderitem AS orderi
						 ON orderi.oi_p_id = prod.p_id
				SET
					p2pc.p2pc_stock = p2pc.p2pc_stock + orderi.oi_qty
				WHERE
					(
						prodcom.pc_p_id = prod.p_id
						OR p2pc_pc_id IS NOT NULL
					)
				AND
					(
						((pc_period_start - INTERVAL pc_preseason_gap DAY) <= orderi.oi_ordered_date)
					OR
						(('" . date('Y-m-d H:i:s', time()) . "' + INTERVAL pc_predelivery_gap DAY) <= orderi.oi_ordered_date)
					)
				AND
					pc_period_end >= orderi.oi_ordered_date
				AND
					orderi.oi_id = " . $order_id . "
				;";
			$this->db->query($sql);
		return $this->db->affected_rows();
	}


}

/* End of file. */
