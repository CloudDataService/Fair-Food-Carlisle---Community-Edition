Your orders are displayed below.

	<?php
	if ($this->input->get('print') != 'yes')
	{
		echo '<br><br><a style="float:right;" href="'. current_url() .'?print=yes" class="btn">Print this page</a>';
	}
	else
	{
		echo '<p>Page generated on <strong>' . date('D jS F Y \a\t G:ia', time()) . '</strong>.</p>';
	}
	?>

<?php

if (@$orders)
{
	$unconfirmed = 0;
	$curr_date = $orders[0]['oi_delivery_date'];
	$week_total = 0.00;
	echo '<table class="results">';
	echo '<tr><td colspan="4"><h3>Orders for '. date('D jS F', strtotime($curr_date)) .'</h3></td></tr>';
	foreach($orders as $o)
	{
		if ($curr_date != $o['oi_delivery_date'])
		{
			//display delivery total
			echo '<tr>
					<td></td>
					<td><strong>Delivery Total</strong></td>
					<td><strong>&pound;'. number_format($week_total, 2) .'</strong></td>
					<td></td>
				</tr>';

			//display notes
			if (isset($order_notes[$curr_date]))
			{
				foreach($order_notes[$curr_date] as $note)
				{
					echo '<tr>
							<td>' . $note['on_text'] . '</td>
						</tr>';
				}
			}

			//display option to add note
			echo form_open(current_url(), array('id' => 'note_form', 'style' => 'display:inline;'));
			echo '<tr class="noprint">
						<input type="hidden" name="on_delivery_date" value="'. $curr_date .'">
						<td><textarea name="on_text" style="width:100%;"></textarea></td>
						<td><input type="submit" class="btn" value="Add Note"></td>
				  </tr>';
			echo form_close();

			//prep for next display
			$week_total = 0;
			$curr_date = $o['oi_delivery_date'];
			//add title for delivery date
			echo '<tr>
					<td colspan="4"><h3>Orders for '. date('D jS F', strtotime($curr_date)) .'</h3></td>
					</tr>';
		}
		$week_total += $o['oi_price'];
		echo '<tr class="vat" style="border-bottom:1px solid #ccc; line-height:30px;">';
			 //<td>Order '. $o['oi_id'] .'</td>
		echo '<td>';
		echo '<a href="' . product_url($o) . '">' . $o['p_name'] . '</a>';
		if (isset($o['oi_source_or']) && $o['oi_source_or'] != null)
		{
			echo '<br /><small class="oi-recurring-note">This is part of an <a href="' . site_url('order/ongoing') . '">ongoing order</a>.</small>';
		}
		echo '</td>
			 <td>'. $o['oi_qty'] .' '. $o['pu_short_plural'] .'</td>
			 <td> &pound;'. number_format($o['oi_price'], 2) .'</td>
			 <td style="text-align:right; width: 230px;">';
				if ($o['oi_status'] == 'Reserved' || $o['oi_status'] == 'Expired')
				{
					if ($o['oi_status'] == 'Reserved')
					{
						echo '<em>Item reserved until '. date('g:ia', strtotime($o['oi_ordered_date'] .' + '. $this->config->item('item_reserved_timeout'))) .'</em><br />';
					}
					else
					{
						echo '<em>Stock no longer reserved</em><br />';
					}
					$unconfirmed++;
					echo form_open(current_url(), array('id' => 'confirm_form', 'style' => 'display:inline;'));
						echo '<input type="hidden" name="oi_id" value="'. $o['oi_id'] .'">';
						echo '<input type="hidden" name="oi_new_status" value="Cancelled">';
						echo '<input type="submit" class="btn noprint" value="Remove">';
					echo form_close();
					echo form_open(current_url(), array('id' => 'confirm_form', 'style' => 'display:inline;'));
						echo '<input type="hidden" name="oi_id" value="'. $o['oi_id'] .'">';
						echo '<input type="hidden" name="oi_new_status" value="Confirmed">';
						echo '<input type="submit" class="btn noprint" value="Confirm Order">';
					echo form_close();
				}
				elseif ($o['oi_status'] == 'Confirmed')
				{
					//do they have time to cancel it?
					if ($o['can_cancel'] == TRUE)
					{
						echo form_open(current_url(), array('id' => 'confirm_form', 'style' => 'display:inline;'));
							echo '<input type="hidden" name="oi_id" value="'. $o['oi_id'] .'">';
							echo '<input type="hidden" name="oi_new_status" value="Cancelled">';
							echo '<input type="submit" class="btn noprint" value="Cancel">';
						echo form_close();
						echo '<span style="width:50px;"> </span>';
						echo '<em>Confirmed</em>';
					}
					else
					{
						echo '<em>Confirmed</em>';
						echo '<br /><small>Delivery soon, can not be cancelled online.</small>';
					}
				}
				elseif ($o['oi_status'] == 'Unavailable')
				{
					echo '<em>Product no longer available</em>';
				}
			echo '</td>';
		echo '</tr>';
	}
	echo '<tr>
			<td></td>
			<td><strong>Delivery Total</strong></td>
			<td><strong>&pound;'. number_format($week_total, 2) .'</strong></td>
			<td></td>
		</tr>';

			//display notes
			if (isset($order_notes[$curr_date]))
			{
				foreach($order_notes[$curr_date] as $note)
				{
					echo '<tr>
							<td>' . $note['on_text'] . '</td>
						</tr>';
				}
			}

			//display option to add note
			echo form_open(current_url(), array('id' => 'note_form', 'style' => 'display:inline;'));
			echo '<tr class="noprint">
						<input type="hidden" name="on_delivery_date" value="'. $curr_date .'">
						<td><textarea name="on_text" style="width:100%;"></textarea></td>
						<td><input type="submit" class="btn" value="Add Note"></td>
				  </tr>';
			echo form_close();
	echo '</table>';

	if ($unconfirmed > 0)
	{
		echo form_open(current_url(), array('id' => 'confirm_form'));
			echo '<input type="hidden" name="oi_id" value="all">';
			echo '<input type="hidden" name="oi_new_status" value="Confirmed">';
			echo '<input type="submit" class="btn noprint" style="float:right;" value="Confirm All Orders">';
		echo form_close();
	}
}
else
{
	echo 'No orders found.';
}
?>

<br />
<div style="margin: 20px; text-align:center; width:100%;">
<a href="/products" class="btn btn-large noprint" style="font-size:16px; padding:10px 25px;">Return to the Food Hub and continue clicking...</a>
</div>
