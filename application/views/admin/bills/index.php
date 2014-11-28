<h2>Customer Bills</h2>
<p>Most recent bills are displayed first. Click a column heading to change the sort order, or use the options below to search.</p>

<?php echo form_open('admin/bills', array('method' => 'get', 'id' => 'bill_search')); ?>
	<table class="search">
		<tr>
			<td>
				<label for="b_status">Status</label>
				<select name="b_status">
					<option value="">-- All statuses --</option>
					<?php
					foreach($bill_fields['statuses'] as $status)
					{
						echo '<option value="' . $status . '" ' . ($status == @$_GET['b_status'] ? 'selected="selected"' : '') . '>' . $status . '</option>';
					}
					?>
				</select>
			</td>
			<td style="padding-left:20px;">
				<label for="b_id">Bill ID</label>
				<input name="b_id" value="<?php echo @$_GET['b_id']; ?>" size="4" />
			</td>
			<td>
				&nbsp;
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr>
			<td>
				<label for="bg_id">Group</label>
				<select name="bg_id">
					<option value="">-- All groups --</option>
					<?php
					foreach($buying_groups as $bg)
					{
						echo '<option value="' . $bg['bg_id'] . '" ' . (isset($params['bg_id']) && $bg['bg_id'] == $params['bg_id'] ? 'selected="selected"' : '') . '>' . $bg['bg_name'] . '</option>';
					}
					?>
				</select>
			</td>
			<td>
				&nbsp;
			</td>
			<td class="btn" style="padding-left:20px;"><input type="submit" class="btn" value="Search" /></td>
			<td class="btn"><a href="admin/bills" class="btn">Clear</a></td>
		</tr>
	</table>
	<?php echo form_close(); ?>



<?php
if ($bills)
{
	echo '<table class="results">';
	echo '<tr class="order">
			<th><a href="'. current_url() .'?order=b_id'. $sort .'"'. ((@$_GET['order'] == 'b_id') ? ' class="' . $_GET['sort'] . '"' : '') .'>ID</a></th>
			<th><a href="'. current_url() .'?order=customer_name'. $sort .'"'. ((@$_GET['order'] == 'customer_name') ? ' class="' . $_GET['sort'] . '"' : '') .'>Customer</a></th>
			<th><a href="'. current_url() .'?order=bg_name'. $sort .'"'. ((@$_GET['order'] == 'bg_name') ? ' class="' . $_GET['sort'] . '"' : '') .'>Group</a></th>
			<th><a href="'. current_url() .'?order=b_items'. $sort .'"'. ((@$_GET['order'] == 'b_items') ? ' class="' . $_GET['sort'] . '"' : '') .'>No. of Items</a></th>
			<th><a href="'. current_url() .'?order=b_price'. $sort .'"'. ((@$_GET['order'] == 'b_price') ? ' class="' . $_GET['sort'] . '"' : '') .'>Total Amount</a></th>
			<th><a href="'. current_url() .'?order=b_status'. $sort .'"'. ((@$_GET['order'] == 'b_status') ? ' class="' . $_GET['sort'] . '"' : '') .'>Status</a></th>
			<th>Actions</th>
		';

	// loop over each topic wall
	foreach($bills as $b)
	{
		echo '<tr class="vat">
				<td>'. $b['b_id'] .'</td>
				<td>'. $b['customer_name'] .'</td>
				<td>'. $b['bg_name'] .'</td>
				<td>'. $b['b_items'] .'</td>
				<td>&pound;'. number_format($b['b_price'], 2) .'</td>
				<td>'. $b['b_status'] .'</td>
				<td>
					<a href="'.site_url().'admin/bills/view/'. $b['b_id'] .'" class="btn" style="float:right;">View</a>
					<!-- <a href="'.site_url().'admin/bills/set/'. $b['b_id'] .'" class="btn" style="float:right;">Edit</a> -->
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
			<p class="no_results">There are no bills found.</p>
		</div>';
}
?>
