	<?php $today = new DateTime(); ?>
	<div class="calNineK-config" style="display:none;">
		<span class="availableWeekFirst" data-value="<?= $available_one->format('Y-m-d'); ?>" data-label="<?= $available_one->format('jS \of M Y'); ?>"></span>
		<span class="availableWeekSecond" data-value="<?= $available_two->format('Y-m-d'); ?>" data-label="<?= $available_two->format('jS \of M Y'); ?>"></span>
	</div>
	<div class="calNineK-section calNineK-section-Year">
		<span class="calNineK-section-label">Year</span>
		<?php foreach($allowed_days['years'] as $year): ?>
			<span class="calNineK-category-element <?= ($year->format('Y') == $today->format('Y') ? 'today selected' : '')?>" data-date="<?= $year->format('Y'); ?>" millis="1389139200000"><?= $year->format('Y'); ?></span>
		<?php endforeach; ?>
	</div>
	<div class="calNineK-section calNineK-section-Month">
		<span class="calNineK-section-label">Month</span>
		<?php foreach($allowed_days['months'] as $month): ?>
			<span class="calNineK-category-element <?= ($month->format('Y-m') == $today->format('Y-m') ? 'today selected' : '')?> hidden" 
				data-date="<?= $month->format('Y-m'); ?>" 
				data-year="<?= $month->format('Y'); ?>"
				style="width: 44px;"><?= $month->format('M'); ?></span>
		<?php endforeach; ?>
	</div>
	<div class="calNineK-section section-Day">
		<span class="calNineK-section-label">Day</span>
		<?php foreach($allowed_days['days'] as $day): ?>
			<span class="calNineK-day-element <?= ($day['date']->format('Y-m-d') == $today->format('Y-m-d') ? 'today' : '')?> hidden <?= ($day['available'] == TRUE ? 'calSelectable' : '')?>"
				data-date="<?= $day['date']->format('Y-m-d'); ?>"
				data-month="<?= $day['date']->format('Y-m'); ?>"
				data-year="<?= $day['date']->format('Y'); ?>"
				>
				<?= $day['date']->format('D'); ?><br><span class="calNineK-dayNumber"><?= $day['date']->format('j'); ?></span></span>
		<?php endforeach; ?>
		<span class="calNineK-no-js">The calendar cannot be displayed. Javascript must be enabled.</span>
	</div>