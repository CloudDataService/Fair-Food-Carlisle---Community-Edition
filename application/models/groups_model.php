<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model to handlethe buying groups and associated functions
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


class Groups_model extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Get a single group, and many details about them
	 *
	 * @author GM
	 * @param int $bg_id		the gfroup id requested
	 * @return mixed		DB result row on success, FALSE on failure
	 */
	public function get_group($bg_id)
	{
		$sql = 'SELECT
					bg_id,
					bg_code,
					bg_name,
					bg_status,
					bg_addr_line1,
					bg_addr_line2,
					bg_addr_city,
					bg_addr_pcode,
					bg_addr_note,
					bg_deliveryday,
					CONCAT(advocate.u_title, " ", advocate.u_fname, " ", advocate.u_sname) AS advocate_name,
					advocate.u_email AS advocate_email,
					advocate.u_telephone AS advocate_phone,
					COUNT(members.u_id) AS bg_member_count
				FROM
					buyinggroup
				LEFT JOIN
					user advocate ON bg_id = advocate.u_bg_id AND 1 = advocate.u_advocate
				LEFT JOIN
					user members ON bg_id = members.u_bg_id
				WHERE bg_id = '. (int)$bg_id.'
                    AND members.u_status != \'Removed\'
				GROUP BY
					bg_id
				LIMIT 1
				';
		return $this->db->query($sql)->row_array();
	}

	/**
	 * Checks to see if a buying group code is valid
	 *
	 * @author GM
	 * @param string	The buying group code to lookup
	 * @param mixed		bg_id on success, FALSE on failure
	 */
	public function find_group($code)
	{
		$sql = 'SELECT
					bg_id
				FROM
					buyinggroup
				WHERE
					bg_code = '. $this->db->escape($code) .'
				AND
					bg_status = "Active"
					';
		$result = $this->db->query($sql)->row_array();
		return ($result ? $result['bg_id'] : false);
	}

	/**
	 *
	 * @return mixed	bg_id on success, false on failure
	 */
	public function update_group($bg_id=null, $input)
	{
		if (!@$bg_id)
		{
			$sql = 'INSERT
					INTO
						buyinggroup
						(bg_status)
					VALUES
						("New")';
			$result = $this->db->query($sql);
			if (!$result)
			{
				$this->flash->set('action', 'Problem adding buying group.', TRUE);
				return redirect(current_url());
			}
			$bg_id = $this->db->insert_id();
			//as it's new, we'll need a new bg_code
			$input['bg_code'] = $this->generate_bgcode($input['bg_name'], $bg_id);
		}
		$sql = 'UPDATE
					buyinggroup
				SET
					bg_name = '. $this->db->escape( $input['bg_name'] ) . ' ';
			if ( @$input['bg_code'] )
			{
				$sql .= ', bg_code = '. $this->db->escape( $input['bg_code'] ) . ' ';
			}
			if ( @$input['bg_status'] )
			{
				$sql .= ', bg_status = '. $this->db->escape( $input['bg_status'] ) . ' ';
			}
			if ( @$input['bg_addr_line1'] )
			{
				$sql .= ', bg_addr_line1 = '. $this->db->escape( $input['bg_addr_line1'] ) . ' ';
			}
			if ( @$input['bg_addr_line2'] )
			{
				$sql .= ', bg_addr_line2 = '. $this->db->escape( $input['bg_addr_line2'] ) . ' ';
			}
			if ( @$input['bg_addr_city'] )
			{
				$sql .= ', bg_addr_city = '. $this->db->escape( $input['bg_addr_city'] ) . ' ';
			}
			if ( @$input['bg_addr_pcode'] )
			{
				$sql .= ', bg_addr_pcode = '. $this->db->escape( $input['bg_addr_pcode'] ) . ' ';
			}
			if ( @$input['bg_addr_note'] )
			{
				$sql .= ', bg_addr_note = '. $this->db->escape( $input['bg_addr_note'] ) . ' ';
			}
			if ( @$input['bg_deliveryday'] )
			{
				$sql .= ', bg_deliveryday = '. $this->db->escape( $input['bg_deliveryday'] ) . ' ';
			}
			$sql .= '
				WHERE
					bg_id = '. (int)$bg_id;

		//run query
		$result = $this->db->query($sql);

		//all done
		if ($result && $bg_id)
		{
			return $bg_id;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Get a list of groups in the system
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_groups($params=null)
	{
		if ( !$params) $params = array('order' => 'bg_id');
		if ( ! in_array(@$params['order'], array('bg_name', 'bg_code', 'bg_deliveryday')) ) $params['order'] = 'bg_id';

		if (@$params['sort'] != 'asc' && @$params['sort'] != 'desc') $params['sort'] = 'desc';

		$sql = 'SELECT
					bg_id,
					bg_code,
					bg_name,
					bg_status,
					bg_deliveryday
				FROM
					buyinggroup ';

		$sql .= ' WHERE 1 = 1 ';

		if (@$params['bg_status'])
			$sql .= ' AND bg_status = ' . $this->db->escape($params['bg_status']) . ' ';

		if (@$params['bg_deliveryday'])
			$sql .= ' AND bg_deliveryday = ' . $this->db->escape($params['bg_deliveryday']) . ' ';

		// set order by clause
		$sql .= ' ORDER BY ' . $params['order'] . ' ' . $params['sort'] . ' ';
		// if a limit has been set
		if (@$params['limit'] != FALSE)
			$sql .= ' LIMIT ' . (int)$params['start'] . ', ' . (int)$params['limit'];

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get the total count of groups, ignoring the limit in filter.
	 *
	 * @author GM
	 * @param array $filter		array of search fields and the requested value
	 * @return int	the number of groups found
	 */
	public function get_total_groups($params=null)
	{
		if ( !$params) $params = array('order' => 'bg_id');
		if ( ! in_array(@$params['order'], array('bg_name', 'bg_code', 'bg_deliveryday')) ) $params['order'] = 'bg_id';

		$sql = 'SELECT
					COUNT(bg_id) AS total
				FROM
					buyinggroup';

		$sql .= ' WHERE 1 = 1 ';

		if (@$params['bg_status'])
			$sql .= ' AND bg_status = ' . $this->db->escape($params['bg_status']) . ' ';

		if (@$params['bg_deliveryday'])
			$sql .= ' AND bg_deliveryday = ' . $this->db->escape($params['bg_deliveryday']) . ' ';

		$result = $this->db->query($sql)->row_array();
		return $result['total'];
	}

	/**
	 * Get a list of groups that users are in
	 *
	 * @author GM
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_groups_list()
	{
		$sql = 'SELECT
					bg_id,
					bg_code,
					bg_name
				FROM
					buyinggroup ';
		$sql .= ' WHERE 1 = 1 ';

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get a list of groups that users can sign up to
	 *
	 * @author GM
	 * @return mixed		DB result array on success, FALSE on failure
	 */
	public function get_active_groups_list()
	{
		$sql = 'SELECT
					bg_id,
					bg_code,
					bg_name
				FROM
					buyinggroup ';
		$sql .= ' WHERE bg_status = "Active" ';

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Generates a random but nice BG code that is unique and can be shared.
	 *
	 * @author	GM
	 * @param string	name of the group, to give us something nice. E.g. FFC345cds
	 * @param int		the bg_id, that it doesn't matter if it stays the same
	 * @param int		attempts, so we can kill it if there are too many
	 * @return string	a unique string to use as the bg_code
	 **/
	private function generate_bgcode($name, $bg_id=null, $attempts=0)
	{
		if ($attempts > 5)
		{
			//don't run forever, just get out of this
			return false;
		}
		//make one up...
		$code = 'ffc';
		$code .= random_string('nozero', 3);
		$code .= strtolower( substr( str_replace(' ', '', $name), 0, 3) );

		//check it's unique
		$sql = 'SELECT bg_id FROM buyinggroup
				WHERE bg_code = '. $this->db->escape($code);
		if (@$bg_id)
		{
			$sql .= ' AND bg_id != '. (int)$bg_id;
		}
		$sql .= ' LIMIT 1;';
		$result = $this->db->query($sql);
		if ($result->num_rows() < 1)
		{
			//nothing found, this code is good
			return $code;
		}
		else
		{
			//oops, try again - recursively!
			return $this->generate_bgcode($name, $bg_id, $attempts+1);
		}
	}

}

/* End of file: ./application/models/users_model.php */
