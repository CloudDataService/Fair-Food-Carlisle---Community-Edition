<h2>Producers</h2>


	<p>
		<a href="<?php echo site_url('admin/suppliers/set') ?>" class="btn btn-small modal" rel="610" style="float:right;">Add a supplier</a>
		<div class="clear"></div>
	</p>


<?php
if ($suppliers)
{
	echo '<table class="results">';
	echo '<tr class="order">
			<th><a href="'. current_url() .'?order=s_id'. $sort .'"'. ((@$_GET['order'] == 's_id') ? ' class="' . $_GET['sort'] . '"' : '') .'>ID</a></th>
			<th><a href="'. current_url() .'?order=s_name'. $sort .'"'. ((@$_GET['order'] == 's_name') ? ' class="' . $_GET['sort'] . '"' : '') .'>Name</a></th>
			<th>Actions</th>
		';

	// loop over each meow
	foreach($suppliers as $s)
	{
		echo '<tr class="vat">
				<td>'. $s['s_id'] .'</td>
				<td>'. $s['s_name'] .'</td>
				<td>
					<a href="'.site_url().'supplier/'. $s['s_id'] .'" class="btn">View</a>
					<a href="'.site_url().'admin/suppliers/set/'. $s['s_id'] .'" class="btn">Edit</a>
					<a href="'.site_url().'admin/suppliers/orders/'. $s['s_id'] .'" class="btn">Orders</a>
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
			<p class="no_results">There are no suppliers found.</p>
		</div>';
}
?>
