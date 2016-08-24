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
	<!-- benchmark image -->
	<div class="col-sm-3 hidden-xs">
		<div class="duck_container text-center va-middle">
		</div>
	</div>
	<!-- quality parameters -->
	<div class="col-sm-9">
		<div class="row padding-10">
			<!-- slider -->
			<div class="col-sm-12">
				<p class="font-sm">Slide to select quality</p>
				<div id="rotating-slider" class="noUiSlider"></div>
			</div>
		</div>
		<hr class="simple">
		<div class="row padding-10">
			<div class="col-sm-12">
				<h5>Quality: <span class="scan-quality-name"></span></h5>
				<p class="font-sm scan-quality-description"></p>
				<hr class="simple">
				<div class="row margin-bottom-10">
					<div class="col-sm-6 col-xs-6">
						<div class="form-group">
							<label>Slices</label>
							<input type="text"  class="form-control quality-slices" readonly="readonly">   
						</div>
					</div>
					<div class="col-sm-6 col-xs-6">
						<div class="form-group">
							<label>Iso</label>
							<input type="text"  class="form-control quality-iso" readonly="readonly">  
						</div>
					</div> 
				</div>
				<div class="row">
					<div class="col-sm-6 col-xs-6">
						<div class="form-group">
							<label>Width</label>
							<input type="text"  class="form-control quality-resolution-width" readonly="readonly">  
						</div>
					</div>
					<div class="col-sm-6 col-xs-6">
						<div class="form-group">
							<label>Height</label>
							<input type="text" class="form-control quality-resolution-height" readonly="readonly">  
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	var rotatingSlider;
	initRotatingSlider();
	setScanQuality('rotating', scanQualites[0], 0);
	$(".button-next").attr('data-scan', 'rotating');
});
</script>