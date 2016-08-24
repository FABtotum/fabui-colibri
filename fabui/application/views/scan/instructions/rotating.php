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
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
			<div class="panel-body status">
				<div class="who clearfix">
					<h4>Are you ready to scan?</h4>
				</div>
				<!-- first row  -->
				<div class="row text-center" id="rotating-first-row">
					<div class="col-sm-6">
						<div class="col-sm-6 hidden-xs">
							<div class="image text-center">
								<img class="img-resposonsive" style="width: 50%" src="/assets/img/scan/rotating/1.png">
							</div>
						</div>
						<div class="col-sm-6 col-xs-12">
							<div class="text text-center">
								<h1 class=""><span class="badge">1</span></h1>
								<h1 class="text-primary">Remove the platform</h1>
								<p class="font-md">
									Check if the LED light on the platform inside is off. <br>Then remove the building platform, exposing the A axis chuck
								</p>
							</div>
						</div>
					</div>
				</div>
				<!-- end first row  -->
				<!-- second row -->
				<div class="row hidden" id="rotating-second-row">
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-6 hidden-xs">
								<div class="image text-center">
									<img class="img-resposonsive" style="width: 50%" src="/assets/img/scan/rotating/2.png">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="text text-center">
									<h1><span class="badge">2</span></h1>
									<h1 class="text-primary">Attach the object</h1>
									<p class="font-md">Attach the object to the chuck with screws, duct tape, wire etc.</p>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="row">
							<div class="col-sm-6 hidden-xs">
								<div class="image text-center">
									<img class="img-resposonsive" style="width: 50%" src="/assets/img/scan/rotating/3.png">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="text text-center">
									<h1><span class="badge">3</span></h1>
									<h1 class="text-primary">Close</h1>
									<p class="font-md">Close the front</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- end second row 
				<ul class="links text-right">
					<li><button type="button" data-action="prepare" class="btn btn-default rotating-instructions-button">Prepare </button></li>
				</ul>
				-->
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	enableButton('.button-next');
	$(".button-next").attr('data-action', 'prepare');
});
</script>