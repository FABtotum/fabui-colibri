<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="step-pane" id="step4" data-step="4">
	<hr class="simple">
	<div class="row">
		<div class="col-sm-6">
			<div class="text-center">
				<h1 class="tada animated">
					<span style="position: relative;">
						<i class="fa fa-play fa-rotate-90 fa-border border-black fa-4x"></i>
						<span><b style="position:absolute; right: -30px; top:-10" class="badge bg-color-green font-md"><i class="fa fa-check txt-color-black"></i> </b></span>
					</span>
				</h1>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="smart-form">
				<fieldset>
					<section>
						<label class="label"><?php echo _('Duration'); ?>: <span class="pull-right"><span class="elapsed-time"></span></span></label>
					</section>
					<div class="row">
						<section class="col col-9">
							<label class="label"><?php echo _("Z's height is"); ?> <strong><span class="z-height"></span></strong>  <?php echo _("Do you want to save it and override the value for the next prints?"); ?> </label>
						</section>
						<section class="col col-3">
							<a style="padding:6px 10px 5px" href="javascript:void(0);" class="btn btn-default btn-block save-z-height"><i class="fa fa-save"></i> <?php echo _("Yes"); ?></a>
						</section>
					</div>
					<section>
						<div class="rating">
							<input type="radio" name="quality" id="quality-10">
							<label for="quality-10"><i class="fa fa-star"></i></label>
							<input type="radio" name="quality" id="quality-9">
							<label for="quality-9"><i class="fa fa-star"></i></label>
							<input type="radio" name="quality" id="quality-8">
							<label for="quality-8"><i class="fa fa-star"></i></label>
							<input type="radio" name="quality" id="quality-7">
							<label for="quality-7"><i class="fa fa-star"></i></label>
							<input type="radio" name="quality" id="quality-6">
							<label for="quality-6"><i class="fa fa-star"></i></label>
							<input type="radio" name="quality" id="quality-5">
							<label for="quality-5"><i class="fa fa-star"></i></label>
							<input type="radio" name="quality" id="quality-4">
							<label for="quality-4"><i class="fa fa-star"></i></label>
							<input type="radio" name="quality" id="quality-3">
							<label for="quality-3"><i class="fa fa-star"></i></label>
							<input type="radio" name="quality" id="quality-2">
							<label for="quality-2"><i class="fa fa-star"></i></label>
							<input type="radio" name="quality" id="quality-1">
							<label for="quality-1"><i class="fa fa-star"></i></label>
							<?php echo _("Quality of the print"); ?>
						</div>
					</section>
					<div class="row">
						<section class="col col-6"><a style="padding:6px 10px 5px" href="javascript:void(0);" class="btn btn-default btn-block restart-print"><i class="fa fa-refresh"></i> <?php echo _("Restart print"); ?></a></section>
						<section class="col col-6"><a style="padding:6px 10px 5px" href="javascript:void(0);" class="btn btn-default btn-block new-print"><i class="fa fa-lg fa-fw icon-fab-<?php echo $type ?>"></i><?php echo _("New"); ?><?php echo ucfirst($type) ?></a></section>
					</div>
				</fieldset>
			</div>
		</div>
	</div>
</div>
