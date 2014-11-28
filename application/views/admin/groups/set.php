<h2><?php echo $group['bg_name']; ?></h2>

<?php echo form_open(current_url(), array('id' => 'group_edit_form')); ?>

	<table class="form auto add-bottom">
		<tr class="vat">
			<th>Group Name</th>
			<td>
				<input type="text" name="bg_name" id="bg_name" maxlength="80" value="<?php echo @$group['bg_name']; ?>" />
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
				<input type="text" name="bg_addr_line1" id="bg_addr_line1" maxlength="60" placeholder="line 1" <?php if (@$group['bg_addr_line1']) {echo 'value="'.$group['bg_addr_line1'].'"';} ?> />
				<br /><input type="text" name="bg_addr_line2" id="bg_addr_line2" maxlength="60" placeholder="line 2" <?php if (@$group['bg_addr_line2']) {echo 'value="'.$group['bg_addr_line2'].'"';} ?> />
				<br /><input type="text" name="bg_addr_city" id="bg_addr_city" maxlength="60" placeholder="city" <?php if (@$group['bg_addr_city']) {echo 'value="'.$group['bg_addr_city'].'"';} ?> />
				<br /><input type="text" name="bg_addr_pcode" id="bg_addr_pcode" maxlength="9" style="width:65px;" placeholder="postcode" <?php if (@$group['bg_addr_pcode']) {echo 'value="'.$group['bg_addr_pcode'].'"';} ?> />
				<br />
			</td>
		</tr>

		<tr class="vat">
			<th><label for="bg_addr_note">Delivery Note<br />(optional)</label></th>
			<td>
				<textarea name="bg_addr_note" id="bg_addr_note" maxlength="200"><?php if (@$group['bg_addr_note']) {echo $group['bg_addr_note'];} ?></textarea>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="bg_deliveryday">Delivery Day of Week</label></th>
			<td>
				<select name="bg_deliveryday" id="bg_deliveryday" class="other">
					<option value="Tuesday">Tuesday</option>
				</select>
				<br /><small>Restricted to Tuesday until phase 2 of the system development.</small>
			</td>
		</tr>
		<tr class="vat">
			<th><label for="bg_status">Group Status</label></th>
			<td>
				<select name="bg_status" id="bg_status" class="other">
					<option value="">-- Please select --</option>
					<?php
					foreach($this->config->config['group_fields']['statuses'] as $status)
					{
						echo '<option value="' . $status . '" ' . ($status == @$group['bg_status'] ? 'selected="selected"' : '') . '>' . $status . '</option>';
					}
					?>
				</select>
				<br /><small><strong>New</strong> groups are awaiting the advocate to login and confirm details which will activate it.
						<br /><strong>Active</strong> is the normal setting for groups.
						<br />Members of <strong>disabled</strong> groups are unable to purchase produce until staff reactiave it.</small>
			</td>
		</tr>


		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Update Group" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>
