
<h3>Ongoing Orders</h3>

<p>
The following products will continue to be ordered automatically until you choose to stop them.
</p>
<p>
Ongoing orders will be shown on your <a href="<?= site_url('order/orders'); ?>">orders page</a> approximately <?= $this->config->item('recuring_order_buffer'); ?> before they are delivered. If you wish to cancel items for only a certain week(e.g. for a holiday), you should do so from that page.
</p>


<?php

if (isset($orders_recurring) )
{
	echo '<table class="results">';
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
	echo 'No ongoing orders have been setup yet.'; //no recurring orders to show
}

?>


<br />
<div style="margin: 20px; text-align:center; width:100%;">
<a href="/products" class="btn btn-large" style="font-size:16px; padding:10px 25px;">Return to the Food Hub and continue clicking...</a>
</div>
