<h2>Orders to <?php echo config_item('site_name'); ?> for <?php echo $supplier['s_name']; ?></h2>
<p>Page generated on <strong><?php echo date('D jS F Y \a\t G:i', time()); ?></strong>.</p>

<?php
if (@$orders)
{
	$unconfirmed = 0;
	$curr_date = $orders[0]['oi_delivery_date'];
	echo '<h3>'. date('D jS F', strtotime($curr_date)) .'</h3>';
	echo '<table class="results">';
	foreach($orders as $o)
	{
		if ($curr_date != $o['oi_delivery_date'])
		{
			$curr_date = $o['oi_delivery_date'];
			echo '</table>
					<h3>'. date('D jS F', strtotime($curr_date)) .'</h3>
					<table class="results">';
		}
		echo '<tr style="border-bottom:1px solid #ccc; line-height:30px;">';
			 //<td>Order '. $o['oi_id'] .'</td>
		echo '<td>'. $o['p_name'] .'</td>
			 <td> For '. $o['o_customers'] .' customer(s)</td>
			 <td>'. $o['o_qty'] . $o['pu_short_plural'] .'</td>
			 <td>';
				if ($o['oi_status'] == 'Reserved')
				{
					$unconfirmed++;
					echo 'Unconfirmed';
				}
				elseif ($o['oi_status'] == 'Confirmed')
				{
					echo 'Confirmed';
				}
			echo '</td>';
		echo '</tr>';
	}
	echo '</table>';

	if ($unconfirmed > 0)
	{
		echo '';
	}
}
else
{
	echo 'No orders found.';
}
?>
