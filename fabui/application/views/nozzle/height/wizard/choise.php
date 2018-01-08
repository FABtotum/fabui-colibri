<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * 
 */
?>
<div class="row">
	<div class="col-sm-12">
		<div class="alert alert-warning animated fadeIn margin-bottom-10">
			<ul class="list-unstyled">
				<li><i class="fa fa-warning"></i> <?php echo _("Before proceed make sure nozzle is clean"); ?></li>
	            <li><i class="fa fa-warning"></i> <?php echo _("Operate according to safety instructions provided. Nozzle and bed can be hot, exercise caution accordingly");?></li>
	            <?php if($settings['probe']['enable']):?>
	            	<li><i class="fa fa-warning"></i> <?php echo _("Z touch probe enabled: probe's length will be automatically calibrated");?></li>
	            <?php endif;?>
         	</ul>
		</div>
	</div>
</div>
<div class="row">
	<!--  -->
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-body status">
				<div class="who clearfix">
					<h4 class="text-center"><?php echo _("Assisted calibration"); ?></h4>
				</div>
				<div class="text hidden-xs text-center">
					<p><?php echo _("Helps you correct the nozzle height during prints. Each time you swap heads you should re-calibrate"); ?></p>
				</div>
				<ul class="links text-center">
					<li><button data-action="assisted" type="button" class="btn btn-default mode-choise" id="nozzle-assisted-calibration"><?php echo _("Choose");?> <i class="fa  fa-arrow-right"></i></button></li>
				</ul>
			</div>
		</div>
	</div>
	<!--  -->
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-body status">
				<div class="who clearfix">
					<h4 class="text-center"><?php echo _("Fine calibration"); ?></h4>
				</div>
				<div class="text hidden-xs text-center">
					<p><?php echo _("Manually edit the override distance to fine tune the nozzle height during prints."); ?></p>
				</div>
				<ul class="links text-center">
					<li><button data-action="fine" type="button" class="btn btn-default mode-choise" id="nozzle-fine-calibration"><?php echo _("Choose");?>  <i class="fa  fa-arrow-right"></i></button></li>
				</ul>
			</div>
		</div>
	</div>
</div>
