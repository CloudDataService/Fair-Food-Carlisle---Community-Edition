<?php

class Options_model extends My_Model
{
	
	
	protected $_table = 'options';
	protected $_primary = 'o_id';
	protected $_pct_key = 'o_pct_id';
	
	
	public function __construct()
	{
		parent::__construct();
	}
	
	
	
	
	/**
	 * Get one or more options and values
	 *
	 * @param mixed $option		NULL for all options & values; string for one option; array of options
	 * @return mixed		Array, string or boolean
	 */
	public function get($option = NULL)
	{
		if ($option == NULL)
		{
			// Get all of the options!
			$sql = 'SELECT
						o_name,
						o_value
					FROM
						options
					WHERE
						o_pct_id = ?';
			
			$query = $this->db->query($sql, array($this->pct->get_id()));
		}
		elseif (is_array($option))
		{
			// Get all of the options in the array!
			$sql = 'SELECT
						o_name,
						o_value
					FROM
						options
					WHERE
						o_pct_id = ?
					AND
						o_name IN (?)';
			$query = $this->db->query($sql, array($this->pct->get_id(), implode(', ', $option)));
		}
		else
		{
			// One option was requested - get the value of it
			return $this->_get_one($option);
		}
			
		if ($query->num_rows() > 0)
		{
			$result = $query->result_array();
			$options = array();
			foreach ($result as $row)
			{
				$options[$row['o_name']] = $this->_parse($row['o_value']);
			}
			return $options;
		}
		else
		{
			return FALSE;
		}
		
	}
	
	
	
	
	/**
	 * Get a single option value
	 *
	 * @param string $name		Name of the option value to retrieve
	 * @return mixed		Raw value as string, or boolean if 1/0/yes/no
	 */
	private function _get_one($name = NULL)
	{
		$sql = 'SELECT
						o_value
					FROM
						options
					WHERE
						o_pct_id = ?
					AND
						o_name = ?
					LIMIT 1';
		$query = $this->db->query($sql, array($this->pct->get_id(), $name));
		
		if ($query->num_rows() == 1)
		{
			$row = $query->row_array();
			return $this->_parse($row['o_value']);
		}
		else
		{
			return FALSE;
		}
	}
	
	
	
	
	/**
	 * Set one or more options
	 *
	 * @param mixed $name		String: option name to set. Array: option names => values
	 * @param string $value		Value of $name to set
	 * @return bool
	 */
	public function set($name = NULL, $value = NULL)
	{
		$pct_id = $this->pct->get_id();
		$errors = 0;
		
		if ($name !== NULL && $value !== NULL)
		{
			// One option to set using name and value
			$data = array($name => $value);
		}
		elseif (is_array($name) && $value == NULL)
		{
			// Lots of options in array format
			$data =& $name;
		}
		
		if (is_array($data))
		{
			$sql = 'INSERT INTO
						options
					SET
						o_pct_id = ?,
						o_name = ?,
						o_value = ?
					ON DUPLICATE KEY UPDATE
						o_pct_id = VALUES(o_pct_id),
						o_name = VALUES(o_name),
						o_value = VALUES(o_value)';
			
			foreach ($name as $o_name => $o_value)
			{
				$query = $this->db->query($sql, array($pct_id, $o_name, $o_value));
				if ( ! $query) $errors++;
			}
		}
		
		return ($errors === 0);
	}
	
	
	
	
	/**
	 * Parse a stored option value for booleanness to return
	 *
	 * @param string $value		The option value
	 * @return mixed		String of value, or boolean if value is 1, 0, yes, no
	 */
	private function _parse($value)
	{
		if (in_array(strtolower($value), array('1', '0', 'yes', 'no')))
		{
			return filter_var($value, FILTER_VALIDATE_BOOLEAN);
		}
		else
		{
			return $value;
		}
	}
	
	
	
	
}

/* End of file: ./application/models/options_model.php */