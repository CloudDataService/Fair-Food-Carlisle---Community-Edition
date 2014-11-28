<h2>Seasons</h2>
<p>Seasons are set up to specify when each produce can be ordered. Seasons are set up here, when editing a produce item the season is selected by name.</p>

	<p>
		<a href="<?php echo site_url('admin/seasons/set') ?>" class="btn btn-small modal" rel="610" style="float:right;">Add a season</a>
		<div class="clear"></div>
	</p>


<?php
if ($seasons)
{
	echo '<table class="results">';
	echo '<tr class="order">
			<th><a href="'. current_url() .'?order=pc_id'. $sort .'"'. ((@$_GET['order'] == 'pc_id') ? ' class="' . $_GET['sort'] . '"' : '') .'>ID</a></th>
			<th><a href="'. current_url() .'?order=pc_name'. $sort .'"'. ((@$_GET['order'] == 'pc_name') ? ' class="' . $_GET['sort'] . '"' : '') .'>Name</a></th>
			<th><a href="'. current_url() .'?order=pc_period_start'. $sort .'"'. ((@$_GET['order'] == 'pc_period_start') ? ' class="' . $_GET['sort'] . '"' : '') .'>Start</a></th>
			<th><a href="'. current_url() .'?order=pc_period_end'. $sort .'"'. ((@$_GET['order'] == 'pc_period_end') ? ' class="' . $_GET['sort'] . '"' : '') .'>End</a></th>
			<th style="padding-left:18px;">Actions</th>
		';

	// loop over each meow
	foreach($seasons as $s)
	{
		echo '<tr class="vat">
				<td>'. $s['pc_id'] .'</td>
				<td>'. $s['pc_name'] .'</td>
				<td>'. $s['pc_period_start_format'] .'</td>
				<td>'. $s['pc_period_end_format'] .'</td>
				<td>
					<a href="'.site_url().'admin/seasons/set/'. $s['pc_id'] .'" class="btn">Edit</a>
					<a href="'.site_url().'admin/seasons/set/'. $s['pc_id'] .'/delete" class="btn">Delete</a>
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
			<p class="no_results">There are no seasons found.</p>
		</div>';
}
?>
