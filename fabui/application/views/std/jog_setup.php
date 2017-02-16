<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>

<hr class="simple">
<div id="row_3" class="row interstitial" >
	<div class="col-sm-12">
		<div class="well">
			<div class="row">
				<div class="col-sm-7">
					<div class="text-center">
						<div class="row">
							<div class="col-sm-7">
								<img style=" display: inline;" class="img-responsive" src="<?php echo $jog_image?>" />
							</div>
							<div class="col-sm-5">
								
								<h1></h1>
								<h2 class="text-center"><?php echo $jog_message; ?></h2>
								
							</div>
						</div>
					</div>
					<div class="text-left">
						<div class="row">
							<div class="col-sm-7">
								 Lower the Z so that the laser head is max 1 mm away from the stock material.
							</div>
						</div>
					</div>
				</div>
			    <div class="col-sm-5">
			        <div class="text-center">
						<div class="row">
							<div class="col-sm-12">

								<div class="row">
									<ul class="nav nav-tabs pull-right">
										<li data-attribute="jog" class="tab active"><a data-toggle="tab" href="#jog-tab"> Jog</a></li>
										<li data-attribute="touch" class="tab"><a data-toggle="tab" href="#touch-tab"> Touch</a></li>
									</ul>
								</div>

								<div class="row">
									<div class="tab-content padding-10 well">
										<div class="tab-pane fade in active" id="jog-tab">
											<!-- jog step and feedrate inputs -->
											<div class="smart-form">
												<fieldset style="background: none !important;">
													<div class="row">
														<section class="col col-4">
															<label class="label-mill text-center">XY Step (mm)</label>
															<label class="input">
																<input  type="number" id="xy-step" value="1" step="1" min="0" max="100">
															</label>
														</section>
														<section class="col col-4">
															<label class="label-mill text-center">Feedrate</label>
															<label class="input">
																<input  type="number" id="feedrate" value="1000" step="50" min="0" max="10000">
															</label>
														</section>
														<section class="col col-4">
															<label class="label-mill text-center">Z Step (mm)</label>
															<label class="input"> 
																<input type="number" id="z-step" value="0.5" step="0.1" min="0" max="100">
															</label>
														</section>
													</div>
												</fieldset>
											</div>
											<!-- jog controls placeholder -->
											<div class="jog-controls-holder"></div>
									</div>
									
									
									<div class="tab-pane fade in" id="touch-tab">
										<div class="touch-container">
											<img class="bed-image" src="/assets/img/std/hybrid_bed_v2_small.jpg" >
											
											<div class="button_container">
												<button class="btn btn-primary touch-home-xy" data-toggle="tooltip" title="Before using the touch interface you need to home XY axis first.<br><br>Make sure that the head will not hit anything during homing." data-container="body" data-html="true">
													Home XY
												</button>
											</div>
										</div>
									</div>
								</div>
								
								</div>
							</div>

							
							<?php if($fourth_axis == True): ?>
							<div class="col-sm-4">
								<span>Mode:</span><span class="mode"> 4th Axis</span>
								<div class="knobs-demo  text-center margin-top-10" id="mode-a">
									<input class="knob" data-width="150" value="0" data-cursor="true" data-step="0.5" data-min="1" data-max="360" data-thickness=".3" data-fgColor="#A0CFEC" data-displayInput="true">
								</div>
							</div>
                            <?php endif; ?>
							
							
						</div>
			        </div>
        		</div>
    		</div>
		</div>
    </div>

</div>

