<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="tab-content">
	<div class="tab-pane fade in active" id="dashboard-tab">
<?php $count = 1; ?>
<?php foreach($units as $unit): ?>
			<div class="row padding-20">
				<div class="col-sm-10">
					<h2>Unit <?php echo $count; ?></h2>
					<div id="json-unit-<?php echo $count?>"></div>
				</div>
			</div>
		<hr class="simple">
<?php $count++; ?>
<?php endforeach; ?>
	</div>
<?php $count = 1; ?>
<?php foreach($units as $unit): ?>
	<div class="tab-pane fade in" id="unit-<?php echo $count; ?>">
		<div class="row">
			<div class="col-sm-12">
				<iframe id="unit-<?php echo $count; ?>" class="google_maps unit-container hidden" src="http://<?php echo $unit;?>"></iframe>
			</div>
		</div>
	</div>
<?php $count++; ?>
<?php endforeach; ?>
</div>
