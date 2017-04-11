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
			<img style="max-width: 100%;" id="image" class="" src="/assets/img/controllers/scan/working_plane_v2.jpg">
		</div>
	</div>
	<div class="col-sm-9">
		<!-- benchmark image -->
		<div class="col-sm-4 hidden-xs">
			<div class="duck_container text-center va-middle">
			</div>
		</div>
		<!-- quality parameters -->
		<div class="col-sm-8">
			<div class="row padding-10">
				<!-- slider -->
				<div class="col-sm-12">
					<p class="font-sm"><?php echo _("Slide to select quality");?></p>
					<div id="sweep-slider" class="noUiSlider"></div>
				</div>
			</div>
			<hr class="simple">
			<div class="row padding-10">
				<div class="col-sm-12">
					<h5><?php echo _("Quality");?>: <span class="scan-quality-name"></span></h5>
					<p class="font-sm scan-quality-description"></p>
					<hr class="simple">
					<div class="row margin-bottom-10">
						<div class="col-sm-6 col-xs-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon"><?php echo _("Slices");?></span>
									<input type="text"  class="form-control quality-slices" readonly="readonly">   
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-xs-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon"><?php echo _("ISO");?></span>
									<input type="text"  class="form-control quality-iso" readonly="readonly">  
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6 col-xs-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon"><?php echo _("Width");?></span>
									<input type="text"  class="form-control quality-resolution-width" readonly="readonly">  
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-xs-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon"><?php echo _("Height");?></span>
									<input type="text" class="form-control quality-resolution-height" readonly="readonly">  
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<hr class="simple">
	<!-- IMAGE CROP ROW  -->
	<div class="row padding-10">
		<div class="col-sm-12">
			<p><?php echo _("Select scan area");?></p>
		</div>
	</div>
		<div class="col-sm-6 col-xs-6">
			<div class="form-group">
				<label><?php echo _("Start");?></label>
				<div class="input-group">
					<span class="input-group-addon">x</span>
					<input type="number" class="form-control sweep-start">  
				</div>
			</div>
		</div>
		
		<div class="col-sm-6 col-xs-6">
			<div class="form-group">
				<label><?php echo _("End");?></label>
				<div class="input-group">
					<span class="input-group-addon">x</span>
					<input type="number"  class="form-control sweep-end"> 
				</div>
			</div>
		</div>
		
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	var sweepSlider; 
	initSweepSlider();
	setScanQuality('rotating', scanQualites[0], 0);
	initSweepCrop();
	$(".button-next").attr('data-scan', 'sweep');
});
</script>
