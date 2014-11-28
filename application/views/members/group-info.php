<h2>Buying Group</h2>

<p><?php echo config_item('site_name'); ?> is uniting local producers and customers for the benefit of the whole community. We will deliver to your workplace or organisation central location to minimise food miles.</p>

	<table class="form auto add-bottom">
		<tr class="vat">
			<th>You are part of the buying group</th>
			<td>
				<?php if (@$group['bg_name'])
					{echo $group['bg_name'];}
					else {echo 'Unknown'; }
				?>
			</td>
		</tr>

		<tr class="vat">
			<th>Your <?php echo config_item('site_name'); ?> advocate is</th>
			<td>
				<?php if (@$group['advocate_name'])
					{echo $group['advocate_name'];}
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

		<?php if (config_item('use_signup_code') == 1): ?>
			<tr class="vat">
				<th>Sign up Code <span class="hint">Share this code to let other people <br />register as part of your group</span></th>
				<td style="font-size:22px; vertical-align:middle; text-align:center; background:#eee">
					<?php
						echo @$group['bg_code'];
					?>
				</td>
			</tr>
		<?php endif; ?>

	</table>

<p>If you have problems with your order, you should first talk to your buying advocate. You can also contact <a href="<?php echo site_url('contact'); ?>"><?php echo config_item('site_name'); ?></a>.</p>
