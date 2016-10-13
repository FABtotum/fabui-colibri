<?php
/**
 * 
 * @author Krios Mane
 * @author Fabtotum Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
?>
<hr class="simple">
<div class="row">
	<div class="col-sm-3 text-center">
		<div>
			<img style="max-width: 100%;" id="image" class="" src="/assets/img/scan/working_plane_v2.jpg">
		</div>
	</div>
	<div class="col-sm-9">
		<div class="row">
			<div class="col-sm-6 col-xs-6  margin-top-10">
				<div class="form-group">
					<label>First point</label>
					<div class="input-group">
						<span class="input-group-addon">x</span>
						<input class="form-control probing-x1" type="number">
					</div>  
				</div>
			</div>
			<div class="col-sm-6 col-xs-6  margin-top-10">
				<div class="form-group">
					<label>&nbsp;</label>
					<div class="input-group">
						<span class="input-group-addon">y</span>
						<input class="form-control probing-y1"  type="number">
					</div>  
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 col-xs-6">
				<div class="form-group">
					<label>Second point</label>
					<div class="input-group">
						<span class="input-group-addon">x</span>
						<input class="form-control probing-x2" type="number">
					</div>  
				</div>
			</div>
			<div class="col-sm-6 col-xs-6"> 
				<div class="form-group"> 
					<label>&nbsp;</label>
					<div class="input-group">
						<span class="input-group-addon">y</span>
						<input class="form-control probing-y2" type="number">
					</div>  
				</div>
			</div>
		</div>
		<hr class="simple">
		<div class="row">
			<div class="col-sm-12">
				<p class="font-sm">Slide to select quality</p>
				<div id="probing-slider" class="noUiSlider"></div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<h5>Quality: <span class="scan-probing-quality-name"></span></h5>
				<h5>Probes per square millimiters: <span class="scan-probing-sqmm"></span></h5>
			</div>
		</div>
		<hr class="simple">
		<div class="row">
			<div class="col-sm-6 col-xs-6">
				<div class="form-group">
					<label>Z Jump (mm)</label>
					<input type="number"  class="form-control probing-z-hop" value="1" step="0.5">  
				</div>
				<div class="note">
					<p>This is the maximum difference in height of the different portions of the object to probe</p>
				</div>
			</div>
			<div class="col-sm-6 col-xs-6">
				<div class="form-group">
					<label>Detail treshold (mm)</label>
					<input type="number"  class="form-control probing-probe-skip" value="0" step="0.01"> 
				</div>
				<div class="note">
					<p>if Z height change is minor than detail threshold adaptive autoskipping is automatically enabled. Lower values give finer details. 0 = disable</p>
				</div>
			</div>
		</div>
	</div>
</div>
<!--  -->
<script type="text/javascript">
$(document).ready(function() {
	var probingSlider;
	initProbeCrop();
	initProbingSlider();
	setProbingQuality(probingQualities[0], 0);
	$(".button-next").attr('data-scan', 'probing');
});
</script>
