<h2>Customer Bills</h2>


<?php
if ($bills)
{
	echo '<table class="results">';
	echo '<tr class="order">
			<th><a href="'. current_url() .'?order=b_id'. $sort .'"'. ((@$_GET['order'] == 'b_id') ? ' class="' . $_GET['sort'] . '"' : '') .'>ID</a></th>
			<th><a href="'. current_url() .'?order=customer_name'. $sort .'"'. ((@$_GET['order'] == 'customer_name') ? ' class="' . $_GET['sort'] . '"' : '') .'>Customer</a></th>
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
