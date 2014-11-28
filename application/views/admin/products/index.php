<h2>Products</h2>

<?php echo form_open('admin/products', array('method' => 'get', 'id' => 'product_search')); ?>
	<table class="search">
		<tr>
			<td>
				<label for="p_status">Status</label>
				<select name="p_status">
					<option value="">-- All statuses --</option>
					<?php
					foreach($product_fields['statuses'] as $status)
					{
						echo '<option value="' . $status . '" ' . ($status == @$_GET['p_status'] ? 'selected="selected"' : '') . '>' . $status . '</option>';
					}
					?>
				</select>
			</td>
			<td style="padding-left:30px;">
				<label for="image_state">Image Uploaded</label>
				<select name="image_state">
					<option value="">-- All products --</option>
					<?php
					foreach($product_fields['image_exists'] as $image_state)
					{
						echo '<option value="' . $image_state . '" ' . ($image_state == @$_GET['image_state'] ? 'selected="selected"' : '') . '>' . $image_state . '</option>';
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="p_cat_id">Category</label>
				<select name="p_cat_id">
					<option value="">-- All Categories --</option>
					<option value="none" <?php echo ('none' == @$_GET['p_cat_id'] ? 'selected="selected"' : '') ?>>-- No Category --</option>
					<?php
					foreach($product_cats as $meow)
					{
						echo '<option value="' . $meow['cat_id'] . '" ' . ($meow['cat_id'] == @$_GET['p_cat_id'] ? 'selected="selected"' : '') . '>' . $meow['cat_name'] . '</option>';
					}
					?>
				</select>
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr>
			<td colspan="4" style="text-align:right;">
				<input type="submit" class="btn" value="Search" />
				<a href="admin/products" class="btn">Clear</a>
			</td>
		</tr>
	</table>
<?php echo form_close(); ?>

	<p>
		<a href="<?php echo site_url('admin/products/set') ?>" class="btn btn-small modal" rel="610" style="float:right;">Add a product</a>
		<div class="clear"></div>
	</p>




<?php
if ($products)
{
	echo '<table class="results">';
	echo '<tr class="order">
			<th><a href="'. current_url() .'?order=p_id'. $sort .'"'. ((@$_GET['order'] == 'p_id') ? ' class="' . $_GET['sort'] . '"' : '') .'>ID</a></th>
			<th><a href="'. current_url() .'?order=p_name'. $sort .'"'. ((@$_GET['order'] == 'cat_name') ? ' class="' . $_GET['sort'] . '"' : '') .'>Name</a></th>
			<th style="max-width:20px;"><a href="'. current_url() .'?order=p_page_order'. $sort .'"'. ((@$_GET['order'] == 'p_page_order') ? ' class="' . $_GET['sort'] . '"' : '') .'>Order</a></th>
			<th><a href="'. current_url() .'?order=p_slug'. $sort .'"'. ((@$_GET['order'] == 'p_slug') ? ' class="' . $_GET['sort'] . '"' : '') .'>URL Name</a></th>
			<th><a href="'. current_url() .'?order=p_status'. $sort .'"'. ((@$_GET['order'] == 'p_status') ? ' class="' . $_GET['sort'] . '"' : '') .'>Status</a></th>
			<th>View</th>
			<th>Edit</th>
			<th>Delete</th>
		';

	// loop over each meow
	foreach($products as $p)
	{
		echo '<tr class="vat">
				<td>'. $p['p_id'] .'</td>
				<td>'. $p['p_name'] .'</td>
				<td>'. $p['p_page_order'] .'</td>
				<td>'. $p['p_slug'] .'</td>
				<td class="'. $p['p_status'] .'">'. $p['p_status'] .'</td>
				<td>
					<a href="'.site_url().'products/all/'. $p['p_slug'] .'"><img src="'.site_url('img/icons/view.png').'" alt="View"></a>
				</td>
				<td>
					<a href="'.site_url().'admin/products/set/'. $p['p_id'] .'"><img src="'.site_url('img/icons/edit.png').'" alt="Edit"</a>
				</td>
				<td>
					<a href="'.site_url().'admin/products/set/'. $p['p_id'] .'/delete"><img src="'.site_url('img/icons/cross.png').'" alt="Delete"</a>
				</td>
			</tr>';
	}
	echo '</table>';

	echo '<div class="item">';
		echo $this->pagination->create_links();
	echo '</div>';

}
else
{
	echo '<div class="item green">
			<p class="no_results">There were no products found.</p>
		</div>';
}
?>
