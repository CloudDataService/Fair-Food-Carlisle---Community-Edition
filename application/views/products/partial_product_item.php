<?php

	//thumbnail images were made at 50x50, but if its more recent it's 140x140. Work out which one to use...
	if (element('p_image', $p) == null)
	{
		$preview_img = 'not-found';
	}
	else if ( file_exists(FCPATH . 'img/uploads/products/small/'.$p['p_image']) &&
	   is_file(FCPATH . 'img/uploads/products/small/'.$p['p_image']) &&
	   filesize( FCPATH . 'img/uploads/products/small/'.$p['p_image'] ) > 10240)
	{
		$preview_img = 'img/uploads/products/small/'.$p['p_image'];
	}
	else
	{
		$preview_img = 'img/uploads/products/'.$p['p_image'];
	}

	echo '<div class="item_preview" data-pid="' . $p['p_id'] . '" >'.
		'<div class="image">'. img_tag($preview_img). '</div>'.
		'<h3><a href="'. site_url('products/'.$category['cat_slug']) .'/'.$p['p_slug'] .'">'. $p['p_name'] .'</a></h3>'.
		'<p class="supplier">'. @$p['s_name'] .'</p>'.
		'<p class="price">1'. @$p['pu_short_single'] .', &pound;'. number_format($p['p_price'], 2) .'</p>'.
		'<p class="p_action"><a href="'. site_url('products/'.$category['cat_slug']) .'/'.$p['p_slug'] .'" class="btn">Buy</a></p>';
	echo '</div>';
	//echo '<li><a href="'. site_url() .'products/'. $cat['cat_slug'] .'">'.$cat['cat_name'].'</a></li>';

?>
