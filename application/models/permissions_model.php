<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handle user permissions within the system
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


class Permissions_model extends MY_Model
{


	protected $_table = 'permission_group';
	protected $_primary = 'pg_id';


	public function __construct()
	{
		parent::__construct();
	}



	/**
	 * Get a list of groups that hold user permissions
	 *
	 * @author GM
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_groups_list()
	{
		$sql = 'SELECT
					pg_id,
					pg_name
				FROM
					permission_group ';
		$sql .= ' WHERE 1 = 1 ';

		return $this->db->query($sql)->result_array();
	}


	public function get_group_details($pg_id)
	{
		$sql = 'SELECT
					pg.pg_id,
					pg.pg_name,
					pg2pi.pg2pi_value,
					pg2pi.pg2pi_bg_value,
					pg2pi.pg2pi_s_value,
					pi.pi_name,
					pi.pi_key
				FROM
					permission_group pg
				LEFT JOIN
					pg2pi ON pg.pg_id = pg2pi.pg2pi_pg_id
				LEFT JOIN
					permission_item pi ON pg2pi.pg2pi_pi_id = pi.pi_id
				WHERE
					pg.pg_id = '. (int)$pg_id .'
				';

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Gets the permissions of a user in a nicely prepared array
	 */
	public function get_user_permissions($user)
	{
		$sql = 'SELECT
					pg.pg_id,
					pg.pg_name,
					pg2pi.pg2pi_value,
					pg2pi.pg2pi_bg_value,
					pg2pi.pg2pi_s_value,
					pi.pi_name,
					pi.pi_key
				FROM
					permission_group pg
				LEFT JOIN
					pg2pi ON pg.pg_id = pg2pi.pg2pi_pg_id
				LEFT JOIN
					permission_item pi ON pg2pi.pg2pi_pi_id = pi.pi_id
				WHERE
					pg.pg_id = '. (int)$user['u_pg_id'] .'
				';

		$result = $this->db->query($sql)->result_array();
		$permissions = array();

		//safety check they have required user data
		if (!isset($user['u_bg_id']))
		{
			$user['u_bg_id'] = 0;
		}
		if (!isset($user['u_s_id']))
		{
			$user['u_s_id'] = 0;
		}

		//okay, make the nice array we want
		foreach($result as $pi)
		{
			if ($pi['pg2pi_value'] == 1)
			{
				$permissions[ $pi['pi_key'] ]['all'] = 1;
				$permissions[ $pi['pi_key'] ]['any'] = 1;
			}
			if ($pi['pg2pi_bg_value'] == 1)
			{
				$permissions[ $pi['pi_key'] ]['bg'] = $user['u_bg_id'];
				$permissions[ $pi['pi_key'] ]['any'] = 1;
			}
			if ($pi['pg2pi_s_value'] == 1)
			{
				$permissions[ $pi['pi_key'] ]['s'] = $user['u_s_id'];
				$permissions[ $pi['pi_key'] ]['any'] = 1;
			}
		}

		return $permissions;
	}


}

/* End of file: ./application/models/users_model.php */
