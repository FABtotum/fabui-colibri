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
	<div class="col-sm-4">
		<div class="smart-form">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">XY Step</label>
						<label class="input">
							<input type="number" min="1" value="10" id="xyStep">
						</label>
					</section>
					<section class="col col-6">
						<label class="label">Z Step (mm)</label>
						<label class="input">
							<input type="number" min="1" value="10" id="#Step">
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
	<div class="col-sm-4">
		
		<ul id="myTab3" class="nav nav-tabs tabs-pull-right ">
			<li class="active pull-right">
				<a href="#extruder-tab" data-toggle="tab">Extruder</a>
			</li>
		</ul>
		<div id="myTabContent3" class="tab-content">
			<div class="tab-pane fade in active" id="extruder-tab">
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
		</div>
	</div>
</div>
<hr class="simple">
<!-- mdi & console -->
<div class="row">
	<div class="col-sm-12">
		<pre class="console"></pre>
	</div>
</div>