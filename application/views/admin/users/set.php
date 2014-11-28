<h2><?php echo $title; ?></h2>

<?php
	if ($action_confirm == 'delete') {
?>
	<div class="confirm-prompt">
		<?php echo form_open(current_url(), array('id' => 'delete_user_form')); ?>
			Are you sure you want to delete the member or admin below?
			<br />
			<input type="hidden" name="action-confirm" value="delete" />
			<input type="submit" class="btn" value="Delete" style="float:right;" />
			<div class="clear"></div>
		<?php echo form_close(); ?>
	</div>
<?php
	}
?>

<?php echo form_open(current_url(), array('id' => 'add_user_form')); ?>

	<table class="form">

	<?php if (!@$user) { ?>
		<script type="text/javascript">var new_user = true;</script>
		<tr>
			<th><label for="u_email">Email address</label> <span class="orange">*</span></th>
			<td><input type="text" name="u_email" id="u_email" <?php if (@$user['u_email']) {echo 'value="'.$user['u_email'].'"';} ?> /></td>
		</tr>

		<tr>
			<th><label for="u_email_confirm">Confirm email</label> <span class="orange">*</span></th>
			<td><input type="text" name="u_email_confirm" id="u_email_confirm" style="width:250px;" /></td>
		</tr>

		<tr>
			<th><label for="u_pword">Password</label> <span class="orange">*</span></th>
			<td>
				<input type="password" name="u_pword" id="u_pword" style="width:250px;" autocomplete="off" />
				<small style="display:block; width:250px;">Must start with a capital letter, be at least 8 characters long and contain a number.</small>
			</td>
		</tr>

		<tr>
			<th><label for="u_password_confirm">Confirm password</label> <span class="orange">*</span></th>
			<td><input type="password" name="u_password_confirm" id="u_password_confirm" style="width:250px;" autocomplete="off" /></td>
		</tr>
	<?php } else {
		echo '<script type="text/javascript">var new_user = false;</script>';
	} ?>

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
			<th><label for="u_fname">First name</label></th>
			<td><input type="text" name="u_fname" id="u_fname" style="width:250px; text-transform:capitalize;" <?php if (@$user['u_email']) {echo 'value="'.$user['u_fname'] .'"';} ?> /></td>
		</tr>

		<tr>
			<th><label for="u_sname">Last name</label></th>
			<td><input type="text" name="u_sname" id="u_sname" style="width:250px; text-transform:capitalize;" <?php if (@$user['u_email']) {echo 'value="'.$user['u_sname'] .'"';} ?> /></td>
		</tr>

		<?php if (config_item('delivery_options') != null): ?>
		<tr class="vat" style="border-top: 1px solid #ccc;">
			<th>Delivery Type</th>
			<td>
				<select name="u_delivery_type" id="u_delivery_type" class="other" <?php echo ($loggedin_user_can['manage_member_permissions'] == FALSE ? 'disabled="disabled"' : '') ?>>
					<?php
					foreach(config_item('delivery_options') as $doption => $dlabel)
					{
						echo '<option value="' . $doption . '" ' . ($doption == element('u_delivery_type', $user) ? 'selected="selected"' : '') . '>' . $dlabel . '</option>';
					}
					?>
				</select>
				<br><small>Note: this affects wether neither, address or buying group are applicable. <br>Only the applicable fields will be shown to the member or on their picking lists.</small>
			</td>
		</tr>
		<?php endif; ?>

		<?php if (config_item('delivery_options') != null): ?>
			<tr>
				<th><label for="u_addr_line1">Address line 1</label></th>
				<td><input type="text" name="u_addr_line1" id="u_addr_line1" style="width:250px;" <?php if (isset($user) && element('u_addr_line1', $user) != null) {echo 'value="'.$user['u_addr_line1'] .'"';} ?> /></td>
			</tr>
			<tr>
				<th><label for="u_addr_line2">Address line 2</label></th>
				<td><input type="text" name="u_addr_line2" id="u_addr_line2" style="width:250px;" <?php if (isset($user) && element('u_addr_line2', $user) != null) {echo 'value="'.$user['u_addr_line2'] .'"';} ?> /></td>
			</tr>
			<tr>
				<th><label for="u_addr_city">City</label></th>
				<td><input type="text" name="u_addr_city" id="u_addr_city" style="width:250px;" <?php if (isset($user) && element('u_addr_city', $user) != null) {echo 'value="'.$user['u_addr_city'].'"';} else {echo 'value="'. $this->config->item('default_bg_town') .'"';} ?> /></td>
			</tr>
			<tr>
				<th><label for="u_addr_pcode">Post Code</label></th>
				<td><input type="text" name="u_addr_pcode" id="u_addr_pcode" maxlength="9" style="width:75px;" <?php if (isset($user) && element('u_addr_pcode', $user) != null) {echo 'value="'.$user['u_addr_pcode'] .'"';} ?> /></td>
			</tr>
		<?php endif;?>

		<tr class="vat" style="border-bottom: 1px solid #ccc;">
			<th>Buying Group</th>
			<td>
				<select name="u_bg_id" id="u_bg_id" class="other" <?php echo ($loggedin_user_can['manage_member_permissions'] == FALSE ? 'disabled="disabled"' : '') ?>>
					<option value="">-- Please select --</option>
					<?php
					foreach($user_fields['groups'] as $group)
					{
						echo '<option value="' . $group['bg_id'] . '" ' . ($group['bg_id'] == element('u_bg_id', $user) ? 'selected="selected"' : '') . '>' . $group['bg_name'] . '</option>';
					}
					?>
				</select>
				<br><small>Warning: changing this will result in all past/current/future orders &amp; bills to appear as for delivery to the new buying group. <br />It will also affect the reports generated.</small>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="u_type">Type</label></th>
			<td>
				<select name="u_type" id="u_type" class="other" <?php echo ($loggedin_user_can['manage_member_permissions'] == FALSE ? 'disabled="disabled"' : '') ?>>
					<option value="">-- Please select --</option>
					<?php
					foreach($this->config->config['user']['types'] as $type)
					{
						echo '<option value="' . $type . '" ' . ($type == @$user['u_type'] ? 'selected="selected"' : '') . '>' . $type . '</option>';
					}
					?>
				</select>
				<br /><small>The permissions granted below will only take affect if the type is Admin.
			</td>
		</tr>

		<tr class="vat">
			<th><label for="u_pg_id">Permissions Group</label></th>
			<td class="has_info_helper">
				<div id="permission_check_box" class="form_info_helper hidden"></div>
				<select name="u_pg_id" id="u_pg_id" <?php echo ($loggedin_user_can['manage_member_permissions'] == FALSE ? 'disabled="disabled"' : '') ?>>
					<option value="0">-- No permissions --</option>
					<?php
					foreach($user_fields['permission_groups'] as $pg)
					{
						echo '<option value="' . $pg['pg_id'] . '" ' . ($pg['pg_id'] == @$user['u_pg_id'] ? 'selected="selected"' : '') . '>' . $pg['pg_name'] . '</option>';
					}
					?>
				</select>
				<img src="/img/icons/info.png" id="permission_check_btn" class="info_helper_btn">
				<br /><small>Gives users access to certain parts of the admin area.
						<?php if ($loggedin_user_can['manage_member_permissions'] == FALSE) {
							echo '<br />You can not change assigned permissions.';
							}
							else
							{
								echo '<br />Permissions will update when they next login.';
							}
						 ?>
							</small>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="u_s_id">Producer/Supplier</label></th>
			<td>
				<select name="u_s_id" id="u_s_id">
					<option value="">-- Not a producer --</option>
					<?php
					foreach($user_fields['suppliers'] as $s)
					{
						echo '<option value="' . $s['s_id'] . '" ' . ($s['s_id'] == @$user['u_s_id'] ? 'selected="selected"' : '') . '>' . $s['s_name'] . '</option>';
					}
					?>
				</select>
				<br /><small>Used for permissions that are specific to the user's producer.
							<br />E.g. if a permisison group states they can only edit produce that they supply.</small>
			</td>
		</tr>

		<tr style="border-top: 1px solid #ccc;">
			<th><label for="u_sname">Go Cardless<br />Authorised?</label></th>
			<td><?php if (@$user['u_gc_setup']) {echo $user['u_gc_setup'];} ?></td>
		</tr>


		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Update User" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>
