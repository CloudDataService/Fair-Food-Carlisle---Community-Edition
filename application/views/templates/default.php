<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]-->
<head>
<base href="<?php echo base_url() ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta charset="utf-8">
<?php echo $this->layout->get_meta(); ?>
<?php echo $this->layout->get_css(); ?>
<?php echo $this->layout->get_js(); ?>
<title><?php echo $this->layout->get_title(); ?></title>
<link rel="shortcut icon" href="img/favicon.ico">
	<script type="text/javascript">var base_url = "<?php echo base_url() ?>";</script>
<!--[if lt IE 9]>
<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

</head>

<body class="<?php echo $this->uri->segment(1) ?> <?php echo $this->uri->segment(2) ?>">

<div class="navbar">
	<div class="container" style="width:1020px;">
		<?php
		if ($this->uri->segment(1) == 'admin')
		{
			echo '<div id="admin-label">ADMIN</div>';
		}
		?>
		<div class="nav-container">
			<?php echo $this->layout->get_nav(); ?>
		</div>
	</div>
</div>
<div style="clear:both;"></div>

<div class="container">
	<div class="header sixteen columns clearfix">
		<div class="logo three columns clearfix">
			<a href="<?php echo site_url() ?>"><img src="img/logo.png" alt="<?php echo $this->config->item('site_name'); ?> Logo" width="161" height="181"></a>
		</div>
		<div class="slogan_container twelve columns clearfix">
			<span class="slogan"><?php echo $this->config->item('site_slogan'); ?></span>
		</div>
	</div>

	<div class="container clearfix">
		<div class="sixteen columns flash"><?php echo $this->flash->get() ?></div>
	</div>

	<div class="content sixteen columns clearfix">
		<div class="breadcrumb">
			<?php echo $this->layout->get_breadcrumbs(); ?>
		</div>
		<?php echo $view ?>
	</div>

    <?php /*if ($this->uri->uri_string() == '') : ?>
    	<div class="footer sixteen columns clearfix"
    		style="background-image:url('<?php echo site_url('img/style/cds-logo.png'); ?>'); background-repeat:no-repeat; background-position:10px 100%;">
    		<img src="<?php echo site_url('img/style/sponsors.png'); ?>" alt="Supporting organisations" style="vertical-align:middle;" />
    		<p style="text-align:center"><a href="http://ec.europa.eu/agriculture/rurdev/index_en.htm">http://ec.europa.eu/agriculture/rurdev/index_en.htm</a>
    			<br />This project is part financed by the European Agricultural Fund for Rural Development: Europe investing in rural areas
    			<br />Defra is the managing authority for this project</p>
    		</p>
    	</div>
    <?php endif;*/ ?>

	<div class="developedby sixteen columns">
		The Food Hub was developed by <a href="http://clouddataservice.co.uk/" target="_blank">Cloud Data Service Ltd</a>
		<br />The software is licensed under the <a href="http://opensource.org/licenses/OSL-3.0" target="_blank">Open Software License 3.0</a>
		<br />Please contact <a href="mailto:hello@clouddataservice.co.uk">hello@clouddataservice.co.uk</a> for more information
	</div>

</div>


	<!-- Delete dialogs -->
	<div id="delete_dialog" class="hidden">
		<h1>Delete <span class="name"></span></h1>
		<p class="text"></p>
		<?php echo form_open('', array('id' => 'delete_form')) ?>
			<div class="hidden" id="hidden_inputs"></div>
			<div style="margin: 30px 15px 15px 0; text-align: right; bottom: 0; position: absolute; right: 0;">
				<button type="submit" class="btn icon delete"><span>Delete</span></button>
				<a href="#" class="btn close-dialog">Cancel</a>
			</div>
		</form>
	</div>


</body>
</html>
