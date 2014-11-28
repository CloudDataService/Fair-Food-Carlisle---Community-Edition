<?php

/**
 * Return the url of a product
 **/
function product_url($product)
{
	if ($cat = element('c_slug', $product) == null)
	{
		$cat = 'all'; //some dummy category, the system will redirect to the right one
	}

	return site_url('products/'. $cat .'/'. element('p_slug', $product));
}

/**
 * Build up the data required to display a CalNineK calendar
 *
 * @param $days_for an interpretable period for which you want days for. E.g. '6 months' will return days for the next 6 months.
 * @param $seasons from the database of what is available
 * @param $restricted_day the only day the user can order (e.g. Tuesday)
 * @return $data an array containing three arrays(years, months, days) of date objects
 **/
function calninek_build_order_data($days_for='6 months', $seasons=array(), $restricted_day=null)
{
	// first a hack: when we deploy this people will have to re-log-in so their buying day is in the session data. We know it is Tuesday.
	// remove this hack if it was deployed at least a month ago.
	if ($restricted_day == '')
	{
		$restricted_day = 'Tuesday';
	}

	//space for the data to return
	$data = array(
		'years' => array(),
		'months' => array(),
		'days' => array()
		);
	if (!$seasons)
	{
		return $data;
	}

	//from today till the time we want to look forward to
	$calendar_start = new DateTime();
	$calendar_end = new DateTime('+'.$days_for);
	$interval = new DateInterval('P1D');
	$days = new DatePeriod($calendar_start, $interval, $calendar_end);
	//loop through every day
	foreach($days as $date)
	{
		//only include in the calendar if the user can buy on that weekday
		if ($restricted_day != null && $date->format('l') == $restricted_day)
		{
			//add this date into the data to return
			$data['years'][$date->format('Y-01-01')] = $date;
			$data['months'][$date->format('Y-m-01')] = $date;
			$data['days'][$date->format('Y-m-d')] = array('date' => $date, 'available' => FALSE);
		}
	}

	//loop through seasons to fill in signal whats available
	foreach ($seasons as $s)
	{

		// Convert to date/time objects to get date range
		$start_date = new DateTime( substr($s['pc_period_start'], 0, 10) );
		$end_date = new DateTime($s['pc_period_end']);
		$interval = new DateInterval('P1D');
		$days = new DatePeriod($start_date, $interval, $end_date);

		//from pc_period_start, to pc_period_end in steps of days
		foreach ($days as $date)
		{
			//if it's in the calendar
			if (isset($data['days'][$date->format('Y-m-d')]))
			{
				//add this date into the data to return
				$data['days'][$date->format('Y-m-d')]['available'] = TRUE;
			}
		}
	}
	return $data;

} //end calninek_build_order_data


?>
