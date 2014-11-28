<h2>Bill #<?php echo $bill['b_id']; ?></h2>

	<table class="form auto add-bottom">

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
					if ($bill['b_status'] == 'Paid')
					{
						echo 'Bill Paid';
					}
					if ($bill['b_status'] == 'Pending')
					{
						echo 'Payment Due';
					}
					else if ($bill['b_status'] == 'Draft')
					{
						echo 'Unconfirmed<br /><small>FFC Staff will confirm there are no changes to be made to the bill, and then you can pay online.</small>';
					}
				?>
			</td>
		</tr>

		<?php
			if ($bill['b_status'] == 'Paid')
			{
		?>
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
		<?php
			}
			else if ($bill['b_status'] == 'Draft')
			{
		?>

		<?php
			}
			else
			{
		?>
				<tr class="vat">
					<th>Automate Payments</th>
					<td>
						<?php
							$gc_limit = 50.00;
							if ($gc_limit < $bill['b_price']) { $gc_limit = $bill['b_price']; }
							echo form_open(site_url('bill/gocardless_payment/'.$bill['b_id']), array('id' => 'bill_pay_form'));
								echo '<input type="hidden" name="automate_payments" value="1" />';
								echo 'Pay this bill of &pound'. number_format($bill['b_price'], 2) .' and authorise Go Cardless to take payments each week';
								echo '<br />up to the value of &pound;<input type="text" name="gc_max_payment" size="1" value="'. number_format($gc_limit, 2) .'" /> per week.';
								echo '<br /><input type="submit" class="btn" value="Make payments with Go Cardless" />';
							echo form_close();
						?>
					</td>
				</tr>
				<tr class="vat">
					<th>Pay This Bill</th>
					<td>
						<?php
							echo form_open(site_url('bill/gocardless_payment/'.$bill['b_id']), array('id' => 'bill_pay_form'));
								echo '<input type="hidden" name="make_payment" value="1" />';
								echo '<input type="submit" class="btn" value="Pay only this bill with Go Cardless" />';
							echo form_close();
						?>
					</td>
				</tr>

				<tr class="vat">
					<th>Alternative Payments</th>
					<td>
						Costumers can also pay cash or cheque on delivery.
						<br />Cheques should be made out to <em>Sustainable Carlisle Ltd</em>.
					</td>
				</tr>

		<?php
			}
		?>
		<tr class="vat">
			<th>Itemised</th>
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

	</table>
