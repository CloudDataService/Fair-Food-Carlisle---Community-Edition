<script type="text/javascript">
	<?php $today = date('Y') .', '. date('m')-1 .', '. date('d') .', '. date('H') .', '. date('i'); ?>
	window.today = new Date(<?= $today; ?>);
</script>
<h2 class="product-title"><?php echo $product['p_name']; ?></h2>
<p class="product-supplier"><?php echo $product['supplier_name']; ?></p>
<script type="text/javascript">var product_id = <?php echo $product['p_id']; ?>;</script>

<div id="product-page" class="ten columns">

	<?php if ( $product['p_stock_warning'] != 0 && $stock['lowest'] < $product['p_stock_warning'] ): ?>
	<div class="product-stock-warning nine columns" style="margin-bottom: 10px;">
		<h3>Low Stock</h3>
		<?php if ($stock['seasons'] == 1): ?>
			<p>Only <strong><?= $stock['lowest']; ?></strong> items are available for the current season.</p>
		<?php else: ?>
			<p>Only <strong><?= $stock['lowest']; ?></strong> items are available for some dates.</p>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<div id="product-info" class="">
		<div id="product-image" class="">
			<?php
				echo img_tag('img/uploads/products/'.$product['p_image'], array("alt" => "product_img")).
				'<p class="product-caption">'. $product['p_name'] .'</a></h3>'.
				'<p class="supplier">'. $product['supplier_name'] .'</p>'.
				'<p class="price">&pound;'. number_format($product['p_price'], 2) .' per '. $product['pu_single'] .'</p>';
			?>
		</div>
		<div id="product-description" class="">
			<h3>Product description</h3>
			<?php echo nl2br(@$product['p_description']); ?>
		</div>

	</div>

	<div style="clear:both;"></div>
	<div id="supplier-info" class="">

		<div id="supplier-image" class="">
			<p class="supplier-title"><?php echo $product['supplier_name']; ?></p>
			<?php echo img_tag('img/uploads/suppliers/'.$product['supplier_image'], array("alt" => "supplier_img")); ?>
		</div>
		<div id="supplier-description" class="">
			<h3>Producer description</h3>
			<?php echo nl2br($product['supplier_description']); ?>
		</div>
	</div>
</div>


<?php if (($this->auth->is_logged_in()) && ($this->session->userdata('bg_status') != 'Active')) { ?>

<div id="product-ordering" class="six columns">
	<p>
		At this time, orders cannot be made for the buying group you are part of.
	</p><p>
		For more detail, please contact your <a href="<?php echo site_url('members/group'); ?>">advocate</a> or <a href="<?php echo site_url('contact/new_group'); ?>"><?php echo $this->config->item('site_name'); ?></a>.
	</p>
</div>
<?php } elseif (!$allowed_days['days']) { ?>
<div id="product-ordering" class="six columns">
	<p>
		At this time, the item is not available.
	</p><p>
		You may contact <a href="<?php echo site_url('contact/order'); ?>"><?php echo $this->config->item('site_name'); ?></a> to express your interest or find out more about the seasons this item is available.
	</p>
</div>
<?php } elseif ($this->auth->is_logged_in()) { ?>

<div id="product-ordering" class="six columns">
	<h3>Place an order</h3>
	<p>Dates available for delivery are highlighted below, input an amount, then click the date you would like that delivered. Repeat until you are happy and then click 'place order'.

	<?php
		echo form_open(current_url(), array('id' => 'orderitem_form'));
	?>
		<p>
			<label for="oi_quantity"><span class="stepcount">1</span> Enter quantity: </label>
				<input type="text" name="oi_quantity" id="oi_quantity" maxlength="5" style="width:30px;" <?php if (@$orderitem['oi_quantity']) {echo 'value="'.$orderitem['oi_quantity'].'"';} else {echo 'value="1"';} ?> />
				<span id="oi_unit"><?php echo @$product['pu_short_plural']; ?></span>
		</p>

		<p>
			<label for="oi_quantity"><span class="stepcount">2</span> Select delivery frequency:</label>
			<select name="oi_frequency" id="oi_frequency">
				<option value="selected" selected="selected">Selected dates</option>
				<option value="weekly">Weekly</option>
				<option value="fortnight">Fortnightly (1)</option>
				<option value="everyother">Fortnightly (2)</option>
				<option value="monthly">Monthly</option>
			</select>
			<input type="hidden" name="or_startmilli" id="or_startmilli" />
		</p>

		<p class="singledateselection">
			<label for="seasonCal" class="singledateselection"><span class="stepcount">3</span> Select delivery dates:</label><br />
			<div id="orderingcal" class="calNineK singledateselection" style="width:340px;">
				<?php echo $this->load->view('products/product-ordering-calendar', $allowed_days); ?>
			</div>

			<div class="seasonkey singledateselection">
				<span class="keyicon dayOrdering">&nbsp;</span><span class="keytext">Selected</span>
				<span class="keyicon calSelectable">&nbsp;</span><span class="keytext">Available</span>
				<span class="keyicon calUnavailable">&nbsp;</span><span class="keytext">Unavailable</span>
			</div>
		</p>

		<p style="clear: both;">
			<span id="commitment_notes"></span>
		</p>

		<div id="craig">
			<label style="margin-bottom:0;">Currently selected dates and quantities:</label>
			<ul id="orderdates"></ul>
		</div>


	<?php
	if (!$this->auth->is_logged_in())
	{
		echo '<p><label for="orderitem_btn">You need to <a href="'.site_url() .'home/login">Login</a> to be able to order items from the site.</label></p>';
	}
	else
	{
		echo '<p>
				<label for="orderitem_btn"><span class="stepcount">4</span> Once you\'re happy, click here:</label>
				<input type="submit" id="orderitem_btn" class="btn" value="Place order" onclick="submitFormOkay = true;" />
			</p>';
	}
		echo form_close();
	?>
</div>
<?php
}
else
{
 ?>
<div id="product-ordering" class="six columns">
	<p>
		Buying products is restricted to members of 'buying groups'.
	</p><p>
		If you are interested in setting up a new buying group please contact <a href="<?php echo site_url('contact/new_group'); ?>">Fair Food Carlisle</a>, or <a href="<?php echo site_url('home/login'); ?>">log in</a>.
	</p>
</div>
<?php } ?>

<div style="clear:both;"></div>

<div class="dialog" style="display:none;"></div>
