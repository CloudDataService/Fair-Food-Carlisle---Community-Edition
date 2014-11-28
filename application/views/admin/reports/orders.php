
<?php echo form_open(current_url(), array('method' => 'post', 'id' => 'user_search')); ?>
	<table class="search">
		<tr>
			<th><label for="delivery_date">Orders for delivery between </label></th>
			<td>
				<input type="text" class="datepicker" name="delivery_from" id="delivery_from" maxlength="10" style="width:70px;" value="<?php echo (isset($filter['delivery_from']) ? $filter['delivery_from'] : ''); ?>" />
				and
				<input type="text" class="datepicker" name="delivery_to" id="delivery_to" maxlength="10" style="width:70px;" value="<?php echo (isset($filter['delivery_to']) ? $filter['delivery_to'] : ''); ?>" />
				<td colspan="2"></td>
			</td>
		</tr>
		<tr>
			<th><label for="bg_id">Group</label></th>
			<td>
				<select name="bg_id">
					<option value="">-- All groups --</option>
					<?php
					foreach($buying_groups as $bg)
					{
						echo '<option value="' . $bg['bg_id'] . '" ' . (isset($filter['bg_id']) && $bg['bg_id'] == $filter['bg_id'] ? 'selected="selected"' : '') . '>' . $bg['bg_name'] . '</option>';
					}
					?>
				</select>
			</td>
		</tr>
		<?php
			if (isset($filter['s_id']))
			{
				echo '<tr><td colspan="3">Showing reports for orders of produce you supply.</td></tr>';
			}
		?>
		<tr>
			<td colspan="2"></td>
			<td class="btn"><input type="submit" class="btn" value="Search" /></td>
			<td class="btn"><a href="admin/reports/orders" class="btn">Clear</a></td>
		</tr>
	</table>
<?php echo form_close(); ?>


<?php
	echo '<h2 class="report-title">Overview of Deliveries</h2>';
	if (count($reports['overview']) > 0)
	{
		//show the chart
		$chart = new Chart('ColumnChart');
		$chart->load($reports['overview'], 'array');
		$options = array('is3D' => true, 'width' => 800, 'height' => 400,
						'hAxis' => array('title' => 'Date of Delivery'),
						'chartArea' => array('top' => 5, 'height' => '80%')
						);
		echo $chart->draw('report-overview', $options);
		echo '<div id="report-overview" class="report-chart"></div>';

		//show the data
		echo '<table class="report-data">';
		echo '<tr>';
		foreach(current($reports['overview']) as $k => $v)
		{
			echo '<th>'. $k .'</th>';
		}
		echo '</tr>';
		foreach($reports['overview'] as $row_date => $row)
		{
			echo '<tr>';
			foreach($row as $k => $v)
			{
				echo '<td>'. $v .'</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}
	else
	{
		echo '<div class="report">No delivery orders found.</div>';
	}
?>


<?php
	echo '<h2 class="report-title">When Orders Were Made</h2>';
	if (count($reports['orders']) > 0)
	{
		//show the chart
		$chart = new Chart('LineChart');
		$chart->load($reports['orders'], 'array');
		$options = array('is3D' => true, 'width' => 800, 'height' => 400,
						'hAxis' => array('title' => 'Date Order Was Made'),
						'chartArea' => array('top' => 5, 'height' => '80%')
						);
		echo $chart->draw('report-orders', $options);
		echo '<div id="report-orders" class="report-chart"></div>';

		//show the data
		echo '<table class="report-data">';
		echo '<tr>';
		foreach(current($reports['orders']) as $k => $v)
		{
			echo '<th>'. $k .'</th>';
		}
		echo '</tr>';
		foreach($reports['orders'] as $row_date => $row)
		{
			echo '<tr>';
			foreach($row as $k => $v)
			{
				echo '<td>'. $v .'</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	}
	else
	{
		echo '<div class="report">No orders made during this time period.</div>';
	}
?>


<?php
	echo '<h2 class="report-title">Value of Orders</h2>';
	if (count($reports['value']) > 0)
	{
		//show the chart
		$chart = new Chart('ColumnChart');
		$chart->load($reports['value'], 'array');
		$options = array('is3D' => true, 'width' => 800, 'height' => 400,
						'hAxis' => array('title' => 'Date of Delivery'),
						'chartArea' => array('top' => 5, 'height' => '80%')
						);
		echo $chart->draw('report-value', $options);
		echo '<div id="report-value" class="report-chart"></div>';

		//show the data
		echo '<table class="report-data">';
		echo '<tr>';
		foreach(current($reports['value']) as $k => $v)
		{
			echo '<th>'. $k .'</th>';
		}
		echo '</tr>';
		foreach($reports['value'] as $row_date => $row)
		{
			echo '<tr>';
			foreach($row as $k => $v)
			{
				if (is_float($v))
				{
					echo '<td>&pound;'. number_format($v, 2) .'</td>';
				}
				else
				{
					echo '<td>'. $v .'</td>';
				}
			}
			echo '</tr>';
		}
		echo '</table>';
	}
	else
	{
		echo '<div class="report">No delivery orders found.</div>';
	}
?>
