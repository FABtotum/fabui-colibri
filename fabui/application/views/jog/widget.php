<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<!-- temperatures sliders row -->
<div class="row">
</div>
<!-- jog controls row -->
<div class="row">
	<!-- xyz steps & feedrates -->
	<div class="col-sm-4 text-center">
		
		<div class="jog-container"></div>
		
		<div class="smart-form">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">XY Step</label>
						<label class="input">
							<input type="number" min="1" value="1" id="xyStep">
						</label>
					</section>
					<section class="col col-6">
						<label class="label">Z Step (mm)</label>
						<label class="input">
							<input type="number" min="1" value="0.5" id="zStep">
						</label>
					</section>
				</div>
				<section>
					<label class="label">XYZ Feedrate</label>
					<label class="input">
						<input type="number" min="1" value="1000" id="xyzFeed">
					</label>
				</section>
			</fieldset>
		</div>
	</div>
	<!-- directions -->
	<div class="col-sm-4 text-center">

		<div class="touch-container">
			<!--img class="touch-container" src="/assets/plugin/fab_laser/img/hybrid_bed_v2_small.jpg" style="opacity: 0.5; filter: alpha(opacity=50);"-->
			<img class="bed-image" src="/assets/img/std/hybrid_bed_v2_small.jpg" >
			
			<div class="button_container">
				<button class="btn btn-primary touch-home-xy" data-toggle="tooltip" title="Before using the touch interface you need to home XY axis first.<br><br>Make sure that the head will not hit anything during homing." data-container="body" data-html="true">
					Home XY
				</button>
			</div>
		</div>

	</div> 
	<div class="col-sm-4">
		
		<ul id="myTab3" class="nav nav-tabs tabs-pull-right ">
			<li class="pull-right">
				<a href="#fourthaxis-tab" data-toggle="tab">4th axis</a>
			</li>
			<li class="pull-right">
				<a href="#extruder-tab" data-toggle="tab">Extruder</a>
			</li>
			<li class="active pull-right">
				<a href="#functions-tab" data-toggle="tab">Head Functions</a>
			</li>
		</ul>
		
		<div id="myTabContent3" class="tab-content">
			<div class="tab-pane fade in" id="extruder-tab">
				<div class="smart-form">
					<fieldset>
						<div class="row">
							<section class="col col-6">
								<button class="btn btn-default btn-block extruder" data-attribute-type="+" style="padding:6px 10px 5px"><i class="fa fa-plus"></i></button>
							</section>
							<section class="col col-6">
								<button class="btn btn-default btn-block extruder" data-attribute-type="-" style="padding:6px 10px 5px"><i class="fa fa-minus"></i></button>
							</section>
						</div>
						<div class="row">
							<section class="col col-6">
								<label class="label">Step (mm)</label>
								<label class="input">
									<input type="number" value="10" min="1" id="extruderStep">
								</label>
							</section>
							<section class="col col-6">
								<label class="label">Feedrate</label>
								<label class="input">
									<input type="number" value="300" min="100" id="extruder-feedrate">
								</label>
							</section>
						</div>
					</fieldset>
				</div>
			</div>
			
			<div class="tab-pane fade in" id="fourthaxis-tab">
				
			</div>
			
			<div class="tab-pane fade in active" id="functions-tab">
				<div class="padding-10"></div>
				<div class="col-sm-12 margin-bottom-50">
					<h4><i class="icon-fab-term"></i> <span>Nozzle</span> <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="Extruder current temperature"  class="extruder-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="Extruder target temperature" class="slider-extruder-target"></span></strong> &deg;C</span></h4>
					<div id="create-ext-target-slider" class="noUiSlider sliders"></div>
				</div>
				
				<div class="col-sm-12 margin-bottom-50">
					<h4><i class="icon-fab-term"></i> Bed <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="Bed current temperature" class="bed-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="Bed target temperature" class="slider-bed-target"></span></strong> &deg;C</span></h4>
					<div id="create-bed-target-slider" class="noUiSlider sliders"></div>
				</div>
					
				<div class="col-sm-12 margin-bottom-50">
					<h4>Fan <span class="pull-right"><strong><span class="slider-task-fan"></span></strong> %</span></h4>
					<div id="create-fan-slider" class="noUiSlider sliders"></div>
				</div>
				
				<div class="col-sm-12 margin-bottom-50">
					<h4>RPM <span class="pull-right"><strong><span class="slider-task-fan"></span></strong> %</span></h4>
					<div id="create-rpm-slider" class="noUiSlider sliders"></div>
				</div>
			</div>
			
		</div>
	</div>
</div>
<hr class="simple">
<!-- mdi & console -->
<div class="row">
	<div class="col-sm-6">
		<div class="chat-footer">
			<div class="textarea-div">
				<div class="typearea">
					<textarea placeholder=">_ Write command"  id="mdiCommands" class="custom-scroll" rows="10"></textarea>
				</div>
			</div>
			<!-- CHAT REPLY/SEND -->
			<span class="textarea-controls">
				<button class="btn btn-sm btn-primary pull-right" type="button" id="mdiButton">Send</button> 
				<span class="pull-right smart-form" style="margin-top: 3px; margin-right: 10px;"> <label class="checkbox pull-right">
					<input type="checkbox" name="enterSend" id="enterSend" checked="checked">
					<i></i>Press <strong> ENTER </strong> to </label> </span> <a href="javascript:void(0);" class="pull-left"></a> 
			</span>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="chat-footer">
			<div class="textarea-div">
				<div class="typearea jogResponseContainer2">
					<div class="consoleContainer custom-scroll"></div>
				</div>
			</div>
			<!-- CHAT REPLY/SEND -->
			<span class="textarea-controls">
				<button class="btn btn-sm btn-primary pull-right" type="button" id="clearButton">Clear</button> 
			</span>
		</div>
	</div>
</div>
