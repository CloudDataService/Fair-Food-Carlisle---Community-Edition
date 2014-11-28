<h2>Manage Members and Staff</h2>

<?php echo form_open('admin/users', array('method' => 'get', 'id' => 'user_search')); ?>
	<table class="search">

		<tr>
			<td>
				<label for="u_type">Type</label>
				<select name="u_type">
					<option value="">-- All types --</option>
					<?php
					foreach($user_fields['types'] as $type)
					{
						echo '<option value="' . $type . '" ' . ($type == @$_GET['u_type'] ? 'selected="selected"' : '') . '>' . $type . '</option>';
					}
					?>
				</select>
			</td>

			<td>
				<label for="u_bg_id">Buying Group</label>
				<select name="u_bg_id">
					<option value="">-- All groups --</option>
					<?php foreach($user_groups as $bg) {
						echo '<option value="' . $bg['bg_id'] . '" ' . ($bg['bg_id'] == @$_GET['u_bg_id'] ? 'selected="selected"' : '') . '>' . $bg['bg_name'] . '</option>';
					} ?>
				</select>
			</td>
			<td>
				<label for="u_pg_id">Permissions</label>
				<select name="u_pg_id">
					<option value="">-- All groups --</option>
					<option value="0">-- No permissions --</option>
					<?php foreach($user_fields['permission_groups'] as $pg) {
						echo '<option value="' . $pg['pg_id'] . '" ' . ($pg['pg_id'] == @$_GET['u_pg_id'] ? 'selected="selected"' : '') . '>' . $pg['pg_name'] . '</option>';
					} ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<label for="u_name">Name</label>
				<input type="text" name="u_name" placeholder="Enter users name" value="<?php echo (isset($_GET['u_name']) ? $_GET['u_name'] : "");?>" />
				<select name="u_s_type">
					<?php $u_s_type = array('Exact' => 'exact', 'Like' => 'like'); ?>
					<?php foreach($u_s_type as $key => $value) { ?>
						<option value="<?php echo $value;?>" <?php echo (isset($_GET['u_s_type']) && $_GET['u_s_type'] == $value ? "selected = selected" : "");?> ><?php echo $key;?></option>
					<?php } ?>
				</select>
			</td>
			<td>&nbsp;</td>
			<td class="btn"><input type="submit" class="btn" value="Search" />
			<a href="admin/users" class="btn">Clear</a></td>
		</tr>
	</table>
	<?php echo form_close(); ?>

	<p>
		<a href="<?php echo site_url('admin/users/set') ?>" class="btn btn-small modal" rel="610" style="float:right;">Add a user</a>
		<div class="clear"></div>
	</p>

<?php
if ($users)
{
	echo '<table class="results">';
	echo '<tr class="order">
			<th><a href="'. current_url() .'?order=u_id'. $sort .'"'. ((@$_GET['order'] == 'u_id') ? ' class="' . $_GET['sort'] . '"' : '') .'>ID</a></th>
			<th><a href="'. current_url() .'?order=u_sname'. $sort .'"'. ((@$_GET['order'] == 'u_sname') ? ' class="' . $_GET['sort'] . '"' : '') .'>Name</a></th>
			<th><a href="'. current_url() .'?order=u_email'. $sort .'"'. ((@$_GET['order'] == 'u_email') ? ' class="' . $_GET['sort'] . '"' : '') .'>E-mail</a></th>
			<th>Go Cardless</th>
			<th><a href="'. current_url() .'?order=u_type'. $sort .'"'. ((@$_GET['order'] == 'u_type') ? ' class="' . $_GET['sort'] . '"' : '') .'>Type</a></th>
			<th>Actions</th>
		</tr>
		';

	// loop over each topic wall
	$count = 0;
	foreach($users as $u)
	{
		echo '<tr class="vat">
				<td>'. $u['u_id'] .'</td>
				<td>'. $u['u_title'] .' '. $u['u_fname']. ' '. $u['u_sname'] .'</td>
				<td>'. $u['u_email'] .'</td>
				<td>'. $u['u_gc_setup'] .'</td>
				<td>'. $u['u_type'] .'</td>
				<td><a href="'.site_url().'admin/users/orders/'. $u['u_id'] .'" class="btn">Orders</a>
					<a href="'.site_url().'admin/users/set/'. $u['u_id'] .'" class="btn">Edit</a>
					<a href="'.site_url().'admin/users/set/'. $u['u_id'] .'/delete" class="btn">Delete</a>
				</td>
			</tr>';
		$count++;
	}
	echo '</table>';

	echo '<div class="item">';
		if (isset($count) && isset($_GET['pp']) && $count == $_GET['pp']) {
			echo $this->pagination->create_links();
		}
	echo '</div>';

}
else
{
	echo '<div class="item green">
			<p class="no_results">There are no users found.</p>
		</div>';
}
?>
