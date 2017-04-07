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
					<h4><?php echo _("Are you ready to scan?");?></h4>
				</div>
				<div class="row">
					<div class="col-sm-6 hidden-xs text-center">
						<div class="image">
							<img style="width: 50%" class="responsive" src="/assets/img/controllers/scan/probing/1.png">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="text text-center">
							<h1 class=""><span class="badge">1</span></h1>
							<h1 class="text-primary"><?php echo _("Position the object");?></h1>
							<p class="font-md">
								<?php echo _("Position your object in the center of the platform when prompted. <br>Secure it with a double sided tape or use the fixture holes");?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	enableButton('.button-next');
	$(".button-next").attr('data-action', 'start');
});
</script>
