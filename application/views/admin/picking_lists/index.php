<h2>Picking Lists</h2>

<?php echo (isset($error_message) ? '<div class="error-msg">'.$error_message.'</div>' : ""); ?>
<?php echo form_open(current_url(), array('id' => 'picking_list')); ?>

	<table class="form">
		<tr>
			<th><label for="picking_date">Please select a delivery date to display that picking list</label></th>
			<td>
				<input type="text" class="datepicker" name="picking_date" id="picking_date" maxlength="10" style="width:70px;" />
				
			</td>
		</tr>
		<tr>
			<td align=""><input type="submit" class="btn" value="View" /></td>
		</tr>
	</table>
<?php echo form_close(); ?>
