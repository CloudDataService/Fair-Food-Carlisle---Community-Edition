
<?php

if (isset($orders) && $orders != null)
{
	$curr_date = $orders[0]['oi_delivery_date'];
	$week_total = 0.00;
	echo '<table class="results">';
	echo '<tr><td colspan="3"><h3>Your next delivery <span style="font-size:14px; color:#666;"> '. date('l jS F', strtotime($curr_date)) .'</span></h3></td></tr>';
	foreach($orders as $o)
	{
		//skip irrelevant orders
		if (!in_array($o['oi_status'], array("Reserved", "Confirmed")))
		{
			break;
		}

		$week_total += $o['oi_price'];
		echo '<tr class="vat" style="border-bottom:1px solid #ccc; line-height:30px;">';
			echo '<td>'. $o['p_name'];
			if (isset($o['oi_source_or']) && $o['oi_source_or'] != null)
			{
				echo '<br /><small class="oi-recurring-note">Part of an <a href="' . site_url('order/ongoing') . '">ongoing order</a>.</small>';
			}
			if ($o['oi_status'] == 'Reserved')
			{
				echo '<br><em>Item reserved until '. date('g:ia', strtotime($o['oi_ordered_date'] .' + '. $this->config->item('item_reserved_timeout'))) .'</em><br />';
			}
			echo '</td>
			 <td>'. $o['oi_qty'] .' '. $o['pu_short_plural'] .'</td>
			 <td> &pound;'. number_format($o['oi_price'], 2) .'</td>';
		echo '</tr>';
	}
	echo '<tr>
			<td colspan="2" style="text-align: right;"><strong>Delivery Total</strong></td>
			<td><strong>&pound;'. number_format($week_total, 2) .'</strong></td>
		</tr>';

			//display notes
			if (isset($order_notes[$curr_date]))
			{
				foreach($order_notes[$curr_date] as $note)
				{
					echo '<tr>
							<td colspan="2">' . $note['on_text'] . '</td>
							<td></td>
						</tr>';
				}
			}
	echo '</table>';

	echo '<p><a href="'. site_url('order') .'" class="btn">View all orders, to make changes or add notes</a></p>';

}
else
{
	echo 'No orders found.';

	echo '<p><a href="'. site_url('products') .'" class="btn">Browse produce to make an order</a></p>';
}
?>
