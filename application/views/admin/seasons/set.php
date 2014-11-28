<h2>Produce Season</h2>

<?php
	if ($action_confirm == 'delete') {
?>
	<div class="confirm-prompt">
		<?php echo form_open(current_url(), array('id' => 'delete_season_form')); ?>
			Are you sure you want to delete the season displayed below?
			<br />
			<input type="hidden" name="action-confirm" value="delete" />
			<input type="submit" class="btn" value="Delete" style="float:right;" />
			<div class="clear"></div>
		<?php echo form_close(); ?>
	</div>
<?php
	}
?>

<?php echo form_open_multipart(current_url(), array('id' => 'add_season_form')); ?>

	<table class="form">
		<tr>
			<th><label for="pc_name">Season name</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="pc_name" id="pc_name" maxlength="200" style="width:250px;" <?php if (@$season['pc_name']) {echo 'value="'.$season['pc_name'].'"';} ?> />
				<br /><small>The name is a private reference when editing products.</small>
			</td>
		</tr>
		<tr>
			<th><label for="pc_period_start">Season start</label> <span class="orange">*</span></th>
			<td>
				<?php
				if (isset($season['pc_period_end'])) {
					$pc_start = date("d/m/Y",strtotime($season['pc_period_start']));
				} ?>
				<input type="text" class="datepicker" name="pc_period_start" id="pc_period_start" maxlength="10" style="width:70px;" <?php if (isset($pc_start)) {echo 'value="'.$pc_start.'"';} ?> />
				<br /><small>The earliest delivery possible will be on this date.</small>
			</td>
		</tr>
		<tr>
			<th><label for="pc_period_end">Season end</label> <span class="orange">*</span></th>
			<td>
				<?php
				if (isset($season['pc_period_end'])) {
					$pc_end = date("d/m/Y",strtotime($season['pc_period_end']));
				} ?>
				<input type="text" class="datepicker" name="pc_period_end" id="pc_period_end" maxlength="10" style="width:70px;" <?php if (isset($pc_end)) {echo 'value="'.$pc_end.'"';} ?> />
				<br /><small>The latest delivery possible will be on this date.</small>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="pc_preseason_gap">Days before season <br />that orders can be placed</label></th>
			<td>
				<input type="text" class="" name="pc_preseason_gap" id="pc_preseason_gap" maxlength="3" style="width:20px;" <?php if (isset($season['pc_preseason_gap'])) {echo 'value="'.$season['pc_preseason_gap'].'"';} else {echo 'value="0"';} ?> />
				<br /><small>Members can order on any day, so long as <br />there are this number of days before the season starts.</small>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="pc_predelivery_gap">Days before delivery <br />that orders must be placed</label></th>
			<td>
				<input type="text" class="" name="pc_predelivery_gap" id="pc_predelivery_gap" maxlength="3" style="width:20px;" <?php if (isset($season['pc_predelivery_gap'])) {echo 'value="'.$season['pc_predelivery_gap'].'"';} else {echo 'value="0"';} ?> />
				<span id="pc_predelivery_hint"></span>
				<br /><small>Members can order before or during the season, <br />but it must be at least this number of days before the delivery date they request.
					<br />4 = anytime on Friday orders can be made for the following Tuesday</small>
			</td>
		</tr>

		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Update Season" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>
