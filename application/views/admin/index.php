<h2>Admin Area</h2>
<p>
	This is the admin area
</p>

<p>
	<ul>
		View picking lists for
		<li>Last Tuesday: <a href="<?php echo site_url('admin/picking-lists/'.date('Y-m-d', strtotime('last Tuesday'))); ?>"><?php echo date('jS F Y', strtotime('last Tuesday')); ?></a></li>
		<?php
			if (date('l') == 'Tuesday')
			{
				echo '<li>This Tuesday: <a href="'. site_url('admin/picking-lists/'.date('Y-m-d')) .'">'. date('jS F Y') .'</a></li>';
			}
		?>
		<li>Next Tuesday: <a href="<?php echo site_url('admin/picking-lists/'.date('Y-m-d', strtotime('next Tuesday'))); ?>"><?php echo date('jS F Y', strtotime('next Tuesday')); ?></a></li>
	</ul>
</p>

<p>
	View <a href="<?php echo site_url('admin/reports/orders'); ?>">Reports</a>
</p>
