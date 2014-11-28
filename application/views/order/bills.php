Your bills are displayed below.

<?php

if (isset($bills) )
{
	echo '<table class="results">';
	echo '<tr><td colspan="4"><h3>Bills</h3> <small>Pending bills will be due once '. $this->config->item('site_abbr') .' staff have confirmed delivery.</small></td></tr>';
	foreach($bills as $b)
	{
		echo '<tr class="vat" style="border-bottom:1px solid #ccc; line-height:30px;">';
			 //<td>Order '. $o['oi_id'] .'</td>
		echo '<td><a href="'. site_url('bill/view/'.$b['b_id']) .'">Bill #'. $b['b_id'] .'</a></td>
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
	echo '<p>No bills found.'; //no bills to show
}
