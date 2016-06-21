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
							<input type="number" min="1" value="10" class="xyStep">
						</label>
					</section>
					<section class="col col-6">
						<label class="label">Z Step (mm)</label>
						<label class="input">
							<input type="number" min="1" value="10" class="zStep">
						</label>
					</section>
				</div>
				<section>
					<label class="label">Feedrate</label>
					<label class="input">
						<input type="number" min="1" value="1000" class="xyzFeed">
					</label>
				</section>
			</fieldset>
		</div>
	</div>
	
	<!-- directions -->
	<div class="col-sm-6">
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
	<div class="col-sm-4"></div>
</div>

<!-- mdi & console -->
<div class="row"></div>