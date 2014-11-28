<h2>Browse Produce</h2>
<p>To take a look at these produce 'click' on the products below.</p>

<?php
	if (@$categories)
	{
		echo '<div class="item_list categories">';
		foreach($categories as $cat)
		{
			if ($cat['cat_show_products'] > 0 && is_array($cat['products']))
			{
				foreach($cat['products'] as $prod)
				{
					echo '<div class="item_preview">'.
						'<div class="feature"><div class="feature-inner"><span>'. $cat['cat_name'] . '</span></div></div>'.
						'<div class="clear"></div>'.
						'<div class="image postfeature">'. img_tag('img/uploads/products/'.$prod['p_image']). '</div>'.
						'<h3><a href="'. site_url('products/featured/'.$prod['p_slug']) .'">'. $prod['p_name'] .'</a></h3>'.
						'<p class="description">'. @$prod['p_description'] .'</p>';
					echo '</div>';
				}
			}
			else
			{
				echo '<div class="item_preview">'.
					'<div class="image">'. img_tag('img/uploads/categories/'.$cat['cat_image']). '</div>'.
					'<h3><a href="'. site_url('products/'.$cat['cat_slug']) .'">'. $cat['cat_name'] .'</a></h3>'.
					'<p>'. @$cat['cat_description'] .'</p>';
				echo '</div>';
				//echo '<li><a href="'. site_url() .'products/'. $cat['cat_slug'] .'">'.$cat['cat_name'].'</a></li>';
			}
		}
		echo '</div>';
	}
	else
	{

	}

?>
