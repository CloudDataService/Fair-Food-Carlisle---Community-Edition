<?php

	//if it's a new customer - totals
	if ($curr_customer['id'] != $o['oi_u_id'])
	{
		//customer total line
		echo '<tr style="font-weight:bold;">
			<td colspan="2" style="text-align:right; padding-right:25px;"><em>Customer Total</em></td>
			<td>'. $curr_customer['items'] .' items</td>
			<td>&pound;'. number_format($curr_customer['price'], 2) .'</td>
		</tr>';
		//display notes
		if (isset($order_notes[ date('Y-m-d 00:00:00', $date) ]))
		{
			foreach($order_notes[ date('Y-m-d 00:00:00', $date) ] as $note)
			{
				if ($note['on_u_id'] == $curr_customer['id'] )
				{
					echo '<tr>
							<td colspan="2">' . $note['on_text'] . '</td>
						</tr>';
				}
			}
		}
		//display printing notes
		if ($this->input->get('print') == 'yes')
		{
			echo '<tr>
					<td colspan="2" style="text-align:left; padding-bottom: 40px;">
						<strong>Packed by:</strong>
					</td>
				</tr>';
			//put a footer at the end
			echo '<tr>
					<td colspan="2">' . config_item('footer_picking_list')  . '</td>
				  </tr>';
			//end the table and restart, so we can page break
			echo '</table>
					<div class="break">&nbsp;</div>
				<table class="results">';
		}
	}

	//if it's a new group
	if ($curr_group['id'] != $o['u_bg_id'] && $this->input->get('print') != 'yes')
	{
		//total line
		echo '<tr style="font-weight:bold;">
			<td colspan="2" style="text-align:right; padding-right:25px;"><em>Group Total</em></td>
			<td>'. $curr_group[$curr_group['id']]['items'] .' items</td>
			<td>&pound;'. number_format($curr_group[$curr_group['id']]['price'], 2) .'</td>
		</tr>';
		$curr_group['items'] = $curr_group['price'] = 0;

	}
?>
