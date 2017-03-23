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
<!-- ASSISTED CALIBRATION  -->
<div class="row calibration-row hidden" id="assisted-row">
	<div class="col-sm-6 text-center hidden-xs">
		<img style="max-width: 50%; display: inline;" class="img-responsive" src="/assets/img/controllers/probe/head_calibration.png">
	</div>
	<div class="col-sm-6">
		<p class="font-md text-center margin-top-10"><?php echo _("Using the buttons below, raise the bed until a standard piece of copy paper (80 mg) can barely move between the nozzle and the bed") ?></p>
		<p class="text-center">( <?php echo _("Caution the nozzle is hot"); ?> )</p>
		<div class="smart-form">
			<fieldset>
				<!-- LABELS ROW -->
				<div class="row">
					<section class="col col-3 text-center">
						<label><strong>Z</strong></label>
					</section>
					<section class="col col-6 text-center">
						<label><strong><?php echo _("Step"); ?> (mm)</strong></label>
					</section>
					<section class="col col-3 text-center">
						<label><strong>Z</strong></label>
					</section>
				</div>
				<!-- END LABELS ROW -->
				<!-- INPUTS ROW  -->
				<div class="row">
					<section class="col col-3">
						<button rel="tooltip" data-action="+" type="button" title="<?php echo _("Away from nozzle");?>" class="btn btn-default btn-sm btn-block z-action"><i class="fa fa-arrow-down"></i> </button>
					</section>
					<section class="col col-6">
						<label class="input">
							<input class="text-center" id="step" name="step" type="number" max="10" min="0" step="0.01" value="1">
						</label>
					</section>
					<section class="col col-3">
						<button rel="tooltip" data-action="-" type="button" title="<?php echo _("Close to nozzle");?>" class="btn btn-default btn-sm btn-block z-action"><i class="fa fa-arrow-up"></i></button>
					</section>
				</div>
				<!-- END INPUTS ROW  -->
				<!-- CALIBRATE BUTTON ROW  -->
				<div class="row">
					<section class="col col-3 text-center"></section>
					<section class="col col-6 text-center">
						<button rel="tooltip" type="button" title="" id="calibrate-height" class="btn btn-default btn-sm btn-block"> Calibrate </button>
					</section>
					<section class="col col-3 text-center"></section>
				</div>
				<!-- END CALIBRATE BUTTON ROW  -->
			</fieldset>
		</div>
	</div>
</div>
<!-- END ASSISTED CALIBRATION  -->

<!-- FINE CALIBRATION  -->
<div class="row calibration-row hidden" id="fine-row">
	<div class="col-sm-6">
		<h4 class="text-center"><?php echo _("Fine calibration");?></h4>
		<p class="font-md text-center margin-top-10"><?php echo _("If the print first layer is too high or too close to the bed, use this function to finely calibrate the distance from the nozzle and the bed during 3D-prints"); ?></p>
		<p class="font-md text-center"><?php echo _("Usually 0.05mm increments are enough to make a difference");?></p>
	</div>
	<div class="col-sm-6">
		<div class="smart-form">
			<fieldset>
				<!-- LABELS ROW -->
				<div class="row">
					<section class="col col-3 text-center">
						<label><strong><?php echo _("Closer"); ?></strong></label>
					</section>
					<section class="col col-6 text-center">
						<label><strong><?php echo _("Distance override"); ?> (<span id="nozzle-offset"></span>mm)</strong></label>
					</section>
					<section class="col col-3 text-center">
						<label><strong><?php echo _("Further"); ?></strong></label>
					</section>
				</div>
				<!--END LABELS ROW -->
				<!-- INPUTS ROW -->
				<div class="row">
					<section class="col col-3">
						<label class="input">
							<button rel="tooltip" data-action="-" type="button" class="btn btn-default btn-sm btn-block change-over"><i class="fa fa fa-minus"></i> </button>
						</label>
					</section>
					<section class="col col-6">
						<label class="input">
							<input class="text-center" max="2" min="-2" id="over" type="text" readonly="true" value="0">
						</label>
					</section>
					<section class="col col-3">
						<label class="input">
							<button rel="tooltip" data-action="+" type="button" class="btn btn-default btn-sm btn-block change-over"><i class="fa fa fa-plus"></i> </button>
						</label>
					</section>
				</div>
				<!--END INPUTS ROW -->
				<!-- CALIBRATE BUTTON ROW  -->
				<div class="row">
					<section class="col col-3 text-center"></section>
					<section class="col col-6 text-center">
						<button rel="tooltip" type="button" title="" id="save-override" class="btn btn-default btn-sm btn-block"> Save </button>
					</section>
					<section class="col col-3 text-center"></section>
				</div>
				<!-- END CALIBRATE BUTTON ROW  -->
			</fieldset>
		</div>
	</div>
</div>
<!-- END FINE CALIBRATION  -->