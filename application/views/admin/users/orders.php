<?php if ($this->auth->is_allowed_to('create_member_orders', 'all')): ?>
	<div class="admin-ordering">
		<h2>Place an additional order for this member</h2>
		<?php echo form_open(); ?>
			<label for="search">Product: </label>
			<input id="search" type="text" style="width:245px;">
			<input id="search_id" type="hidden" name="p_id">
			<label for="order_date">Delivery: </label>
			<input type="text" class="datepicker" name="order_date" id="order_date" maxlength="10" style="width:70px;" />
			<label for="oi_quantity">Quantity: </label>
			<input type="text" name="oi_quantity" id="oi_quantity" maxlength="3" style="width:30px;" value="1" />
			<input type="submit" class="btn" value="Place Order" />
		<?php echo form_close(); ?>
		<ul id="stock-details"></ul>
		<em>Stock levels will be updated after ordering. Existing bills will not be updated, if the delivery is for today it will be billed next week.</em>
	</div>
<?php endif; ?>

<h2>Orders for <?php echo $customer['u_fullname']; ?></h2>

<?php

if (@$orders)
{
	$unconfirmed = 0;
	$curr_date = $orders[0]['oi_delivery_date'];
	echo '<table class="results">';
	echo '<tr><td colspan="4"><h3>'. date('D jS F', strtotime($curr_date)) .'</h3></td></tr>';
	foreach($orders as $o)
	{
		if ($curr_date != $o['oi_delivery_date'])
		{
			$curr_date = $o['oi_delivery_date'];
			echo '<tr>
					<td colspan="4"><h3>'. date('D jS F', strtotime($curr_date)) .'</h3></td>
					</tr>';
		}
		echo '<tr class="vat orderitem oi_status_'.$o['oi_status'] .'" style="border-bottom:1px solid #ccc; line-height:30px;" ref="oi'. $o['oi_id'] .'">';
			 //<td>Order '. $o['oi_id'] .'</td>
		echo '<td>'. $o['p_name'] .'</td>
			 <td>'. $o['oi_qty'] .' '. $o['pu_short_plural'] .'</td>
			 <td> &pound;'. number_format($o['oi_price'], 2) .'</td>
			 <td class="oi_status" style="text-align:right; width: 230px;">';
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
				elseif ($o['oi_b_id'] != null)
				{
					echo '<em>Delivered/Billed</em>';
				}
				elseif ($o['oi_status'] == 'Confirmed')
				{
					echo '<em>Confirmed</em>';
					if ($o['oi_delivery_date'] > date('Y-m-d'))
					{
						//confirmed item can be cancelled by admin
						echo form_open(current_url(), array('id' => 'confirm_form', 'style' => 'display:inline;'));
							echo '<input type="hidden" name="oi_id" value="'. $o['oi_id'] .'">';
							echo '<input type="hidden" name="oi_new_status" value="Rejected">';
							echo '<input type="submit" class="btn" value="Cancel">';
						echo form_close();
					}
				}
				elseif ($o['oi_status'] == 'Unavailable')
				{
					echo '<em>Product no longer available</em>';
				}
				elseif ($o['oi_status'] == 'Rejected')
				{
					echo '<em>Removed by staff</em>';
				}
				else
				{
					echo '<em>'. $o['oi_status'] .'</em>';
				}
			echo '</td>';
		echo '</tr>';
	}
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
	echo '<p>No orders found.</p>';
}
?>
