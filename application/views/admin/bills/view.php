<h2>Member Bill</h2>

	<table class="form auto add-bottom">
		<tr class="vat">
			<th>Bill ID</th>
			<td>
				<?php echo $bill['b_id']; ?>
			</td>
		</tr>

		<tr class="vat">
			<th>Customer Name</th>
			<td>
				<?php echo $bill['customer_name'];	?>
			</td>
		</tr>

		<tr class="vat">
			<th>Contact Details</th>
			<td>
				<?php
					if ($bill['customer_email'])
					{
						echo $bill['customer_email'];
					}
					if ($bill['customer_email'] && $bill['customer_phone'])
					{
						echo '<br />';
					}
					if ($bill['customer_phone'])
					{
						echo $bill['customer_phone'];
					}
				?>
			</td>
		</tr>

		<tr class="vat">
			<th><?php
				if ($bill['b_status'] != 'Paid')
				{
					echo 'Amount Due';
				}
				else
				{
					echo 'Amount Paid';
				}
				?>
			</th>
			<td>
				<?php echo '&pound'. number_format($bill['b_price'], 2);	?>
			</td>
		</tr>

		<tr class="vat">
			<th>Bill Status</th>
			<td>
				<?php
					echo $bill['b_status'];
					if ($bill['b_status'] == 'Pending')
					{
						echo ' Payment';
					}
					else if ($bill['b_status'] == 'Draft')
					{
						echo '<br /><small>Mark this bill as "due" to allow the customer to pay online.</small>';
					}
				?>
			</td>
		</tr>

		<tr class="vat">
			<th>Payment Method</th>
			<td>
				<?php
					if (@$bill['b_payment_method'])
					{
						echo $bill['b_payment_method'];
					}
				?>
			</td>
		</tr>

		<tr class="vat">
			<th>Items</th>
			<td>
				<?php
				if (@$items)
				{
					foreach($items as $i)
					{
						echo $i['oi_qty'] . $i['pu_short_plural'] .' of '. $i['p_name'] . ' from '. $i['s_name'] .
						', delivered on '. date('dS M Y', strtotime($i['oi_delivery_date'])) .
						'. Price &pound;'. number_format($i['oi_price'], 2) . '<br />';
					}
				}
				else
				{
					echo 'None Found';
				}
				?>
				<?php
				if (@$adjustments)
				{
					foreach($adjustments as $a)
					{
						echo '*** '. $a['ba_description'];
						if ( substr($a['ba_price'], 0, 1) == '-')
						{
							echo '. Deduction of &pound'. substr($a['ba_price'], 1);
						}
						else
						{
							echo '. Addition of &pound'. $a['ba_price'];
						}
						echo ' ***<br />';
					}
				}
				?>
			</td>
		</tr>

		<?php
		if ($bill['b_status'] != 'Paid')
		{
		?>
			<tr class="vat">
				<th>Add Adjustment</th>
				<td>
					<?php
						echo form_open(current_url(), array('id' => 'bill_adjustment_form'));
							echo 'Description: <input type="text" name="ba_description" id="ba_description" maxlength="250" style="width:400px;" />
								  <br /><small>E.g. Name of product and reason you are changing the price.</small><br />';
							echo 'Addition: &pound;<input type="text" name="ba_price" id="ba_price" maxlength="10" style="width:40px;" />
								  <br /><small>Include -(minus) symbol in price to make a reduction/discount, e.g. -10.50.</small><br />';
							echo '<input type="submit" class="btn" value="Add adjustment to bill" style="float:right;" />';
						echo form_close();
					?>
				</td>
			</tr>
		<?php
		}
		?>

		<?php
		if ($bill['b_status'] != 'Paid')
		{
		?>
			<tr class="vat">
				<th>Add Discount</th>
				<td>
					<?php
						echo form_open(current_url(), array('id' => 'bill_adjustment_form'));
							echo 'Description: <input type="text" name="bd_description" id="bd_description" maxlength="250" style="width:170px;" value="Discount" />';
							echo '<br />Value: <input type="text" name="bd_price" id="bd_price" maxlength="10" style="width:30px;" value="20" />&#37; off the current amount due.';
							echo '<input type="hidden" name="bd_total" id="bd_total" value="'. number_format($bill['b_price'], 2) .'" />';
							echo '<br /><input type="submit" class="btn" value="Apply discount to bill" style="float:right;" />';
							echo '<br /><small>The discounted amount will not be updated if adjustments are added after applying the discount.</small><br />';
						echo form_close();
					?>
				</td>
			</tr>
		<?php
		}
		?>

		<?php
		if ($bill['b_status'] == 'Draft')
		{
		?>
			<tr class="vat">
				<th>Mark as due</th>
				<td>
					<?php
						echo form_open(current_url(), array('id' => 'bill_paid_form'));
						echo '<small>If the member has pre-authorised Go Cardless, the system will attempt to take a payment from their bank.</small>';
							echo '<br /><small>Adjustments can not be added to bills once the bill is marked as due, please add those first.</small>';
							echo '<input type="hidden" name="bill_due" value="1" />';
							echo '<br /><input type="submit" class="btn" value="Mark bill as due" style="float:right;" />';
						echo form_close();
					?>
				</td>
			</tr>
		<?php
		}
		?>

		<?php
		if ($bill['b_status'] != 'Paid')
		{
		?>
			<tr class="vat">
				<th>Mark as paid</th>
				<td>
					<?php
						echo form_open(current_url(), array('id' => 'bill_paid_form'));
							echo '<select name="b_payment_method" id="b_payment_method" class="other">
									<option value="">-- Please select --</option>';
								foreach($this->config->config['bill_fields']['methods'] as $method)
								{
									if ($method != 'Go Cardless')
									{
										echo '<option value="' . $method . '" ' . ($method == @$bill['b_payment_method'] ? 'selected="selected"' : '') . '>' . $method . '</option>';
									}
								}
							echo '</select>';

							echo '<br /><small>Adjustments can not be added to bills once they are paid, please add those first.</small>';
							echo '<br /><small>The customer will be sent an e-mail informing them that their bill has been paid.</small>';
							echo '<br /><input type="submit" class="btn" value="Mark bill as paid" style="float:right;" />';
						echo form_close();
					?>
				</td>
			</tr>
		<?php
		}
		?>
	</table>
