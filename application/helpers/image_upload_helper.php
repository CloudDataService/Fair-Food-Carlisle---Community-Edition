<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Helper to upload images and deal with them, for reference from the DB
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


// ------------------------------------------------------------------------

/**
 * Uploads the image file provided in the form, and returns data about it to put in the db
 *
 * @author	GM
 * @access	public
 * @param	integer		The id of the object, combined with time this will form a filename
 * @param	string		The fieldname of the form that was submitted
 * @param	string		The old filename, that we will delete if all goes okay and it's still there
 * @param	string		A folder name, to give a bit of sorting.
 * @param	array 		config options to be used. e.g. width and height to make the image
 * @return	array 	Will contain ['result'] with true or false/empty
 */
if ( ! function_exists('image_upload'))
{
	function image_upload($id, $fieldname, $oldname, $folder, $config)
	{
		$_CI =& get_instance();

		// set upload path for profile images
		$config['upload_path'] = FCPATH . 'img/uploads/'. $folder .'/';

		$file = explode('.', $_FILES[$fieldname]['name']);

		// create md5 hash given id and timestamp
		$config['file_name'] = $id .'-'. md5($id . time()) . '.' . $file[1];

		// set allowed image types
		$config['allowed_types'] = 'gif|jpg|jpeg|png';

		// if already a file, overwrite it with the new one
		$config['overwrite'] = true;

		// load upload library
		$_CI->load->library('upload', $config);

		// if the upload does not work
		if ( ! $_CI->upload->do_upload($fieldname))
		{
			// get error messages
			$data['upload_errors'] = $_CI->upload->display_errors();
			$data['result'] = false;
			return $data;
		}
		// if upload works
		else
		{
			// get upload data
			$data = $_CI->upload->data();

			// set params for image resize
			$config['image_library'] = 'gd2';
			$config['quality'] = 100;
			$config['source_image'] = $config['upload_path'] . $data['file_name'];
			if (!$config['width']){ $config['width'] = 100;}
			if (!$config['height']){ $config['height'] = 100;}

			// load image manipulation library
			$_CI->load->library('image_lib', $config);

			// resize image
			if ($_CI->image_lib->resize())
			{
				// make a smaller 50x50 version of profile img
				$_CI->image_lib->clear();

				// set params for image resize
				$config['image_library'] = 'gd2';
				$config['quality'] = 100;
				$config['source_image'] = $config['upload_path'] . $data['file_name'];
				$config['new_image'] = $config['upload_path'] . 'small/' . $data['file_name'];
				$config['width'] = 140;
				$config['height'] = 140;

				$_CI->image_lib->initialize($config);

				// resize image again
				if ($_CI->image_lib->resize())
				{
					// if has an existing image
					if (file_exists($oldname != '' && $config['upload_path'] . $oldname))
					{
						// delete main image
						unlink($config['upload_path'] . $oldname);
						// delete image 50x50
						unlink($config['upload_path'] . 'small/' . $oldname);
					}

					$data['result'] = true;
					return $data;
				}
			}
		}
	}
}


/* End of file */
?>
