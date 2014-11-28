<?php

	//display titles on every print page
	if (($this->input->get('print') == 'yes') && (isset($new['customer']) && $new['customer'] == TRUE))
	{
		echo '<h2>Picking List for delivery on ' . date('jS F Y', $date) . '</h2>';
		echo '<p>Page generated on <strong>' . date('D jS F Y \a\t G:ia', time()) . '</strong>.</p>';
	}

	if ( (isset($new['bg']) && $new['bg'] == TRUE)
		|| (isset($new['customer']) && $new['customer'] == TRUE)
	  )
	{
			echo '<tr><td colspan="4"><h3>Buying Group: '. $o['bg_name'] .'</h3></td></tr>';
	}

	if (isset($new['customer']) && $new['customer'] == TRUE)
	{
		if (config_item('delivery_options') != null)
		{
			$delivery_types = config_item('delivery_options');
			$delivery = $delivery_types[ $o['u_delivery_type'] ];
			if ($o['u_delivery_type'] == 'home_delivery') {
				$delivery .= ' to <strong>'. multiline_address($o, ', ') . '</strong>';
			}
			echo '<tr style="text-align:left;"><th colspan="4">Delivery: ' . $delivery . '</th></tr>';
		}

		echo '<tr>
				<th colspan="4" style="text-align:left; font-weight:bold;">'.
					$o['u_title'] .' '. $o['u_fname'] .' '. $o['u_sname'] .' ';
			if (!isset($search_u_id)) { echo '<a href="'. current_url() .'/'. $o['oi_u_id'] .'"><img src="'. site_url('img/icons/view.png') .'" title="view individual picking list"></a>'; }
		echo '
				</th>
			</tr>';
	}

?>
