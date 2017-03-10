<?php
/**
 * 
 * @author 
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>

<div class="row">
	<div class="col-sm-6">
		<p class="margin-top-10" style="padding:10px">
			<span class=""> <i class="fab-lg fab-fw icon-fab-term "></i> <span class="mode-label"><?php echo _("Nozzle");?></span> <span class="spd-temperature"></span> &deg;C</span>			
		</p>
		<div id="temperatures-chart" style="margin-top:0px;" class="chart"> </div>	
	</div>
	<div class="col-sm-6">
		<div class="smart-form">
			<!-- <header>Status: <span class="header-status"><?php echo isset($task) ? 'running' : 'stopped'; ?></span></header> -->
			<header class="">
				<?php echo _("Installed head"); ?>: <strong><?php echo $installed_head['name'];?></strong>
				<br><small><?php echo _("Current PID"); ?> <code class="current-pid"><?php echo str_replace("M301", "", $installed_head['pid']) ?></code></small>
			</header>
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label"><?php echo _("Target temperature");?> </label>
						<label class="input">
							<input type="number" max="250" value="200" id="temperature_target">
						</label>
					</section>
					<section class="col col-6">
						<label class="label"><?php echo _("Cicle");?></label>
						<label class="input">
							<input type="number" value="8" id="cycle">
						</label>
					</section>
				</div>
				<div class="row tuning-values">
					<section class="col col-4">
						<label class="label">Kp</label>
						<label class="input"><input type="text" id="kp" readonly="readonly" /></label>
					</section>
					<section class="col col-4">
						<label class="label">Ki</label>
						<label class="input"><input type="text" id="ki" readonly="readonly" /></label>
					</section>
					<section class="col col-4">
						<label class="label">Kd</label>
						<label class="input"><input type="text" id="kd" readonly="readonly" /></label>
					</section>
				</div>		
			</fieldset>
		</div>
	</div>
</div>
