<h2>Categories</h2>


	<p>
		<a href="<?php echo site_url('admin/categories/set') ?>" class="btn btn-small modal" rel="610" style="float:right;">Add a category</a>
		<div class="clear"></div>
	</p>


<?php
if ($categories)
{
	echo '<table class="results">';
	echo '<tr class="order">
			<th><a href="'. current_url() .'?order=cat_id'. $sort .'"'. ((@$_GET['order'] == 'cat_id') ? ' class="' . $_GET['sort'] . '"' : '') .'>ID</a></th>
			<th><a href="'. current_url() .'?order=cat_name'. $sort .'"'. ((@$_GET['order'] == 'cat_name') ? ' class="' . $_GET['sort'] . '"' : '') .'>Name</a></th>
			<th><a href="'. current_url() .'?order=cat_page_order'. $sort .'"'. ((@$_GET['order'] == 'cat_page_order') ? ' class="' . $_GET['sort'] . '"' : '') .'>Order</a></th>
			<th><a href="'. current_url() .'?order=cat_parent_id'. $sort .'"'. ((@$_GET['order'] == 'cat_parent_id') ? ' class="' . $_GET['sort'] . '"' : '') .'>Parent Category</a></th>
			<th><a href="'. current_url() .'?order=cat_slug'. $sort .'"'. ((@$_GET['order'] == 'cat_slug') ? ' class="' . $_GET['sort'] . '"' : '') .'>URL Name</a></th>
			<th style="padding-left:18px;">Actions</th>
		';

	// loop over each meow
	foreach($categories as $cat)
	{
		echo '<tr class="vat">
				<td>'. $cat['cat_id'] .'</td>
				<td>'. $cat['cat_name'] .'</td>
				<td>'. $cat['cat_page_order'] .'</td>
				<td>'. $cat['cat_parent_name'] .'</td>
				<td>'. $cat['cat_slug'] .'</td>
				<td>
					<a href="'.site_url().'products/'. $cat['cat_slug'] .'" class="btn">View</a>
					<a href="'.site_url().'admin/categories/set/'. $cat['cat_id'] .'" class="btn">Edit</a>
					<a href="'.site_url().'admin/categories/set/'. $cat['cat_id'] .'/delete" class="btn">Delete</a>
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
			<p class="no_results">There are no categories found.</p>
		</div>';
}
?>
