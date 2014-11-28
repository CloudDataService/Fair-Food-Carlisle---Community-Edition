<h2>Picking List for delivery on <?php echo date('jS F Y', $date); ?></h2>

<?php
	if (isset($search['u_bg_id']) && !isset($search['oi_u_id']))
	{
		echo '<p>Only showing orders to your buying group.</p>';
	}
	if (isset($search['oi_s_id']))
	{
		if (isset($orders[0]['s_name']))
		{
			echo '<p>Only showing orders for produce supplied by '. $orders[0]['s_name'] .'.</p>';
		}
		else
		{
			echo '<p>Only showing orders for produce you supply.</p>';
		}
	}
?>
<p>
	Page generated on <strong><?php echo date('D jS F Y \a\t G:ia', time()); ?></strong>.
	<?php
	if ($this->input->get('print') != 'yes')
	{
		echo '<a style="float:right;" href="'. current_url() .'?print=yes" class="btn">Print</a>';
	}
	?>
</p>

<?php
if (@$orders)
{
	$unconfirmed = 0;
	$grand['items'] = $grand['price'] = 0;
	$curr_group['id'] = $orders[0]['u_bg_id'];
	$curr_group['items'] = $curr_group['price'] = 0;
	$curr_customer['id'] = $orders[0]['oi_u_id'];
	$curr_customer['items'] = $curr_customer['price'] = 0;
	if (config_item('delivery_options') != null)
	{
		$curr_customer['delivery_detail'] = $orders[0]['u_delivery_type'];
	}
	echo '<table class="results">';
		echo '<tr><td colspan="4"><h3>Buying Group: '. $orders[0]['bg_name'] .'</h3></td></tr>';
		if (config_item('delivery_options') != null)
		{
			$delivery_types = config_item('delivery_options');
			$delivery = $delivery_types[ $curr_customer['delivery_detail'] ];
			if ($curr_customer['delivery_detail'] == 'home_delivery') {
				$delivery .= ' to <strong>'. multiline_address($orders[0], ', ') . '</strong>';
			}
			echo '<tr style="text-align:left;"><th colspan="4">Delivery: ' . $delivery . '</th></tr>';
		}
		echo '<tr><th colspan="4" class="customer-name" style="text-align:left; font-weight:bold;">'. $orders[0]['u_title'] .' '. $orders[0]['u_fname'] .' '. $orders[0]['u_sname'] .' ';
		if (!isset($search_u_id)) {
			echo '<a href="'. current_url() .'/'. $curr_customer['id'] .'" class="noprint"><img src="'. site_url('img/icons/view.png') .'" title="view individual picking list"></a>';
		}
		echo '</th></tr>';
	foreach($orders as $o)
	{
		$new = array();

		// Define current array
		if (!isset($curr_group[$o['u_bg_id']])) {
			$curr_group[$o['u_bg_id']] = array('price'=>0, 'items'=>0);
		}
		//display totals, if appropriate
		$this->load->view('admin/picking_lists/view-totals-part', compact('curr_customer', 'curr_group', 'o', 'date'));

		//if customer/group has changed, update totals (we can't do this in the partial view because vars don't come back)
		if ($curr_customer['id'] != $o['oi_u_id'])
		{
			//clear customer totals
			$curr_customer['items'] = $curr_customer['price'] = 0;
			$curr_customer['id'] = $o['oi_u_id'];
			$new['customer'] = TRUE;
		}
		if ($curr_group['id'] != $o['u_bg_id'])
		{
			//new BG title
			$curr_group['id'] = $o['u_bg_id'];
			$new['bg'] = TRUE;
		}

		//display new titles, if appropriate
		$this->load->view('admin/picking_lists/view-titles-part', compact('new', 'o', 'search_u_id', 'date'));

		echo '<tr style="border-bottom:1px solid #ccc; line-height:30px;" orderid="'. $o['oi_id'] .'">';
			 //<td>Order '. $o['oi_id'] .'</td>
		echo '<td>'. $o['s_name'] .'</td>
			 <td>'. $o['p_name'] .'</td>
			 <td>'. $o['oi_qty'] .' '. $o['pu_short_plural'] .'</td>
			 <td>&pound;'. number_format($o['oi_price'], 2) .'</td>';
		echo '</tr>';



		//add up total vars, ready for the next interation o fthe loop
		$curr_group[$o['u_bg_id']]['price'] += $o['oi_price'];
		$curr_group[$o['u_bg_id']]['items'] += $o['oi_qty'];

		//$curr_customer[$o['u_bg_id']]['price'] += $o['oi_price'];
		//$curr_customer[$o['u_bg_id']]['items'] += $o['oi_qty'];
		$curr_customer['price'] = $curr_customer['price'] + $o['oi_price'];
		$curr_customer['items']+=$o['oi_qty'];

		$grand['price'] = $grand['price'] + $o['oi_price'];
		$grand['items']+=$o['oi_qty'];
	}

	//force totals to be appropriate (we finished the loop)
	$o = null;

	//display totals, if appropriate
	$this->load->view('admin/picking_lists/view-totals-part', compact('curr_customer', 'curr_group', 'o', 'date'));

	if (!isset($search_u_id) && $this->input->get('print') != 'yes')
	{
		//picking list total
		echo '<tr style="font-weight:bold;">
			<td colspan="4">&nbsp;</td>
		</tr>';
		echo '<tr style="font-weight:bold;">
			<td colspan="2" style="text-align:right; padding-right:25px;"><em>Picking List Total</em></td>
			<td>'. $grand['items'] .' items</td>
			<td>&pound;'. number_format($grand['price'], 2) .'</td>
		</tr>';
	}
	echo '</table>';
}
else
{
	echo 'No orders found for this date.';
}
?>
