<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * List of all files
 * 
 */
?>
<hr class="simple">
<div class="" style="<?php echo ($this->session->settings['feeder']['show'] == true)?"":"display:none;"; ?>" >
	<h4 class="text-center"><?php echo _('Engage feeder'); ?></h4>
	<div class="row">
		<div class="col-sm-12 col-md-12">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-4 hidden-xs">
						<div class="product-image medium text-center">
							<img class="img-responsive" src="/assets/img/controllers/feeder/feeder.png" />
						</div>
					</div>
					<div class="col-sm-8">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">0</span></h1>
							<p class="font-md"><?php echo _('To engage the filament feeder first pres ENGAGE button below and wait for the bed to stop moving.'); ?></p>
							<p class="font-md"><?php echo _('Then push the small button under the building platform near the 4th axis chuck. Apply a good amount of force when pushing.'); ?></p>
							<button class="btn btn-primary engage-feeder"><?php echo _("Engage");?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<h4 class="text-center"><?php echo _('Follow the instructions'); ?></h4>
<div class="row">
	<div class="col-sm-6 col-md-6  ">
		<div class="product-content product-wrap clearfix">
			<div class="row">
				<div class="col-sm-4 hidden-xs">
					<div class="product-image medium text-center">
						<img class="img-responsive" src="/assets/img/controllers/create/additive/1.png">
					</div>
				</div>
				<div class="col-sm-8">
					<div class="description text-center">
						<h1><span class="badge bg-color-blue txt-color-white">1</span></h1>
						<p class="font-md"><?php echo _('Make sure that the working plane is clean and free to use'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-6 col-md-6 ">
		<div class="product-content product-wrap clearfix">
			<div class="row">
				<div class="col-sm-4 col-sx-4  hidden-xs">
					<div class="product-image medium  text-center">
						<img class="img-responsive" src="/assets/img/controllers/create/additive/2.png">
					</div>
				</div>
				<div class="col-sm-8">
					<div class="description text-center">
						<h1><span class="badge bg-color-blue txt-color-white">2</span></h1>
						<p class="font-md"><?php echo _('Close the cover'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<h4 class="text-center"><?php echo _('Choose calibration type'); ?></h4>
<div class="row">
	<div class="col-sm-6 col-md-6">
		<div  class="product-content product-wrap clearfix">
			<div class="row">
				<div class="col-sm-4 col-sx-4 hidden-xs">
					<div class="product-image mini text-center">
						<img class="img-responsive" src="/assets/img/controllers/create/homing.png">
					</div>
				</div>
				<div class="col-sm-8">
					<div class="description text-center">
						<div class="radio margin-top-10">
							<label>
							<input type="radio" value="home_all" class="radiobox style-0" <?php echo $this->session->settings["print"]["calibration"] == 'homing' ? 'checked="checked"' : '' ?>  name="calibration">
								<span><?php echo _('Simple homing'); ?></span> 
							</label>
						</div>
						<p class="font-md"><?php echo _('Quickly home all axis. Works well with a well calibrated working plane'); ?>. <br>(<?php echo _('Suggested'); ?>)</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-sm-6 col-md-6">
		<div class="product-content product-wrap clearfix">
			<div class="row">
				<div class="col-sm-4 col-sx-4 hidden-xs">
					<div class="product-image mini text-center">
						<img class="img-responsive" src="/assets/img/controllers/create/abl.png">
					</div>
				</div>
				<div class="col-sm-8">
					<div class="description text-center">
						<div class="radio margin-top-10">
							<label>
								<input value="auto_bed_leveling" type="radio" class="radiobox style-0" <?php echo $this->session->settings["print"]["calibration"] == 'auto_bed_leveling' ? 'checked="checked"' : '' ?> name="calibration">
								<span><?php echo _('Auto bed leveling'); ?></span> 
							</label>
						</div>
						<p class="font-md"><?php echo _('Probes the working plane to auto-correct movements to account for not leveled bed'); ?>.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
