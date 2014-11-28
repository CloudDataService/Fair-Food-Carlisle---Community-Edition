<h2>Producer</h2>

<?php echo form_open_multipart(current_url(), array('id' => 'add_supplier_form')); ?>

	<table class="form">
		<tr class="vat">
			<th><label for="s_name">Supplier name</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="s_name" id="s_name" class="required" maxlength="200" style="width:250px;" <?php if (@$supplier['s_name']) {echo 'value="'.$supplier['s_name'].'"';} ?> />
			</td>
		</tr>

		<?php
			if (@$supplier['s_image'])
			{
				echo '<tr><th>&nbsp;</th>
					<td><img src="'. site_url('img/uploads/suppliers/small/'.$supplier['s_image']) .'" alt="supplier_img" class="thumb"></td>
					</tr>';
			}
		?>
		<tr class="vat">
			<th><label for="s_image">Image</label></th>
			<td><input type="file" name="s_image" id="s_image" /></td>
		</tr>

		<tr class="vat">
			<th><label for="s_description">Description</label></th>
			<td>
				<textarea name="s_description" id="s_description" maxlength="750" style="width:250px; height:80px"><?php if (@$supplier['s_description']) {echo $supplier['s_description'];} ?></textarea>
				<br /><small>Any URLs will be converted into links.
			</td>
		</tr>

		<tr class="vat">
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Update Supplier" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>
