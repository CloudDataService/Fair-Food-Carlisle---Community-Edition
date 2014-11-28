
<?php echo form_open(current_url(), array('method' => 'post', 'id' => 'user_search')); ?>
	<table class="search">
		<tr>
			<td>
				<label for="delivery_date">Produce delivered between </label>
			</td>
			<td>
				<input type="text" class="datepicker" name="delivery_from" id="delivery_from" maxlength="10" style="width:70px;" value="<?php echo (isset($filter['delivery_from']) ? $filter['delivery_from'] : ''); ?>" />
				and
				<input type="text" class="datepicker" name="delivery_to" id="delivery_to" maxlength="10" style="width:70px;" value="<?php echo (isset($filter['delivery_to']) ? $filter['delivery_to'] : ''); ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="p_cat_id">Produce in category</label>
			</td>
			<td>
				<select name="p_cat_id" id="p_cat_id">
					<option value="">Any</option>
				<?php
					foreach($categories as $meow)
					{
						echo '<option value="'. $meow['cat_id']  .'" ';
						if (isset($filter['p_cat_id']) && $meow['cat_id'] == $filter['p_cat_id']) {echo 'selected="selected" ';}
						echo '>'. $meow['cat_name'] .'</option>';
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="delivery_date">Produce from supplier</label></td>
			<td>
				<select name="p_s_id" id="p_s_id">
					<option value="">Any</option>
				<?php
					foreach($suppliers as $sup)
					{
						echo '<option value="'. $sup['s_id']  .'" ';
						if ($sup['s_id'] == $filter['p_s_id']) {echo 'selected="selected" ';}
						echo '>'. $sup['s_name'] .'</option>';
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="1"></td>
			<td class="btn"><a href="admin/reports/produce" class="btn">Clear</a>
				<input type="submit" class="btn" value="Search" /></td>
		</tr>
	</table>
<?php echo form_close(); ?>


<?php
	echo '<h2 class="report-title">Top Selling Produce</h2>';
	if (count($reports['top_sellers']) > 0)
	{
		//show the chart
		$chart = new Chart('ColumnChart');
		$chart->load($reports['top_sellers'], 'array');
		$options = array('is3D' => true, 'width' => 800, 'height' => 400,
						'hAxis' => array('title' => 'Produce Name'),
						'chartArea' => array('top' => 5, 'height' => '80%')
						);
		echo $chart->draw('top_sellers', $options);
		echo '<div id="top_sellers" class="report-chart"></div>';

		//show the data
		echo '<table class="report-data">';
		echo '<tr>';
			echo '<th>Rank</th>';
		foreach(current($reports['top_sellers']) as $k => $v)
		{
			echo '<th>'. $k .'</th>';
		}
		echo '</tr>';
		$i = 1;
		foreach($reports['top_sellers'] as $row_date => $row)
		{
			echo '<tr>';
			echo '<td>'. $i .'</td>';
			foreach($row as $k => $v)
			{
				echo '<td>'. $v .'</td>';
			}
			echo '</tr>';
			$i++;
		}
		echo '</table>';
	}
	else
	{
		echo '<div class="report">No delivery orders found.</div>';
	}
?>


<?php
	echo '<h2 class="report-title">Bottom Selling Produce</h2>';
	if (count($reports['bottom_sellers']) > 0)
	{
		//show the chart
		$chart = new Chart('ColumnChart');
		$chart->load($reports['bottom_sellers'], 'array');
		$options = array('is3D' => true, 'width' => 800, 'height' => 400,
						'hAxis' => array('title' => 'Produce Name'),
						'chartArea' => array('top' => 5, 'height' => '80%')
						);
		echo $chart->draw('bottom_sellers', $options);
		echo '<div id="bottom_sellers" class="report-chart"></div>';

		//show the data
		echo '<table class="report-data">';
		echo '<tr>';
			echo '<th>Rank</th>';
		foreach(current($reports['bottom_sellers']) as $k => $v)
		{
			echo '<th>'. $k .'</th>';
		}
		echo '</tr>';
		$i = 1;
		foreach($reports['bottom_sellers'] as $row_date => $row)
		{
			echo '<tr>';
			echo '<td>'. $i .'</td>';
			foreach($row as $k => $v)
			{
				echo '<td>'. $v .'</td>';
			}
			echo '</tr>';
			$i++;
		}
		echo '</table>';
	}
	else
	{
		echo '<div class="report">No delivery orders found.</div>';
	}
?>

