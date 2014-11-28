<h2>Category</h2>

<?php
	if ($action_confirm == 'delete') {
?>
	<div class="confirm-prompt">
		<?php echo form_open(current_url(), array('id' => 'delete_cat_form')); ?>
			Are you sure you want to delete the category displayed below?
			<br />
			<input type="hidden" name="action-confirm" value="delete" />
			<input type="submit" class="btn" value="Delete" style="float:right;" />
			<div class="clear"></div>
		<?php echo form_close(); ?>
	</div>
<?php
	}
?>

<?php echo form_open_multipart(current_url(), array('id' => 'add_category_form')); ?>

	<table class="form">
		<tr>
			<th><label for="cat_name">Category name</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="cat_name" id="cat_name" maxlength="200" style="width:250px;" <?php if (isset($category['cat_name'])) {echo 'value="'.$category['cat_name'].'"';} ?> />
			</td>
		</tr>

		<tr>
			<th><label for="cat_parent_id">Parent</label></th>
			<td>
				<select name="cat_parent_id" id="cat_parent_id" class="other">
					<option value="">-- Top level --</option>
					<?php
					foreach($categories as $parent)
					{
						echo '<option value="' . $parent['cat_id'] . '" ' . ($parent['cat_id'] == isset($category['cat_parent_id']) ? 'selected="selected"' : '') . '>' . $parent['cat_name'] . '</option>';
					}
					?>
				</select>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="cat_page_order">Page order</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="cat_page_order" id="cat_page_order" width="5" maxlength="3" style="max-width:30px;" <?php if (isset($category['cat_page_order'])) {echo 'value="'.$category['cat_page_order'].'"';} ?> />
				<br /><small>10 is default. Categories with lower numbers will be listed first. Where items have the same number they are listed alphabetically.</small>
			</td>
		</tr>


		<?php
			if (@$category['cat_image'])
			{
				echo '<tr><th>&nbsp;</th>
					<td><img src="'. site_url('img/uploads/categories/small/'.$category['cat_image']) .'" alt="category_img" class="thumb"></td>
					</tr>';
			}
		?>
		<tr>
			<th><label for="cat_image">Image</label></th>
			<td><input type="file" name="cat_image" id="cat_image" /></td>
		</tr>

		<tr>
			<th><label for="cat_slug">Short Name</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="cat_slug" id="cat_slug" maxlength="30" <?php if (@$category['cat_slug']) {echo 'value="'.$category['cat_slug'].'"';} ?> />
			</td>
		</tr>

		<tr>
			<th><label for="cat_description">Description</label></th>
			<td>
				<textarea name="cat_description" id="cat_description" maxlength="750" style="width:250px; height:80px"><?php if (@$category['cat_description']) {echo $category['cat_description'];} ?></textarea>
			</td>
		</tr>

		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Update Category" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>
