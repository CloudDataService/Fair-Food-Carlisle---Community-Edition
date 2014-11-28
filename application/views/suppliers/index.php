<h2 class="supplier-title"><?php echo $supplier['s_name']; ?></h2>

<div id="product-page" class="ten columns">

	<div id="supplier-info" class="">
		
		<div id="supplier-image" class="">
			<?php echo img_tag('img/uploads/suppliers/'.$supplier['s_image'], array("alt" => "supplier_img")); ?>
		</div>
		<div id="supplier-description" class="">
			<?php echo $supplier['s_description']; ?>
		</div>
	</div>
	
	
	<div style="clear:both;"></div>
	<div id="product-info" class="">

	</div>
</div>

