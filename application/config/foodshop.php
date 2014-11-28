<?php
/*
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

// Application version
$config['major_version'] = '0.2';

//About the site
$config['site_name'] = 'The Example Food Hub';
$config['site_abbr'] = 'food hub';
$config['site_slogan'] = 'An example website, providing an online location to buy produce from local suppliers and cut down on food miles.';
$config['footer_picking_list'] = 'Contact us if you have any queries. Thank you for supporting local producers.';

$config['default_bg_town'] = 'Carlisle';

//Settings
$config['contact_email'] = 'gregory@clouddataservice.co.uk'; //contact form recipient etc
$config['auto_email'] = 'no-reply@fairfoodcarlisle.org'; //where automated e-mails are sent from
$config['dev_email'] = 'developers@clouddataservice.co.uk'; //where system e-mails get sent to (e.g. critical errors)

//Signup & Member settings
$config['use_signup_code'] = 0; //If 0 a list of active buying groups well be shown on the registration page. If 1 then the buying group code is needed from the BG page.
$config['default_signup_group'] = null; //If null a group is required for registration (valid code or selected bg depending on use_signup_code). Otherwise the bg_id set here will be used if none is supplied.

//the next 4 settings cause specific(hardcoded) actions. LEAVE as NULL or pr prepare for UNDESIRED effects.
$config['delivery_registration_text'] = NULL;
$config['delivery_options'] = NULL;.
$config['delivery_standard_postcode_areas'] = NULL;
$config['delivery_nonstandard_message'] = NULL;

//Ordering settings
$config['confirm_new_orders'] = 0; //If 1 then orders need to be confirmed after placing them. If 0 then order is confirmed when placed.
$config['item_reserved_timeout'] = '30 minutes'; //string recognised by strtotime when appeneded by 'now - '. After this time ordered items will be unreserved.
$config['recuring_order_buffer'] = '1 month'; //string recognised by strtotime when appeneded by 'now + '. How far in advance order_items should be made from order_recurrings

// Map the various roles to the base controller path for their access level
$config['roles_controllers'] = array(
	'Admin' => 'admin',
	'Customer' => 'products',
	'Member' => 'products'
);

$config['site_nav'] = array(
					'Home' 	=> 'http://clouddataservice.co.uk/',
					'Services' =>  'http://clouddataservice.co.uk/services',
					'The Team' => 'http://clouddataservice.co.uk/team',
					'The Food Hub' => array(
										'Produce' => '/products'
										),
					'Contact' => '/contact'
					);
$config['site_nav_hubkey'] = 'The Food Hub';

$config['default_bg_town'] = 'Littleton';

// Go Cardless config settings
$config['gocardless_livemode'] = 0; //use 0 for testing/sandbox, 1 to actually take your money.

if ($config['gocardless_livemode'] == 0)
{
	$config['gocardless_account'] = array(
	  'app_id'        => '',
	  'app_secret'    => '',
	  'access_token'  => '',
	  'merchant_id'   => ''
	);
}
else
{
	$config['gocardless_account'] = array(
	  'app_id'        => '',
	  'app_secret'    => '',
	  'access_token'  => '',
	  'merchant_id'   => ''
	);
}
