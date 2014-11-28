<h2>Welcome to the <?php echo $this->config->item('site_name'); ?></h2>

<?php if (!$this->auth->is_logged_in()): ?>
	<p>First please <a href="<?= site_url() ?>home/login"><strong>login</strong></a> if you have an account, or <a href="<?= site_url() ?>home/register">register</a>, so that you can view and make orders.</p>
	<p>You can also <a href="contact/new-group">get in touch with us</a>, to arrange local food deliveries to your work place or community centre.</p>

<?php endif; ?>

	<p><a href="products">Browse categories</a> or type the name of produce in the box below.</p>

	<div class="ac-search" style="font-size: 1.2em; width:350px; margin-bottom: 5px;">
		<?php echo form_open('', array('id' => 'ac-search-form')); ?>
			Search for:
			<input id="search" type="text">
			<img class="loadicon" src="img/style/ajax-loader-bgwhite.gif" style="visibility:hidden;">
		<?php echo form_close(); ?>
	</div>


	<div class="item_list products">

	</div>



