<h2>Contact Us</h2>

<p>Hello, please fill in the details below to get in touch?</p>
<?php if (@$error) { echo $error; } ?>


<?php echo form_open(current_url(), array('id' => 'contact_form')); ?>
	<table class="form">
		<tr>
			<th><label for="cf_name">Your name</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="cf_name" id="cf_name" maxlength="100" <?php if (@$input['cf_name']) {echo 'value="'.$input['cf_name'].'"';} ?> />
			</td>
		</tr>

		<tr>
			<th><label for="cf_email">Your e-mail address</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="cf_email" id="cf_email" maxlength="80" <?php if (@$input['cf_email']) {echo 'value="'.$input['cf_email'].'"';} ?> />
			</td>
		</tr>

		<tr>
			<th><label for="cf_phone">Your phone number</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="cf_phone" id="cf_phone" maxlength="16" <?php if (@$input['cf_phone']) {echo 'value="'.$input['cf_phone'].'"';} ?> />
			</td>
		</tr>

		<tr>
			<th><label for="cf_subject">Contact us about</label></th>
			<td>
				<select name="cf_subject" id="cf_subject"> <span class="orange">*</span>
					<option value="">-- Please Select --</option>
					<?php
					$reasons = config_item('contact_reasons');
					foreach($reasons as $r => $l)
					{
						echo '<option value="' . $r . '" ' . ($r == @$link_reason ? 'selected="selected"' : '') . '>' . $l . '</option>';
					}
					?>
				</select>
			</td>
		</tr>

		<tr>
			<th><label for="cf_message">Your Message</label> <span class="orange">*</span></th>
			<td>
				<textarea name="cf_message" id="cf_message" maxlength="250"><?php if (@$input['cf_message']) {echo $input['cf_message'];} ?></textarea>
			</td>
		</tr>

		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Send Message" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>

