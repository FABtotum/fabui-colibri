<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="step-pane" id="step2" data-step="2">
	<hr class="simple">
	<h4 class="text-center"><?php echo _("Follow the instructions"); ?></h4>
	<div class="row">
		<div class="col-sm-6 col-md-6">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-4 col-sx-4  hidden-xs">
						<div class="product-image medium  text-center">
							<img class="img-responsive" src="/assets/img/controllers/create/additive/2.png">
						</div>
					</div>
					<div class="col-sm-8">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">1</span></h1>
							<p class="font-md"><?php echo _("Close the cover"); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-6">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-4 col-sx-4  hidden-xs">
						<div class="product-image medium  text-center">
							<img class="img-responsive" style="max-width: 100%; margin-top:10px;" src="/assets/img/controllers/create/subtractive/1.png">
						</div>
					</div>
					<div class="col-sm-8">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">2</span></h1>
							<p class="font-md"><?php echo _('Jog the endmill to the desired origin point, press HOME and when you are ready press "Next"'); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- jog controls row -->
	<div class="row">
		<!-- directions -->
		<div class="col-sm-6 text-center">
			<!-- left column -->
			<div class="btn-group-vertical">
				<button type="button" class="btn btn-default btn-circle btn-xl directions" data-attribute-direction="up-left"><i class="fa fa-arrow-left fa-1x fa-rotate-45"></i></button>
				<button type="button" class="btn btn-default btn-circle btn-xl directions" data-attribute-direction="left"><i class="fa fa-arrow-left fa-1x"></i></button>
				<button type="button" class="btn btn-default btn-circle btn-xl directions" data-attribute-direction="down-left"><i class="fa fa-arrow-down fa-1x fa-rotate-45"></i></button>
			</div>
			<!-- center column -->
			<div class="btn-group-vertical">
				<button type="button" class="btn btn-default btn-circle btn-xl directions" data-attribute-direction="up"><i class="fa fa-arrow-up fa-1x"></i></button>
				<button type="button" class="btn btn-default btn-circle btn-xl directions" data-attribute-direction=""></button>
				<button type="button" class="btn btn-default btn-circle btn-xl directions" data-attribute-direction="down"><i class="fa fa-arrow-down fa-1x"></i></button>
			</div>
			<!-- right column -->
			<div class="btn-group-vertical">
				<button type="button" class="btn btn-default btn-circle btn-xl directions" data-attribute-direction="up-right"><i class="fa fa-arrow-up fa-1x fa-rotate-45"></i></button>
				<button type="button" class="btn btn-default btn-circle btn-xl directions" data-attribute-direction="right"><i class="fa fa-arrow-right fa-1x"></i></button>
				<button type="button" class="btn btn-default btn-circle btn-xl directions" data-attribute-direction="down-right"><i class="fa fa-arrow-right fa-1x fa-rotate-45"></i></button>
			</div>
			
			<!-- Z axis -->
			<div class="btn-group-vertical text-center">
				<button type="button" class="btn btn-default btn-circle btn-xl jog-axisz" data-attribute-function="moveZ" data-attribute-value="up"><i class="fa fa-arrow-up fa-1x "></i></button>
				<span>Z</span>
				<button type="button" class="btn btn-default btn-circle btn-xl jog-axisz" data-attribute-function="moveZ" data-attribute-value="down"><i class="fa fa-arrow-down fa-1x"></i></button>
			</div>
		</div> 
		<!-- xyz steps & feedrates -->
		<div class="col-sm-6">
			<div class="smart-form">
				<fieldset>
					<div class="row">
						<section class="col col-6">
							<label class="label">XY <?php echo _('Step'); ?></label>
							<label class="input">
								<input type="number" min="1" value="10" id="xyStep">
							</label>
						</section>
						<section class="col col-6">
							<label class="label">Z <?php echo _('Step'); ?> (mm)</label>
							<label class="input">
								<input type="number" min="1" value="10" id="zStep">
							</label>
						</section>
					</div>
					<section>
						<label class="label">XYZ <?php echo _('Feedrate'); ?></label>
						<label class="input">
							<input type="number" min="1" value="1000" id="xyzFeed">
						</label>
					</section>
				</fieldset>
			</div>
		</div>
	</div>
</div>
