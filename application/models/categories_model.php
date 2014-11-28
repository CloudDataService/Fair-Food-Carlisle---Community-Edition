<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handle categories within the system
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


class Categories_model extends MY_Model
{


	protected $_table = 'category';
	protected $_primary = 'cat_id';


	public function __construct()
	{
		parent::__construct();
	}




	/**
	 * Get a single cat, and many details about it
	 *
	 * @author GM
	 * @param string $cat_id		the cat id requested(int) or the slug of the cat(must not be int)
	 * @return mixed		DB result row on success, FALSE on failure
	 */
	public function get_category($cat_id)
	{
		if (is_numeric($cat_id))
		{
			$where = ' category.cat_id = ' . (int) $cat_id . ' ';
		}
		else
		{
			$where = ' category.cat_slug = ' . $this->db->escape($cat_id) . ' AND category.cat_status = "Active" ';
		}

		$sql = 'SELECT
					category.cat_id,
					category.cat_page_order,
					category.cat_name,
					category.cat_image,
					category.cat_description,
					category.cat_parent_id,
					category.cat_status,
					parent.cat_name AS cat_parent_name,
					parent.cat_slug AS cat_parent_slug,
					category.cat_slug,
					category.cat_show_products
				FROM
					category
				LEFT JOIN
					category parent
					ON category.cat_parent_id = parent.cat_id
				WHERE
					' . $where . '
				LIMIT 1';

		return $this->db->query($sql)->row_array();
	}


	/**
	 *
	 * @return mixed	cat_id on success, false on failure
	 */
	public function update_category($cat_id)
	{
		// load form validation library
		$this->load->library(array('slug'));
		$this->slug->set_config(array(
			'table'       => 'category',
			'id'          => 'cat_id',
			'field'       => 'cat_slug',
			'title'       => 'cat_slug',
			'replacement' => 'dash' // Either dash or underscore
		));
		$slug = $this->slug->create_slug($this->input->post('cat_slug'));

		if ($cat_id)
		{
			$sql = 'UPDATE
						category
					SET
						cat_page_order = ?,
						cat_name = ?,
						category.cat_slug = ?,
						category.cat_description = ?
					WHERE
						cat_id = ?';
			$sql_data = array($this->input->post('cat_page_order'),
						 $this->input->post('cat_name'),
						 strtolower(trim($slug)),
						 $this->input->post('cat_description'),
						 $cat_id
						  );
		}
		else
		{
			$sql = 'INSERT
					INTO
						category
						(cat_page_order, cat_name, cat_slug, cat_description)
					VALUES
						(?, ?, ?, ?)';

			$sql_data = array(
				$this->input->post('cat_page_order'),
				$this->input->post('cat_name'),
				strtolower(trim($slug)),
				$this->input->post('cat_description'),
			);
		}

		//run query
		$result = $this->db->query($sql, $sql_data);

		//all done
		if ($result && $cat_id)
		{
			return $cat_id;
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
	 * Get a list of categories in the system
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @param Boolean $show_products	TRUE if you want to get the products where show_products = 1
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_categories($params=null, $show_products=FALSE)
	{
		if ( !$params) $params = array('order' => 'cat_page_order');
		if ( ! in_array(@$params['order'], array('cat_id', 'cat_page_order', 'cat_name', 'cat_parent_id', 'cat_slug')) ) $params['order'] = 'cat_page_order';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'asc';

		$sql = 'SELECT
					category.cat_id,
					category.cat_page_order,
					category.cat_name,
					category.cat_image,
					category.cat_description,
					category.cat_parent_id,
					parent.cat_name AS cat_parent_name,
					category.cat_slug,
					category.cat_show_products
				FROM
					category
				LEFT JOIN
					category parent
					ON category.cat_parent_id = parent.cat_id
				';

		$sql .= ' WHERE 1 = 1
				AND category.cat_status = "Active" ';

		if (@$params['cat_parent_id'])
			$sql .= ' AND cat_parent_id = ' . $this->db->escape($params['cat_parent_id']) . ' ';

		// set order by clause
		$sql .= ' ORDER BY ' . $params['order'] . ' ' . $params['sort'] . '
					 , cat_page_order DESC, cat_name ASC';

		// if a limit has been set
		if (@$params['limit'] != FALSE)
			$sql .= ' LIMIT ' . (int)$params['start'] . ', ' . (int)$params['limit'];

		$cats = $this->db->query($sql)->result_array();

		//check, do you want any products?
		if ($show_products == FALSE)
		{
			return $cats;
		}
		else
		{
			$product_param = array('random' => TRUE);
			foreach($cats as $key => $cat)
			{
				if ($cat['cat_show_products'] > 0)
				{
					$product_param['limit'] = $cat['cat_show_products'];
					$cats[$key]['products'] = $this->products_model->get_category_products($cat['cat_id'], $product_param);
				}
			}
			//give the cats back with products
			return $cats;
		}
	}

	/**
	 * Get the total count of cats, ignoring the limit in filter.
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return int	the number of users found
	 */
	public function get_total_categories($params=null)
	{
		if ( !$params) $params = array('order' => 'cat_id');
		if ( ! in_array(@$params['order'], array('cat_name', 'cat_parent_id')) ) $params['order'] = 'cat_id';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'desc';

		$sql = 'SELECT
					COUNT(cat_id) AS total
				FROM
					category
				';

		$sql .= ' WHERE 1 = 1
				AND cat_status = "Active" ';

		if (@$params['cat_parent_id'])
			$sql .= ' AND cat_parent_id = ' . $this->db->escape($params['cat_parent_id']) . ' ';

		$result = $this->db->query($sql)->row_array();
		return $result['total'];
	}

	/*
	 * Updates the image to one that has hopefully just been uploaded
	 */
	public function update_img($cat_id, $filename)
	{
		$sql = 'UPDATE
					category
				SET
					cat_image = ?
				WHERE
					cat_id = "' . (int)$cat_id . '";';

		return $this->db->query($sql, $filename);
	}



	/**
	 * Sets a category status to Removed, so they should not be displayed in lists.
	 */
	public function delete_category($cat_id)
	{
			$sql = 'UPDATE
						category
					SET
						cat_status = "Removed"
					WHERE
						cat_id = '. (int)$cat_id .'
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
