<h2>Buying Group</h2>

<p>Thank you for getting involved with Fair Food Carlisle. Please provide the following details so that you and other members of the buying group can start shopping.</p>

<?php echo form_open(current_url(), array('id' => 'new_bg_form')); ?>

	<table class="form">
		<tr class="vat">
			<th><label for="bg_name">Buying Group Name</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="bg_name" id="bg_name" maxlength="80" <?php if (@$group['bg_name']) {echo 'value="'.$group['bg_name'].'"';} ?> />
			</td>
		</tr>

		<tr class="vat">
			<th>Address</th>
			<td>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="bg_addr_line1">Line 1</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="bg_addr_line1" id="bg_addr_line1" maxlength="60" <?php if (@$group['bg_addr_line1']) {echo 'value="'.$group['bg_addr_line1'].'"';} ?> />
			</td>
		</tr>

		<tr class="vat">
			<th><label for="bg_addr_line2">Line 2</label></th>
			<td>
				<input type="text" name="bg_addr_line2" id="bg_addr_line2" maxlength="60" <?php if (@$group['bg_addr_line2']) {echo 'value="'.$group['bg_addr_line2'].'"';} ?> />
			</td>
		</tr>

		<tr class="vat">
			<th><label for="bg_addr_city">City</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="bg_addr_city" id="bg_addr_city" maxlength="60" <?php if (@$group['bg_addr_city']) {echo 'value="'.$group['bg_addr_city'].'"';} else {echo 'value="'. $this->config->item('default_bg_town') .'"';} ?> />
			</td>
		</tr>

		<tr class="vat">
			<th><label for="bg_addr_pcode">Postcode</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="bg_addr_pcode" id="bg_addr_pcode" maxlength="9" style="width:65px;" <?php if (@$group['bg_addr_pcode']) {echo 'value="'.$group['bg_addr_pcode'].'"';} ?> />
			</td>
		</tr>

		<tr class="vat">
			<th><label for="bg_addr_note">Delivery Note<br />(optional)</label></th>
			<td>
				<textarea name="bg_addr_note" id="bg_addr_note" maxlength="200">
					<?php if (@$group['bg_addr_note']) {echo $group['bg_addr_note'];} ?>
				</textarea>
			</td>
		</tr>


		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Save Details" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>
