<h2>Buying Groups</h2>

<?php echo form_open('admin/groups', array('method' => 'get', 'id' => 'group_search')); ?>
	<table class="search">

		<tr>
			<td>
				<label for="bg_status">Status</label>
				<select name="bg_status">
					<option value="">-- All statuses --</option>
					<?php
					foreach($this->config->config['group_fields']['statuses'] as $status)
					{
						echo '<option value="' . $status . '" ' . ($status == @$_GET['bg_status'] ? 'selected="selected"' : '') . '>' . $status . '</option>';
					}
					?>
				</select>
			</td>

			<td class="btn"><input type="submit" class="btn" value="Search" /></td>
			<td class="btn"><a href="admin/groups" class="btn">Clear</a></td>
		</tr>
	</table>
	<?php echo form_close(); ?>

	<p>
		<a href="<?php echo site_url('admin/groups/create') ?>" class="btn btn-small modal" rel="610" style="float:right;">Create new buying group</a>
		<div class="clear"></div>
	</p>


<?php
if ($groups)
{
	echo '<table class="results">';
	echo '<tr class="order">
			<th><a href="'. current_url() .'?order=bg_id'. $sort .'"'. ((@$_GET['order'] == 'bg_id') ? ' class="' . $_GET['sort'] . '"' : '') .'>ID</a></th>
			<th><a href="'. current_url() .'?order=bg_code'. $sort .'"'. ((@$_GET['order'] == 'bg_code') ? ' class="' . $_GET['sort'] . '"' : '') .'>Join Code</a></th>
			<th><a href="'. current_url() .'?order=bg_name'. $sort .'"'. ((@$_GET['order'] == 'bg_name') ? ' class="' . $_GET['sort'] . '"' : '') .'>Group Name</a></th>
			<th><a href="'. current_url() .'?order=bg_status'. $sort .'"'. ((@$_GET['order'] == 'bg_status') ? ' class="' . $_GET['sort'] . '"' : '') .'>Status</a></th>
			<th><a href="'. current_url() .'?order=bg_deliveryday'. $sort .'"'. ((@$_GET['order'] == 'bg_deliveryday') ? ' class="' . $_GET['sort'] . '"' : '') .'>Delivery Day</a></th>
			<th>Actions</th>
		';

	// loop over each topic wall
	foreach($groups as $bg)
	{
		echo '<tr class="vat">
				<td>'. $bg['bg_id'] .'</td>
				<td>'. $bg['bg_code'] .'</td>
				<td>'. $bg['bg_name'] .'</td>
				<td>'. $bg['bg_status'] .'</td>
				<td>'. $bg['bg_deliveryday'] .'</td>
				<td>
					<a href="'.site_url().'admin/groups/view/'. $bg['bg_id'] .'" class="btn">View</a>
					<a href="'.site_url().'admin/groups/set/'. $bg['bg_id'] .'" class="btn">Edit</a>
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
			<p class="no_results">There are no groups found.</p>
		</div>';
}
?>
