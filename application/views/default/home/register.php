<h2>Register</h2>
<p>Hello, we just need a few details so that you are ready to order products through the website.</p>
<div class=""><ul class="error-msg" style="display:none;"></ul></div>
<?php echo form_open(current_url(), array('id' => 'account_form')); ?>
	<table class="form vat">

		<tr class="hidden">
			<th><label for="u_title">Title</label></th>
			<td>
				<select name="u_title" id="u_title" class="other">
					<option value="">-- Please select --</option>
					<?php
					foreach($this->config->config['titles'] as $title)
					{
						echo '<option value="' . $title . '" ' . ($title == @$user['u_title'] ? 'selected="selected"' : '') . '>' . $title . '</option>';
					}
					?>
				</select>
			</td>
		</tr>

		<tr>
			<th><label for="u_fname">First name</label> <span class="orange">*</span></th>
			<td><input type="text" name="u_fname" id="u_fname" class="required" style="width:250px; text-transform:capitalize;" /></td>
		</tr>

		<tr>
			<th><label for="u_sname">Last name</label> <span class="orange">*</span></th>
			<td><input type="text" name="u_sname" id="u_sname" class="required" style="width:250px; text-transform:capitalize;" /></td>
		</tr>

		<tr>
			<th><label for="u_email">Email address</label> <span class="orange">*</span></th>
			<td><input type="text" name="u_email" id="u_email" class="required" style="width:250px;" <?php if (@$user['u_email']) {echo 'value="'.$user['u_email'].'"';} ?> /></td>
		</tr>

		<tr>
			<th><label for="u_email_confirm">Confirm email</label> <span class="orange">*</span></th>
			<td><input type="text" name="u_email_confirm" class="required" id="u_email_confirm" style="width:250px;" /></td>
		</tr>

		<tr>
			<th><label for="u_pword">Password</label> <span class="orange">*</span></th>
			<td>
				<input type="password" name="u_pword" id="u_pword" class="required" style="width:250px;background-color:#ffffff;" autocomplete="off" />
				<!--<small style="display:block; width:250px;">Must be more than 6 characters long.</small>-->
				<small class="left" id="u_pword_display"></small>
			</td>
		</tr>

		<tr>
			<th><label for="u_password_confirm">Confirm password</label> <span class="orange">*</span></th>
			<td><input type="password" name="u_password_confirm" id="u_password_confirm" class="required" style="width:250px;" autocomplete="off" /></td>
		</tr>


		<?php if (config_item('delivery_registration_text') != null): ?>
			<tr>
				<td colspan="2"><?= config_item('delivery_registration_text') ?></td>
			</tr>
		<?php endif; ?>
		<?php if (config_item('delivery_options') != null): ?>
			<tr>
				<th></th>
				<td>
					<?php foreach(config_item('delivery_options') as $option => $label): ?>
						<input type="radio" name="u_delivery_type" class="js-delivery_type" id="delivery_type_<?= $option ?>" value="<?= $option ?>" >
						<label for="delivery_type_<?= $option ?>"><?= $label; ?></leabel>
						<br>
					<?php endforeach; ?>
				</td>
			</tr>
		<?php endif; ?>


		<?php if (config_item('delivery_options') != null): ?>
			<tr class="js-delivery-optional js-optional-home_delivery">
				<th><label for="u_addr_line1">Address line 1</label> <span class="orange">*</span></th>
				<td><input type="text" name="u_addr_line1" id="u_addr_line1" style="width:250px;" /></td>
			</tr>
			<tr class="js-delivery-optional js-optional-home_delivery">
				<th><label for="u_addr_line2">Address line 2</label> <span class="orange">*</span></th>
				<td><input type="text" name="u_addr_line2" id="u_addr_line2" style="width:250px;" /></td>
			</tr>
			<tr class="js-delivery-optional js-optional-home_delivery">
				<th><label for="u_addr_city">City</label> <span class="orange">*</span></th>
				<td><input type="text" name="u_addr_city" id="u_addr_city" style="width:250px;" <?php if (element('u_addr_city', $user) != null) {echo 'value="'.$user['u_addr_city'].'"';} else {echo 'value="'. $this->config->item('default_bg_town') .'"';} ?> /></td>
			</tr>
			<tr class="js-delivery-optional js-optional-home_delivery">
				<?php if (config_item('delivery_standard_postcode_areas') != null): ?>
					<script type="text/javascript">var allowed_pcode_areas = [<?php echo '"' . implode('", "', config_item('delivery_standard_postcode_areas')) . '"'; ?>];</script>
				<?php endif; ?>
				<th><label for="u_addr_pcode">Post Code</label> <span class="orange">*</span></th>
				<td><input type="text" name="u_addr_pcode" id="u_addr_pcode" maxlength="9" style="width:75px;" /></td>
			</tr>
			<?php if (config_item('delivery_nonstandard_message') != null): ?>
				<tr class="js-delivery-nonstandard-pcode">
					<td colspan="2"><?= config_item('delivery_nonstandard_message'); ?></td>
				</tr>
			<?php endif;?>
		<?php endif;?>


	<?php if (isset($groups_list)): ?>
		<tr class="js-delivery-optional js-optional-selected_group">
			<th>
				<label for="u_bg_id">Buying Group</label> <?php if (config_item('default_signup_group') == null): ?><span class="orange">*</span><?php endif; ?>
			</th>
			<td>
				<select name="u_bg_id" id="u_bg_id" class="other">
					<option value="">-- Please select --</option>
					<?php
					foreach($groups_list as $g)
					{
						echo '<option value="' . $g['bg_id'] . '" ' . ($g['bg_id'] != null && $g['bg_id'] == element('u_bg_id', $user) ? 'selected="selected"' : '') . '>' . $g['bg_name'] . '</option>';
					}
					?>
				</select>
				<br /><small>This will be used as a delivery location</small>
			</td>
		</tr>
	<?php else: ?>
		<tr>
			<th>
				<label for="bg_code">Group Sign-up Code</label> <?php if (config_item('default_signup_group') == null): ?><span class="orange">*</span><?php endif; ?>
			</th>
			<td>
				<input type="text" name="bg_code" id="bg_code" class="required" style="width:80px;" />
				<small style="display:block; width:250px;">Please get this from <a href="<?php echo site_url('contact/register'); ?>"><?php echo config_item('site_name'); ?></a>.</small>
			</td>
		</tr>
	<?php endif; ?>

		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Register" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>
