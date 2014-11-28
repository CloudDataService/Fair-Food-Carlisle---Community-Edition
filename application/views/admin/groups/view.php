<h2>Buying Group</h2>

	<table class="form auto add-bottom">
		<tr class="vat">
			<th>Group Name</th>
			<td>
				<?php if (@$group['bg_name'])
					{echo $group['bg_name'];}
					else {echo 'Unknown'; }
				?>
			</td>
		</tr>

		<tr class="vat">
			<th>Group advocate</th>
			<td>
				<?php if (@$group['advocate_name'])
					{
						echo $group['advocate_name'];
						echo '<br />'. $group['advocate_email'];
						echo '<br />'. $group['advocate_phone'];
					}
					else {echo 'Unknown'; }
				?>
			</td>
		</tr>

		<tr class="vat">
			<th>The delivery address is</th>
			<td>
				<?php
					echo @$group['bg_addr_line1']
					.'<br />'. @$group['bg_addr_line2']
					.'<br />'. @$group['bg_addr_city']
					.'<br />'. @$group['bg_addr_pcode']
					.'<br />'. @$group['bg_addr_note'];
				?>
			</td>
		</tr>

		<tr class="vat">
			<th>Sign up Code <span class="hint">Share this code to let other people <br />register as part of the group</span></th>
			<td style="font-size:22px; vertical-align:middle; text-align:center; background:#eee">
				<?php
					echo @$group['bg_code'];
				?>
			</td>
		</tr>

		<tr class="vat">
			<th>Number of customers</th>
			<td>
				<?php
					echo @$group['bg_member_count'];
				?>
				<a href="<?php echo site_url('admin/users/index?u_bg_id='.$group['bg_id']); ?>" class="btn">View members</a>
			</td>
		</tr>

		<tr class="vat">
			<th>Orders</th>
			<td>
				<?php
				if (@$orders)
				{
					foreach($orders as $o)
					{
						echo date('dS M Y', strtotime($o['oi_delivery_date'])) . ' from '. $o['o_suppliers'] . ' supplier(s) for '. $o['o_customers'] . ' customer(s).';
						echo ' - <strong>'. $o['oi_status'] .'</strong> <br />';
					}
				}
				else
				{
					echo 'None';
				}
				?>
			</td>
		</tr>
	</table>
