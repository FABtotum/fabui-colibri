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
	<?php if($this->session->settings['feeder']['show'] == false): ?>
	<h4 class="text-center">Follow the instructions</h4>
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
							<p class="font-md">Make sure that the working plane is clean and free to use</p>
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
							<p class="font-md">Close the cover</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<hr class="simple">
	<h4 class="text-center">Choose calibration type</h4>
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
									<input type="radio" value="home_all" class="radiobox style-0" checked="checked" name="calibration">
									<span>Simple homing</span> 
								</label>
							</div>
							<p class="font-md">Quickly home all axis. Works well with a well calibrated working plane. <br>(SUGGESTED)</p>
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
									<input value="auto_bed_leveling" type="radio" class="radiobox style-0" name="calibration">
									<span>Auto Bed Leveling</span> 
								</label>
							</div>
							<p class="font-md">Probes the working plane to auto-correct movements to account for not leveled bed.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php else: ?>
	<?php endif; ?>
</div>
