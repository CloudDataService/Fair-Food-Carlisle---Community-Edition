<h2><?php echo $category['cat_name']; ?></h2>

<?php
	if ($category['cat_description'] != '' )
	{
		echo '<p>'. $category['cat_description'] .'</p>';
	}
	else
	{
		echo '<p>This is the selection of products in the '. $category['cat_name'] .' category.</p>';
	}
?>


<?php
	$product_view = 'products/partial_product_item';
	if (@$products)
	{
		echo '<div class="item_list products">';
		foreach($products as $p)
		{
			$p_data = array(
				'p' => $p,
				'category' => $category
				);
			echo $this->load->view($product_view, $p_data, TRUE);
		}
		echo '</div>';
	}
	else
	{
		'There are no products in this category at the moment.';
	}

?>

<?php
	if (!$this->auth->is_logged_in()) {
		echo '<div style="clear:both;"></div>';
		echo '<p>Buying products is restricted to members of "buying groups".
	If you are interested in setting up a new buying group please contact <a href="'. site_url('contact/new_group') .'">Fair Food Carlisle</a>, or <a href="'. site_url('home/login') .'">log in</a>.</p>';
	}
 ?>
