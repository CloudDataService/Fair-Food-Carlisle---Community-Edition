<h2>Your Account</h2>
<div class=""><ul class="error-msg" style="display:none;"></ul></div>
<?php echo form_open(current_url(), array('id' => 'account_form')); ?>
	<table class="form vat">
		<tr>
			<th><label for="u_email">Email address</label> </th>
			<td><input type="text" name="u_email" id="u_email" style="width:250px;" <?php if (@$user['u_email']) {echo 'value="'.$user['u_email'].'"';} ?> /></td>
		</tr>

		<tr>
			<th><label for="u_email_confirm">Confirm email</label></th>
			<td><input type="text" name="u_email_confirm" id="u_email_confirm" style="width:250px;" />
				<small style="display:block; width:250px;">Only required if changing your e-mail address.</small></td>
		</tr>

		<tr>
			<th><label for="u_pword">New Password</label></th>
			<td>

				<input type="password" name="u_pword" id="u_pword" style="width:250px;" autocomplete="off" />
				<small class="left" id="u_pword_display"></small>
				<small style="display:block; width:250px;">Leave blank to keep your current password.</small>
				<!--<small style="display:block; width:250px;">Must be more than 6 characters long.</small>-->

			</td>
		</tr>

		<tr>
			<th><label for="u_password_confirm">Confirm password</label> </th>
			<td><input type="password" name="u_password_confirm" id="u_password_confirm" style="width:250px;" autocomplete="off" />
				<small style="display:block; width:250px;">Only required if changing your password.</small></td>
		</tr>

		<tr>
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
			<td><input type="text" name="u_fname" id="u_fname" style="width:250px; text-transform:capitalize;" <?php if (@$user['u_email']) {echo 'value="'.$user['u_fname'] .'"';} ?> /></td>
		</tr>

		<tr>
			<th><label for="u_sname">Last name</label> <span class="orange">*</span></th>
			<td><input type="text" name="u_sname" id="u_sname" style="width:250px; text-transform:capitalize;" <?php if (@$user['u_email']) {echo 'value="'.$user['u_sname'] .'"';} ?> /></td>
		</tr>

		<tr>
			<th><label for="u_telephone">Telephone number</label> </th>
			<td><input type="text" name="u_telephone" id="u_telephone" style="width:90px; text-transform:capitalize;" <?php if (@$user['u_telephone']) {echo 'value="'.$user['u_telephone'] .'"';} ?> /></td>
		</tr>

		<tr>
			<th><label for="u_type">Delivery Type</label></th>
			<td>
				<?php
				$types = config_item('delivery_options');
				echo $types[ $user['u_delivery_type'] ];
				 ?>
			</td>
		</tr>

		<?php if ($user['u_delivery_type'] == 'selected_group'): ?>
			<tr>
				<th><label for="u_bg_name">Buying Group</label></th>
				<td>
					<?php if (@$user['bg_name']) {echo $user['bg_name'];} ?>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ($user['u_delivery_type'] == 'home_delivery'): ?>
			<tr>
				<th><label for="u_address_full">Delivery Address</label></th>
				<td>
					<?= multiline_address($user); ?>
				</td>
			</tr>
		<?php endif; ?>


		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Update Details" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>
