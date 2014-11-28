<?php

function mysql_timestamp_to_english_date( $date ) {
	//$d = DateTime::createFromFormat( "Y-m-d H:i:s", $date );
	//TODO: add support for lower versions of PHP
	$parts = explode('-', $date );
	$d = explode( " ", $parts[2] );
	$parts[2] = $d[0];
	$dval = $parts[2] . "/" . $parts[1] . "/" . $parts[0];
	return $dval; // $d->format( "d/m/Y" );
}

function english_date_to_mysql_timestamp( $date ) {
	//$d = DateTime::createFromFormat( "d/m/Y", $date );
	$d = date( "Y-m-d H:i:s", strtotime( $date ) );
	/*$parts = explode( "/", $date );
	$d = $parts[2] . "-" . $parts[1] . "-" . $parts[0] . " 00:00:00";*/
	return $d; //$d->format( "Y-m-d H:i:s" );
}

function english_date_to_mysql_date( $date ) {
	//$d = DateTime::createFromFormat( "d/m/Y", $date );
	$d = date( "Y-m-d", strtotime( $date ) );
	/*$parts = explode( "/", $date );
	$d = $parts[2] . "-" . $parts[1] . "-" . $parts[0] . " 00:00:00";*/
	return $d; //$d->format( "Y-m-d H:i:s" );
}

/**
 * From dd/mm/yyyy to yyy-mm-dd
 * because english_date_to_mysql_date relies too much on strtotime understanding the input
 **/
function other_english_date_to_mysql_date( $date ) {
	$parts = explode( "/", $date );
	$d = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
	return $d;
}

function english_dates_array_to_mysql_dates($english) {
	if (count($english) <= 1)
	{
		return $english;
	}
	$mysql = array();
	foreach($english as $key => $date)
	{
		if (preg_match('@^([0-3]{1}[0-9]{1})/([0-1]{1}[0-9]{1})/(20[0-9]{2})$@', $date, $matches))
		{
			$mysql[$key] = $matches[3] .'-'. $matches[2] .'-'. $matches[1];
		}
		else
		{
			$mysql[$key] = $date;
		}
	}
	return $mysql;
}

function dateDiff($startDate, $endDate)
{
    // Parse dates for conversion
    $startArry = date_parse($startDate);
    $endArry = date_parse($endDate);

    // Convert dates to Julian Days
    $start_date = gregoriantojd($startArry["month"], $startArry["day"], $startArry["year"]);
    $end_date = gregoriantojd($endArry["month"], $endArry["day"], $endArry["year"]);

    // Return difference
    return round(($end_date - $start_date), 0);
}


	function isValidDate($date, $separator) {

		if (count(explode($separator,$date)) == 3) {
			$pattern = "/^([0-9]{2})".$separator."([0-9]{2})".$separator."([0-9]{4})$/";
			if (preg_match ($pattern, $date, $parts))  {
				if (checkdate($parts[2],$parts[1],$parts[3])) {
					return true;
					/* This is a valid date */
				} else {
					return false;
				}
			/* This is an invalid date */
			}  else  {
				$pattern = "/^([0-9]{4})".$separator."([0-9]{2})".$separator."([0-9]{2})$/";
				if (preg_match ($pattern, $date, $parts))  {

					if (checkdate($parts[2],$parts[3],$parts[1])) {
						return true;
					/* This is a valid date */
					} else {
						return false;
					}
				/* This is an invalid date in terms of format */
				} else {
					return false;
				}
			}
		 } else {
			return false;
			/* Day, Month, Year - either of them not present */
		}
	}
?>
