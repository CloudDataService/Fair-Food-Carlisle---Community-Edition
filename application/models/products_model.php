<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handle products within the system
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


class Products_model extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Look for a product, and return simple details about it's existence and a category it's in(preferably the one requested).
	 *
	 * @author GM
	 * @param String $p_slug	The slug that must match the product
	 * @param String $cat_slug	The slug of a category to check the product is in
	 * @return mixed	DB result row containing basic details about the product, and a category it is in.
	 */
	 public function find_product($p_slug, $cat_slug=null)
	 {
		if (@$cat_slug)
		{
			$sql = 'SELECT
						product.p_id,
						product.p_slug,
						product.p_status,
						category.cat_slug
					FROM
						product
					LEFT JOIN p2cat
						ON product.p_id = p2cat.p2cat_p_id
					LEFT JOIN category
						ON p2cat.p2cat_cat_id = category.cat_id
					WHERE
						product.p_status = "Active"
					AND
						product.p_slug = ?
					AND
						category.cat_slug = ?
					LIMIT 1;';
			$product = $this->db->query($sql, array($p_slug, $cat_slug))
							->row_array();
		}

		if (@$product)
		{
			return $product;
		}

		//is it in another/any cat?
		$sql = 'SELECT
					product.p_id,
					product.p_slug,
					category.cat_slug
				FROM
					product
				LEFT JOIN p2cat
					ON product.p_id = p2cat.p2cat_p_id
				LEFT JOIN category
					ON p2cat.p2cat_cat_id = category.cat_id
				WHERE
					product.p_status = "Active"
				AND
					product.p_slug = ?
				LIMIT 1;';
		$product = $this->db->query($sql, $p_slug)
						->row_array();
		return $product; //whether you found one or not
	 }

	/*
	 * Get products in a certain category
	 */
	public function get_category_products($cat_id, $params=array())
	{
		$sql = 'SELECT
					p_id,
					p_slug,
					p_image,
					p_name,
					s_name,
					p_description,
					p_price,
					pu_short_single
				FROM
					product
				LEFT JOIN p2cat
					ON product.p_id = p2cat.p2cat_p_id
				LEFT JOIN supplier
					ON p_s_id = s_id
				LEFT JOIN productunit
					ON p_pu_id = pu_id
				WHERE
					p_status = "Active"
				AND
					p2cat_cat_id = ?
				';
		if (isset($params['random']) && $params['random'] == TRUE)
		{
			$sql .= ' ORDER BY RAND() ';
		}
		else
		{
			$sql .= ' ORDER BY p_page_order ASC, p_name ASC ';
		}
		if (isset($params['limit']))
		{
			$sql .= ' LIMIT 0,'. (int)$params['limit'];
		}
		return $this->db->query($sql, $cat_id)
						->result_array();
	}

	/**
	 * Get single product, full detail
	 *
	 * @author GM
	 * @param string $cat_id		the product id requested(int) or the slug of the product(must not be int)
	 * @return mixed		DB result row on success, FALSE on failure
	 **/
	public function get_product_full($p_id=null, $p_slug=null)
	{
		$sql = 'SELECT
					p_id,
					p_page_order,
					p_status,
					p_slug,
					p_code,
					p_s_id,
					p_name,
					p_description,
					p_pu_id,
					pu_single,
					pu_plural,
					pu_short_single,
					pu_short_plural,
					p_price,
					p_cost,
					p_stock_warning,
					p_image,
					s_id AS supplier_id,
					s_name AS supplier_name,
					s_image AS supplier_image,
					s_description AS supplier_description
				FROM
					product
				LEFT JOIN
					productunit ON p_pu_id = pu_id
				LEFT JOIN
					supplier ON p_s_id = s_id
				WHERE
					1 = 1';
		if ($p_id)
		{
			$sql .= ' AND p_id = '. (int)$p_id;
		}
		else
		{
			$sql .= ' AND p_slug = '. $this->db->escape($p_slug) .'
					  AND p_status = "Active"';
		}

		$product = $this->db->query($sql, $p_id)
						->row_array();

		if ($product)
		{
			$product['categories'] = $this->get_product_categories($product['p_id']);
			$product['commitments'] = $this->get_product_commitments($product['p_id']);
		}

		return $product;
	}

	/**
	 * Get a list of products in the system
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_products($params=null)
	{
		if ( !$params) $params = array('order' => 'p_id');
		if ( ! in_array(@$params['order'], array('p_id', 'p_page_order', 'p_name', 'p_slug')) ) $params['order'] = 'p_page_order';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'asc';

		$sql = 'SELECT
					p_id,
					p_page_order,
					p_slug,
					p_code,
					p_status,
					p_name
				';
		if (element('include_supplier', $params) != null)
		{
			$sql .= ', s_name ';
		}
		if (element('extended_detail', $params) != null)
		{
			$sql .= ', p_price, p_image ';
		}

		$sql .= '
				FROM
					product
				';
		if (element('include_supplier', $params) != null)
		{
			$sql .= 'LEFT JOIN
						supplier ON p_s_id = s_id ';
		}

		if (isset($params['p_cat_id']))
		{
			$sql .= 'LEFT JOIN
						p2cat ON p_id = p2cat_p_id ';
			if ($params['p_cat_id'] != 'none')
			{
				$sql .= ' AND p2cat_cat_id = '. (int)$params['p_cat_id'] .' ';
			}
		}

		$sql .= ' WHERE 1 = 1 ';


		if (element('p_name', $params) != null)
		{
			$sql .= ' AND p_name LIKE "%' . $this->db->escape_like_str($params['p_name']) . '%" ';
		}

		if (@$params['p_status'])
		{
			$sql .= ' AND p_status = ' . $this->db->escape($params['p_status']) . ' ';
		}
		elseif (!isset($params['p_status']))
		{
			//no search specificaly made, hide removed until they search
			$sql .= ' AND p_status != "Removed" ';
		}

		if (@$params['image_state'] == 'Yes')
			$sql .= ' AND p_image IS NOT NULL';
		if (@$params['image_state'] == 'No')
			$sql .= ' AND p_image IS NULL';

		if (isset($params['p_cat_id']) && $params['p_cat_id'] != '' && $params['p_cat_id'] == 'none')
		{
			$sql .= ' AND p2cat_cat_id IS NULL ';
		}
		else if (isset($params['p_cat_id']) && $params['p_cat_id'] != '')
		{
			$sql .= ' AND p2cat_cat_id IS NOT NULL ';
		}

		if (isset($params['p_s_id']))
		{
			$sql .= ' AND p_s_id = '. (int)$params['p_s_id'] .' ';
		}

		// set order by clause
		$sql .= ' ORDER BY ' . $params['order'] . ' ' . $params['sort'] . '
					 , p_page_order ASC, p_name ASC';

		// if a limit has been set
		if (@$params['limit'] != FALSE)
			$sql .= ' LIMIT ' . (int)$params['start'] . ', ' . (int)$params['limit'];

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get the total count of products, ignoring the limit in filter.
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return int	the number of products found
	 */
	public function get_total_products($params=null)
	{
		if ( !$params) $params = array('order' => 'p_id');
		if ( ! in_array(@$params['order'], array('p_name', 'p_slug')) ) $params['order'] = 'p_id';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'desc';

		$sql = 'SELECT
					COUNT(p_id) AS total
				FROM
					product
				';

		if (isset($params['p_cat_id']))
		{
			$sql .= 'LEFT JOIN
						p2cat ON p_id = p2cat_p_id ';
			if ($params['p_cat_id'] != 'none')
			{
				$sql .= ' AND p2cat_cat_id = '. (int)$params['p_cat_id'] .' ';
			}
		}

		$sql .= ' WHERE 1 = 1 ';

		if (@$params['p_status'])
		{
			$sql .= ' AND p_status = ' . $this->db->escape($params['p_status']) . ' ';
		}
		elseif (!isset($params['p_status']))
		{
			//no search specificaly made, hide removed until they search
			$sql .= ' AND p_status != "Removed" ';
		}

		if (@$params['image_state'] == 'Yes')
			$sql .= ' AND p_image IS NOT NULL';
		if (@$params['image_state'] == 'No')
			$sql .= ' AND p_image IS NULL';

		if (isset($params['p_cat_id']) && $params['p_cat_id'] != '' && $params['p_cat_id'] == 'none')
		{
			$sql .= ' AND p2cat_cat_id IS NULL ';
		}
		elseif (isset($params['p_cat_id']) && $params['p_cat_id'] != '')
		{
			$sql .= ' AND p2cat_cat_id IS NOT NULL ';
		}

		if (isset($params['p_s_id']))
		{
			$sql .= ' AND p_s_id = '. (int)$params['p_s_id'] .' ';
		}

		$result = $this->db->query($sql)->row_array();
		return $result['total'];
	}

	/**
	 * Get the categories attached to a certain product
	 *
	 * @author GM
	 * @param int	The id of the product
	 * @return mixed	Array of categories
	 **/
	public function get_product_categories($p_id)
	{
		$sql = 'SELECT
					cat_id,
					cat_name
				FROM
					category
				LEFT JOIN p2cat
					ON cat_id = p2cat_cat_id
				WHERE
					p2cat_p_id = '. (int)$p_id;
		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get the allowed product commitments attached to a certain product
	 *
	 * @author GM
	 * @param int	The id of the product
	 * @return mixed	Array of commitments
	 **/
	public function get_product_commitments($p_id)
	{
		$sql = 'SELECT
					pc_id,
					pc_name,
					pc_min_qty,
					pc_max_qty,
					pc_period_start,
					pc_period_end,
					pc_preseason_gap,
					DATE_SUB(pc_period_start, INTERVAL pc_preseason_gap DAY) AS pc_last_order_date,
					pc_predelivery_gap,
					CONCAT(
						"Delivery between ",
						DATE_FORMAT(pc_period_start, "%D %b %y"),
						" and ",
						DATE_FORMAT(pc_period_end, "%D %b %y"),
						". Order by ",
						DATE_FORMAT(DATE_SUB(pc_period_start, INTERVAL pc_preseason_gap DAY), "%D %b %y"),
						" OR ",
						pc_predelivery_gap,
						" days before delivery."
						) AS pc_description,
					p2pc_id,
					p2pc_stock
				FROM
					productcommitment
				LEFT JOIN
					p2pc ON pc_id = p2pc_pc_id AND p2pc_p_id = '. (int)$p_id .'
				WHERE
					pc_p_id = '. (int)$p_id .'
					OR p2pc_pc_id IS NOT NULL;';
		return $this->db->query($sql)->result_array();
	}

	/**
	 * Gets an array of what dates can be used when ordering the specified product. Assumes you will be ordering no earlier than today (no time machines allowed).
	 *
	 **/
	public function get_product_allowances($p_id, $min_stock=1)
	{
		//check stock, and reduce/simmer
		$sql = 'SELECT
					pc_id,
					pc_name,
					IF(pc_max_qty = 0, p2pc_stock, pc_max_qty) AS pc_max_qty,
					pc_min_qty,
					IF(pc_period_start <= NOW(),
						NOW() + INTERVAL pc_predelivery_gap DAY,	/* if season has started, use the predelivery_gap buffer */
						pc_period_start + INTERVAL pc_preseason_gap DAY /* cant order to close to the start of the season */
						) AS "pc_period_start",
					pc_period_end,
					p2pc_id,
					p2pc_stock
				FROM
					productcommitment
				LEFT JOIN
					p2pc ON pc_id = p2pc_pc_id AND p2pc_p_id = '. (int)$p_id .'
				WHERE
					(
					pc_p_id = '. (int)$p_id .'
					OR p2pc_pc_id IS NOT NULL
					)
				AND
					(
						((pc_period_start - INTERVAL pc_preseason_gap DAY) >= "'. date('Y-m-d', time()) .'")
					OR
						((pc_predelivery_gap > 0) AND ((pc_period_end - INTERVAL pc_predelivery_gap DAY) >= "'. date('Y-m-d', time()) .'"))
					)
				';
		if ($min_stock != null)
		{
			$sql .= ' AND p2pc_stock >= ' . (int)$min_stock .' ';
		}
		$sql .= '
				LIMIT 1;
					';
		$seasons = $this->db->query($sql)->result_array();
		return $seasons;
	}

	/**
	 * Calculates stock levels across seasons
	 *
	 * @author GM
	 * @param Array $seasons Seasons for the product, use get_product_allowances
	 * @return Array $result 	Contains lowest,highest,total stock levels across the seasons
	 **/
	public function calculate_stock_remaining($seasons)
	{
		$result = array(
			'lowest' => null,
			'highest' => null,
			'total' => 0
		);

		foreach($seasons as $s)
		{
			if ( $result['lowest'] == null OR $s['p2pc_stock'] < $result['lowest'] )
			{
				$result['lowest'] = $s['p2pc_stock'];
			}
			if ( $result['highest'] == null OR $s['p2pc_stock'] > $result['highest'] )
			{
				$result['highest'] = $s['p2pc_stock'];
			}
			$result['total'] += $s['p2pc_stock'];
		}
		$result['seasons'] = count($seasons);

		return $result;
	}

	/**
	 * Checks if the request is allowed, or how much can be ordered.
	 *
	 * @author GM
	 * @param int	$p_id	Product ID of the item requested
	 * @param string $delivery_date	The date requested for delivery (e.g. 2013-05-01)
	 * @param int 	$qty 	The qantity that has been requested. Optional.
	 * @return bool $result	True if you can order for that date
	 **/
	public function check_product_allowance($p_id, $delivery_date, $qty=null)
	{
		//do it by getting all the seasons for the product, saves me working out how to write a new sql query
		$seasons = $this->get_product_allowances($p_id);
		if (!$seasons) {
			return false;
		}

		foreach($seasons as $s)
		{
			if ($delivery_date >= $s['pc_period_start'] && $delivery_date <= $s['pc_period_end'])
			{
				//do we care about qty?
				if ($qty == null)
				{
					return TRUE; //yes you can order that much on that date
				}
				else if ($s['pc_qty_min'] = 0 && $qty <= $s['pc_qty_max'])
				{
					return TRUE;
				}
				else if ($qty <= $s['pc_qty_max'] && $qty >= $s['pc_qty_min'])
				{
					return TRUE; //yes you can order that much on that date
				}
			}
		}

		//still haven't found a season suitable?
		return FALSE;
	}

	/**
	 * Get possible units to attach to a product
	 *
	 */
	public function get_product_units()
	{
		$sql = 'SELECT
					pu_id,
					pu_single,
					pu_plural,
					pu_short_single,
					pu_short_plural
				FROM
					productunit';
		return $this->db->query($sql)->result_array();
	}


	/**
	 * get product details from input
	 *
	 * @return mixed	p_id on success, false on failure
	 */
	protected function get_product_from_input()
	{
		return array(
			'p_page_order'    => $this->input->post('p_page_order') ?: 10,
			'p_status'        => $this->input->post('p_status') ?: 'Draft',
			'p_slug'          => $this->input->post('p_slug'),
			'p_code'          => $this->input->post('p_code') ?: null,
			'p_s_id'          => $this->input->post('p_s_id'),
			'p_name'          => $this->input->post('p_name'),
			'p_description'   => $this->input->post('p_description') ?: null,
			'p_pu_id'         => $this->input->post('p_pu_id') ?: null,
			'p_price'         => $this->input->post('p_price'),
			'p_cost'          => $this->input->post('p_cost'),
			'p_stock_warning' => $this->input->post('p_stock_warning') ?: 0,
		);
	}


	/**
	 * Create a new product in the db
	 *
	 * @return mixed	p_id on success, false on failure
	 */
	public function insert_product()
	{
		$product = $this->get_product_from_input();
		$this->db->insert('product', $product);
		$id = $this->db->insert_id();

		return $this->update_product($id);
	}


	/**
	 * Update a product and details about it, using input->post data
	 *
	 * @return mixed	p_id on success, false on failure
	 */
	public function update_product($p_id=null)
	{
		if (!$p_id)
		{
			//insert only the bare minimum to get started
			$sql = 'INSERT
					INTO
						product
						(p_name, p_slug)
					VALUES
						(?, ?)';
			$sql_data = array(
				$this->input->post('p_name'),
				$this->input->post('p_slug')
			);
			$result = $this->db->query($sql, $sql_data);
			if (!$result)
			{
				return false; //error
			}
			$p_id = $this->db->insert_id();
		}

		//even if we just added a product, we will now update the product with all the details the user might have submitted
		$sql = 'UPDATE
					product
				SET
					p_name = '. $this->db->escape( $this->input->post('p_name') ) .',
					p_slug = '. $this->db->escape( $this->input->post('p_slug') );

		if (@$this->input->post('p_page_order'))
		{
			$sql .= ', p_page_order = '. $this->db->escape( $this->input->post('p_page_order') );
		}
		if (@$this->input->post('p_s_id'))
		{
			$sql .= ', p_s_id = '. $this->db->escape( $this->input->post('p_s_id') );
		}
		if (@$this->input->post('p_status'))
		{
			$sql .= ', p_status = '. $this->db->escape( $this->input->post('p_status') );
		}
		if (@$this->input->post('p_description'))
		{
			$sql .= ', p_description = '. $this->db->escape( $this->input->post('p_description') );
		}
		if (@$this->input->post('p_pu_id'))
		{
			$sql .= ', p_pu_id = '. $this->db->escape( $this->input->post('p_pu_id') );
		}
		if (@$this->input->post('p_price'))
		{
			$sql .= ', p_price = '. $this->db->escape( $this->input->post('p_price') );
		}
		if (@$this->input->post('p_cost'))
		{
			$sql .= ', p_cost = '. $this->db->escape( $this->input->post('p_cost') );
		}
		if (@$this->input->post('p_stock_warning'))
		{
			$sql .= ', p_stock_warning = '. $this->db->escape( $this->input->post('p_stock_warning') );
		}
		$sql .= ' WHERE
					p_id = '. (int)$p_id;

		$result = $this->db->query($sql);

		//what about auxillary tables, like categories?
		if (@$this->input->post('p_cats'))
		{
			//remove them all first
			$sql = 'DELETE
					 FROM
						p2cat
					 WHERE
						p2cat_p_id = '. (int)$p_id.';';
			$this->db->query($sql);

			//now add them
			$sql_vals = array();
			foreach($this->input->post('p_cats') as $cat)
			{
				$sql_vals[] = '('. (int)$p_id .', '. (int)$cat .')';
			}

				$sql = 'INSERT INTO
							p2cat
						(p2cat_p_id, p2cat_cat_id)
						VALUES '.implode(', ', $sql_vals);
			$this->db->query($sql);
		}
		//auxillary table, the allowed product commitments
			//remove all product commitments/seasons
				$sql = 'DELETE
						FROM
							productcommitment
						WHERE
							pc_p_id = '. (int)$p_id. ';';
				$this->db->query($sql);
				$sql = 'DELETE
						FROM
							p2pc
						WHERE
							p2pc_p_id = '. (int)$p_id.';';
				$this->db->query($sql);

				//add product committments, old style
				$mins = $this->input->post('p_pc_min_qty') ?: array();
				$maxs = $this->input->post('p_pc_max_qty') ?: array();
				$period_starts = $this->input->post('p_pc_period_start');
				$period_ends = $this->input->post('p_pc_period_end');
				$preseason_gaps = $this->input->post('p_pc_preseason_gap');
				$predelivery_gaps = $this->input->post('p_pc_predelivery_gap');
				$sql_vals = array();
				$this->load->helper('date_helper');

				foreach($mins as $li => $min)
				{
					if ( (@$maxs[$li] != null)
						AND (@$period_starts[$li] != null)
						AND (@$period_ends[$li] != null)
						AND (@$preseason_gaps[$li] != null)
						AND (@$predelivery_gaps[$li] != null)
					) {
						$sql_vals[] = '('. (int)$p_id .', '
										. $this->db->escape( $min ) .', '
										. $this->db->escape( $maxs[$li] ) .', '
										. $this->db->escape( date("Y-m-d", strtotime(str_replace('/', '-', $period_starts[$li]))) ) .', '
										. $this->db->escape( date("Y-m-d", strtotime(str_replace('/', '-', $period_ends[$li]))) ) .', '
										. $this->db->escape( $preseason_gaps[$li] ) .', '
										. $this->db->escape( $predelivery_gaps[$li] ) .')';
					}
					else
					{
						//not all the fields were filled in, how did that happen?

					}
				}
				if ($sql_vals != null)
				{
					$sql = 'INSERT INTO
								productcommitment
							(pc_p_id, pc_min_qty, pc_max_qty, pc_period_start, pc_period_end, pc_preseason_gap, pc_predelivery_gap)
							VALUES '. implode(', ', $sql_vals) .';';
					$this->db->query($sql);
				}


				//now add them - the new style
				$pc_ids = $this->input->post('p_pc_id');
				$pc_qts = $this->input->post('p_pc_max_qty');
				$sql_vals = array();

				if (gettype($pc_ids) == 'array' || gettype($pc_ids) == 'object')
				{
					foreach($pc_ids as $li => $pc_id)
					{
						if (@$pc_ids[$li] != null && @$pc_qts[$li] != null)
						{
							$sql_vals[] = '('. (int)$p_id .', '
											. (int)$pc_ids[$li] .', '
											. (int)$pc_qts[$li] .'
											) ';
						}
						else
						{
							//not all the fields were filled in, how did that happen?
						}
					}
				}

				if ($sql_vals != null)
				{
					$sql = 'INSERT INTO
								p2pc
							(p2pc_p_id, p2pc_pc_id, p2pc_stock)
							VALUES '. implode(', ', $sql_vals) .';';
					$this->db->query($sql);
				}
			//done with product commitments/seasons

		//all done, product added/updated
		if ($result && $p_id)
		{
			return $p_id;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Updates the stock, relative to what it is. Finds a suitable product_commitment row to change stock of
	 *
	 * @param	$p_id	The product id to change
	 * @param	$change	The direction to change it, + or -.
	 * @param	$value	How much to change it, an interger.
	 * @param	$date	Date in which the stock should be available
	 */
	public function update_product_stock($p_id, $change='+', $value=0, $date)
	{
		if (($change != '+') && ($change != '-'))
		{
			return false;
		}

		$sql = 'UPDATE
					productcommitment
				SET
					pc_max_qty = pc_max_qty '. $change .' '. (int)$value .'
				WHERE
					pc_p_id = "' . (int)$p_id . '"
				AND
					pc_period_start <= '. $this->db->escape($date) .'
				AND
					pc_period_end >= '. $this->db->escape($date) .'
				LIMIT 1
				;';
		$result = $this->db->query($sql);

		//if we didn't update it that way, then use the new season system
		if (!$result)
		{
			$sql = 'UPDATE
						p2pc
					SET
						p2pc_stock = p2pc_stock '. $change .' '. (int)$value .'
					FROM (
						SELECT
							p2pc_id
						FROM
							p2pc
						LEFT JOIN
							productcommitment ON p2pc_pc_id = pc_id
						WHERE
							p2pc_p_id = "' . (int)$p_id . '"
						AND
							pc_period_start <= '. $this->db->escape($date) .'
						AND
							pc_period_end >= '. $this->db->escape($date) .'
						LIMIT 1
					);';
			$result = $this->db->query($sql);
		}
	}

	/*
	 * Updates the image to one that has hopefully just been uploaded
	 */
	public function update_img($p_id, $filename)
	{
		$sql = 'UPDATE
					product
				SET
					p_image = ?
				WHERE
					p_id = "' . (int)$p_id . '";';

		return $this->db->query($sql, $filename);
	}



	/**
	 * Sets a product status to Removed, so they should not be displayed in lists.
	 */
	public function delete_product($p_id)
	{
			$sql = 'UPDATE
						product
					SET
						p_status = "Removed"
					WHERE
						p_id = '. (int)$p_id .'
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

/* End of file: ./application/models/users_model.php */
