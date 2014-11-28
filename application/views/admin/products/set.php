<h2>Product</h2>

<?php
	if ($action_confirm == 'delete') {
?>
	<div class="confirm-prompt">
		<?php echo form_open(current_url(), array('id' => 'delete_p_form')); ?>
			Are you sure you want to delete the product displayed below?
			<br />
			The product will not be displayed on the website, but existing orders for it will remain.
			<br />
			<input type="hidden" name="action-confirm" value="delete" />
			<input type="submit" class="btn" value="Delete" style="float:right;" />
			<div class="clear"></div>
		<?php echo form_close(); ?>
	</div>
<?php
	}
?>

<?php echo form_open_multipart(current_url(), array('id' => 'add_product_form')); ?>

	<table class="form">
		<tr class="vat">
			<th><label for="p_name">Produce name</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="p_name" id="p_name" class="required" maxlength="200" style="width:250px;" <?php if (@$product['p_name']) {echo 'value="'.$product['p_name'].'"';} ?> />
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_slug">Short (URL) Name</label></th>
			<td>
				<input type="text" name="p_slug" id="p_slug" class="required" maxlength="30" <?php if (@$product['p_slug']) {echo 'value="'.$product['p_slug'].'"';} ?> />
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_description">Description</label></th>
			<td>
				<textarea name="p_description" id="p_description" maxlength="750" style="width:250px; height:80px"><?php if (@$product['p_description']) {echo $product['p_description'];} ?></textarea>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_status">Status</label></th>
			<td>
				<select name="p_status" id="p_status">
					<?php
					$statuses = array('Draft', 'Active', 'Removed');
					foreach($statuses as $status)
					{
						echo '<option value="' . $status . '" ' . ($status == @$product['p_status'] ? 'selected="selected"' : '') . '>' . $status . '</option>';
					}
					?>
				</select>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_page_order">Page order</label> <span class="orange">*</span></th>
			<td>
				<input type="text" name="p_page_order" id="p_page_order" width="5" maxlength="3" style="max-width:30px;" <?php if (@$product['p_page_order']) {echo 'value="'.$product['p_page_order'].'"';} ?> />
				<br /><small>10 is default. Produce with lower numbers will be listed first. Where items have the same number they are listed alphabetically.</small>
			</td>
		</tr>

		<?php
			if (@$product['p_image'])
			{
				echo '<tr><th>&nbsp;</th>
					<td><img src="'. site_url('img/uploads/products/small/'.$product['p_image']) .'" alt="product_img" class="thumb"></td>
					</tr>';
			}
		?>
		<tr class="vat">
			<th><label for="p_image">Image</label></th>
			<td><input type="file" name="p_image" id="p_image" /></td>
		</tr>

		<tr class="vat">
			<th><label for="p_cat_id">Categories</label></th>
			<td>
				<select name="p_cat_add" id="p_cat_add" class="other">
					<option value="">-- Please Select --</option>
					<?php
					foreach($categories as $cat)
					{
						echo '<option value="' . $cat['cat_id'] . '" >' . $cat['cat_name'] . '</option>';
					}
					?>
				</select>
				<a href="" id="p_cat_add" class="btn">Add</a>
				<ul id="p_cat_list">
					<?php
					if (@$product['categories'])
					{
						foreach($product['categories'] as $cat)
						{
							echo '<li style="margin-bottom:0;"><input type="hidden" name="p_cats[]" value="' . $cat['cat_id'] . '" >' . $cat['cat_name'] . '
							<span class="p_cat_remove"><strong>X</strong></span></li>';
						}
					}
					?>
				</ul>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_s_id">Producer</label></th>
			<td>
				<select name="p_s_id" id="p_s_id">
					<option value="">-- Please Select --</option>
					<?php
					foreach($suppliers as $supplier)
					{
						echo '<option value="' . $supplier['s_id'] . '" ' . ($supplier['s_id'] == @$product['p_s_id'] ? 'selected="selected"' : '') . '>' . $supplier['s_name'] . '</option>';
					}
					?>
				</select>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_pu_id">Quantity Unit</label></th>
			<td>
				<select name="p_pu_id" id="p_pu_id">
					<option value="">-- Please Select --</option>
					<?php
					foreach($units as $u)
					{
						echo '<option value="' . $u['pu_id'] . '" ' . ($u['pu_id'] == @$product['p_pu_id'] ? 'selected="selected"' : '') . '>' . $u['pu_plural'] . ' ('. $u['pu_short_plural'] .')</option>';
					}
					?>
				</select>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_price">Price per unit<br />to customer</label> <span class="orange">*</span></th>
			<td>
				&pound;<input type="text" name="p_price" id="cat_slug" class="required" maxlength="5" style="width:35px;" <?php if (@$product['p_price']) {echo 'value="'.$product['p_price'].'"';} ?> />
				<?php
					if (isset($product))
					{
						echo '<br /><small>If changing the price or cost, orders that have not yet been billed will be updated with the new price.</small>';
					}
				?>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_cost">Cost per unit<br />to FFC</label></th>
			<td>
				&pound;<input type="text" name="p_cost" id="p_cost" maxlength="5" style="width:35px;" <?php if (@$product['p_cost']) {echo 'value="'.$product['p_cost'].'"';} ?> />
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_stock_warning">Warn members<br />when stock below</label></th>
			<td>
				<input type="text" name="p_stock_warning" id="p_stock_warning" maxlength="5" style="width:35px;" <?php if (isset($product['p_stock_warning'])) {echo 'value="'.$product['p_stock_warning'].'"';} ?> />
				<br /><small>Set to 0 to never show a warning.</small>
			</td>
		</tr>

		<tr class="vat">
			<th><label for="p_pc">Availability Seasons</label></th>
			<td>
				<ul id="p_pc_list" multiadd_last="<?php if (!@$product['commitments']) {echo '0';} else { echo (count($product['commitments']) -1);} ?>">
					<?php
					if (@$product['commitments'])
					{
						$i =0;
						foreach($product['commitments'] as $pc)
						{
							if (isset($pc['pc_name']) && $pc['pc_name'] != '')
							{
								//season is named
								echo '<li style="margin-bottom:0;">
									<input type="hidden" multiadd_ref="'. $i .'" name="p_pc_id['. $i .']" value="'. $pc['pc_id'] .'">
									<input type="hidden" multiadd_ref="'. $i .'" name="p_pc_max_qty['. $i .']" value="'. $pc['p2pc_stock'] .'">

									'. $pc['pc_name'] .', stock left '. $pc['p2pc_stock'] .'.
									'. /* '<span><img src="/img/icons/info.png" title="Delivery between '. date('jS M y', strtotime($pc['pc_period_start'])) .' and '. date('jS M y', strtotime($pc['pc_period_end'])) .'. Order by '. date('jS M y', strtotime($pc['pc_last_order_date'])) .' or '. $pc['pc_predelivery_gap'] .' days before delivery."></span>' */ '
									<span><img src="/img/icons/info.png" title="'. $pc['pc_description'] .'"></span>
									<span class="multiadd_remove"><img src="/img/icons/cross.png" title="Remove"></span>
								</li>';
								$i++;
							}
							else
							{
								//old style
								echo '<li style="margin-bottom:0;">
									<input type="hidden" multiadd_ref="'. $i .'" name="p_pc_min_qty['. $i .']" value="'. $pc['pc_min_qty'] .'">
									<input type="hidden" multiadd_ref="'. $i .'" name="p_pc_max_qty['. $i .']" value="'. $pc['pc_max_qty'] .'">
									<input type="hidden" multiadd_ref="'. $i .'" name="p_pc_period_start['. $i .']" value="'. $pc['pc_period_start'] .'">
									<input type="hidden" multiadd_ref="'. $i .'" name="p_pc_period_end['. $i .']" value="'. $pc['pc_period_end'] .'">
									<input type="hidden" multiadd_ref="'. $i .'" name="p_pc_preseason_gap['. $i .']" value="'. $pc['pc_preseason_gap'] .'">
									<input type="hidden" multiadd_ref="'. $i .'" name="p_pc_predelivery_gap['. $i .']" value="'. $pc['pc_predelivery_gap'] .'">
									Stock available '. $pc['pc_max_qty'] .', Min order '. $pc['pc_min_qty'] . $product['pu_short_plural'] .'. At least '. $pc['pc_preseason_gap'] .' days before season, or '. $pc['pc_predelivery_gap'] .' days before delivery during season. Season is '. mysql_timestamp_to_english_date($pc['pc_period_start']) .' to '. mysql_timestamp_to_english_date($pc['pc_period_end']) .'.

									<span class="multiadd_remove"><img src="/img/icons/cross.png" title="Remove"></span>
								</li>';
								$i++;
							}
						}
					}
					?>
				</ul>
			</td>
		</tr>


		<tr class="vat">
			<th><label for="p_pc">Add A Season</label></th>
			<td>
				<fieldset id="p_pc" class="multiadd" style="padding: 0 5px;">
					<label for="p_pc_id">Season: </label>
					<select name="p_pc_new_id" ref="p_pc_id" id="p_pc_new_id" multiadd_label="">
						<option value="">-- Please Select --</option>
						<?php
						foreach($named_seasons as $s)
						{
							echo '<option value="' . $s['pc_id'] . '" >' . $s['pc_name'] . '</option>';
						}
						?>
					</select>

					<label for="p_pc_new_max_qty" style="margin-left:20px;">Units available this season</label>
					<input type="text" name="p_pc_new_max_qty" ref="p_pc_max_qty" id="p_pc_new_max_qty" maxlength="4" style="width:30px;" multiadd_label=", stock available " />

					<a href="" class="btn multiadd_btn" style="margin-left:20px;">Add Season <img src="/img/icons/add.png" title="Add"></a>
					<div style="clear:both;"></div>
				</fieldset>
				<small>Add seasons in which the product will be available for delivery. Make sure you click 'Add Season' before clicking 'Update Product'.</small>

				<?php /* old way of working
				<fieldset id="p_pc" class="multiadd" style="border: 1px solid #333; padding: 0 5px;">
					<label for="p_pc_new_max_qty">Total available to FFC</label>
					<input type="text" name="p_pc_new_max_qty" ref="p_pc_max_qty" id="p_pc_new_max_qty" maxlength="4" style="width:30px;" multiadd_label="Stock available " />

					<label for="p_pc_new_min_qty">Customers must order at least </label>
					<input type="text" name="p_pc_new_min_qty" ref="p_pc_min_qty" id="p_pc_new_min_qty" maxlength="4" style="width:30px;" multiadd_label=", Min order " />

					<br />

					<label for="p_pc_new_preseason_gap">Days before season that orders can be placed</label>
					<input type="text" name="p_pc_new_preseason_gap" ref="p_pc_preseason_gap" id="p_pc_new_preseason_gap" maxlength="4" style="width:30px;" multiadd_label=". At least " />

					<label for="p_pc_new_predelivery_gap">Days before delivery that orders can be placed</label>
					<input type="text" name="p_pc_new_predelivery_gap" ref="p_pc_predelivery_gap" id="p_pc_new_predelivery_gap" maxlength="4" style="width:30px;" multiadd_label=" days before season, or " />

					<br />

					<label for="p_pc_new_period_start">Available from</label>
					<input type="text" class="datepicker" name="p_pc_new_period_start" ref="p_pc_period_start" id="p_pc_new_period_start" maxlength="10" style="width:70px;" multiadd_label=" days before delivery during season. Season is " />

					<label for="p_pc_new_period_end">till</label>
					<input type="text" class="datepicker" name="p_pc_new_period_end" ref="p_pc_period_end" id="p_pc_new_period_end" maxlength="10" style="width:70px;" multiadd_label=" to " />

					<!--
					<label for="p_pc_new_freq">Every</label>
					<select name="p_pc_new_freq" id="p_pc_new_freq" ref="p_pc_freq" multiadd_label=" every " class="other">
						<option value="">-- Please Select --</option>
						<?php
						foreach($frequencies as $v => $l)
						{
							echo '<option value="' . $v . '" >' . $l . '</option>';
						}
						?>
					</select>

					<label for="p_pc_new_period">For</label>
					<select name="p_pc_new_period" id="p_pc_new_period" ref="p_pc_period" multiadd_label=" for " class="other">
						<option value="">-- Please Select --</option>
						<?php
						foreach($periods as $v => $l)
						{
							echo '<option value="' . $v . '" >' . $l . '</option>';
						}
						?>
					</select>
					-->

					<a href="" class="btn multiadd_btn" style="float:right;">Add Season <img src="/img/icons/add.png" title="Add"></a>
					<div style="clear:both;"></div>
				</fieldset>
				*/
				?>
			</td>
		</tr>

		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="btn" value="Update Product" /></td>
		</tr>

	</table>

<?php echo form_close(); ?>
