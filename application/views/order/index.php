Below is a summary of your recent <a href="<?= site_url('order/orders'); ?>">Orders</a>, <a href="<?= site_url('order/bills'); ?>">Bills</a>, and <a href="<?= site_url('order/ongoing'); ?>">Ongoing Orders</a>.

<?php

if (isset($bills) && $bills != array() )
{
	echo '<table class="results">';
	echo '<tr><td colspan="4"><h3>Bills Due</h3> <small>Payment is now due for the following bills.</small></td></tr>';
	foreach($bills as $b)
	{
		echo '<tr class="vat" style="border-bottom:1px solid #ccc; line-height:30px;">';
			 //<td>Order '. $o['oi_id'] .'</td>
		echo '<td>Bill #'. $b['b_id'] .'</td>
			 <td>'. $b['b_items'] .' products</td>
			 <td> &pound;'. number_format($b['b_price'], 2) .'</td>
			 <td style="text-align:right; width: 230px;">';
			 	if ($b['b_status'] == 'Draft')
				{
					echo 'Pending';
					echo '';
				}
				else if ($b['b_status'] == 'Pending')
				{
					echo '<strong style="color:red;">Payment Due</strong>';
					echo '<br /><a href="'. site_url('bill/view/'.$b['b_id']) .'" class="btn">Make Payment</a>';
				}
				else if ($b['b_status'] == 'Paid')
				{
					echo 'Paid';
				}
			echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
}
else
{
	echo ''; //no bills to show
}
echo '<p><a href="'. site_url('order/bills') .'" class="btn">View all bills</a></p>';


if (isset($orders_recurring) && $orders_recurring != array() )
{
	echo '<table class="results">';
	echo '<tr><td colspan="4"><h3>Ongoing Orders</h3> <small>Once you confirm, the following products will be automatically ordered on a regular basis until you choose to stop them.</small></td></tr>';
	foreach($orders_recurring as $or)
	{
		echo '<tr class="vat order-recurring '. $or['or_status'] .'" style="border-bottom:1px solid #ccc; line-height:30px;">';
		echo '<td>'. $or['p_name'] .'</td>
			 <td>'. $or['or_qty'] .' '. $or['pu_short_plural'] .'</td>
			 <td>'. ucfirst($or['or_frequency']) .'</td>
			 <td>From '. date('D jS F', strtotime($or['or_started_date'])) .' onwards.</td>';
			 if ($or['or_latest_date'] != null)
			 {
			 	echo '<td>Latest order '. date('D jS F', strtotime($or['or_latest_date'])) .'</td>';
			 }
			 else
			 {
			 	echo '<td></td>';
			 }
		echo '<td style="text-align:right; width: 230px;" class="or_status '. $or['or_status'] .'">';
			 	if ($or['or_status'] == 'Pending')
				{
					echo 'Awaiting Confirmation';
					echo '<br />';
					echo form_open(current_url(), array('id' => 'confirm_form'));
						echo '<input type="hidden" name="or_id" value="'. $or['or_id'] .'">';
						echo '<input type="hidden" name="or_new_status" value="Confirmed">';
						echo '<input type="submit" class="btn" style="float:right;" value="Confirm">';
					echo form_close();
					echo form_open(current_url(), array('id' => 'confirm_form'));
						echo '<input type="hidden" name="or_id" value="'. $or['or_id'] .'">';
						echo '<input type="hidden" name="or_new_status" value="Cancelled">';
						echo '<input type="submit" class="btn" style="float:right;" value="Cancel">';
					echo form_close();
				}
			 	if ($or['or_status'] == 'Confirmed')
				{
					echo 'Ongoing';
					echo form_open(current_url(), array('id' => 'confirm_form'));
						echo '<input type="hidden" name="or_id" value="'. $or['or_id'] .'">';
						echo '<input type="hidden" name="or_new_status" value="Stopped">';
						echo '<input type="submit" class="btn" style="float:right;" value="Stop future orders">';
					echo form_close();
				}
				else if ($or['or_status'] == 'Stopped' || $or['or_status'] == 'Finished')
				{
					echo 'Stopped';
					//echo '<br />This ongoing order was stopped on '. $or['or_finished_date'] .'.';
				}
			echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
}
else
{
	echo ''; //no recurring orders to show
}
echo '<p><a href="'. site_url('order/ongoing') .'" class="btn">View all ongoing orders</a></p>';

?>

<?php

if (@$orders)
{
	$unconfirmed = 0;
	$curr_date = $orders[0]['oi_delivery_date'];
	$week_total = 0.00;
	echo '<table class="results">';
	echo '<tr><td colspan="4"><h3>Next delivery <span style="font-size:14px; color:#666;"> '. date('l jS F', strtotime($curr_date)) .'</span></h3></td></tr>';
	foreach($orders as $o)
	{
		if ($curr_date != $o['oi_delivery_date'])
		{
			echo '<tr>
					<td></td>
					<td><strong>Delivery Total</strong></td>
					<td><strong>&pound;'. number_format($week_total, 2) .'</strong></td>
					<td></td>
				</tr>';
			$week_total = 0;
			$curr_date = $o['oi_delivery_date'];
			echo '<tr>
					<td colspan="4"><h3>Orders for '. date('D jS F', strtotime($curr_date)) .'</h3></td>
					</tr>';
		}
		$week_total += $o['oi_price'];
		echo '<tr class="vat" style="border-bottom:1px solid #ccc; line-height:30px;">';
			 //<td>Order '. $o['oi_id'] .'</td>
		echo '<td>'. $o['p_name'];
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
						echo '<input type="submit" class="btn" value="Remove">';
					echo form_close();
					echo form_open(current_url(), array('id' => 'confirm_form', 'style' => 'display:inline;'));
						echo '<input type="hidden" name="oi_id" value="'. $o['oi_id'] .'">';
						echo '<input type="hidden" name="oi_new_status" value="Confirmed">';
						echo '<input type="submit" class="btn" value="Confirm Order">';
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
							echo '<input type="submit" class="btn" value="Cancel">';
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
			echo '<tr>
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
			echo '<input type="submit" class="btn" style="float:right;" value="Confirm All Orders">';
		echo form_close();
	}
}
else
{
	echo 'No orders found.';
}
echo '<p><a href="'. site_url('order/orders') .'" class="btn">View all orders</a></p>';
?>

<br />
<div style="margin: 20px; text-align:center; width:100%;">
<a href="/products" class="btn btn-large" style="font-size:16px; padding:10px 25px;">Return to the Food Hub and continue clicking...</a>
</div>
