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
 
//user fields
$config['user']['types'] = array('Admin', 'Member');

$config['user']['statuses'] = array('Pending', 'Active');

$config['titles'] = array('Mr', 'Mrs', 'Miss', 'Ms', '', 'Dr', 'Revd');


//contact form
$config['contact_reasons'] = array('new_group' => 'setting up a new buying group',
								   'register' => 'registering on the site',
								   'order' => 'an order',
								   'website' => 'the website',
								   'supplier' => 'being a supplier'
								   );

//product fields
$config['commitment_frequencies'] = array('week' => 'Week',
										  '2week' => '2 Weeks',
										  'month' => 'Month'
										  );
$config['commitment_periods'] = array('month' => 'Month',
									  '2month' => '2 Months',
									  '3month' => '3 Months',
									  '4month' => '4 Months',
									  'year' => 'Year'
									  //Victorian => Edwardian
									  );
$config['product_fields']['statuses'] = array('Active',
											  'Draft',
											  'Removed');
$config['product_fields']['image_exists'] = array('Yes',
												  'No');

//group fields
$config['group_fields']['statuses'] = Array('New',
											'Active',
											'Disabled');
									  
//Bill fields
$config['bill_fields']['statuses'] = array(
										'Draft',
										'Pending',
										'Paid'
									);
$config['bill_fields']['methods'] = array(
										'Go Cardless',
										'Cheque',
										'Cash',
										'Other'
									);
?>