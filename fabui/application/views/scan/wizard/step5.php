<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * 
 */
 
?>
<div class="step-pane" id="step5" data-step="5">
	<hr class="simple">
	<div class="row">
		<!--  -->
		<div class="col-sm-12">
			<div class="text-center">
				<h1 class="tada animated">
					<span class="fabtotum-icon">
						<i class="fa fa-play fa-rotate-90 fa-border border-black border-1px-solid fa-4x"></i>
						<span><b class="badge fabtotum-badge"><i class="fa fa-check"></i></b></span>
					</span>
				</h1>
				<h4 class="margin-bottom-20">Scan complete</h4>
				<div class="button-container text-center">
					<a class="btn btn-default margin-bottom-10" id="got-to-projects-manager" href="#"><i class="fa fa-cubes"></i> <?php echo  _("Go to projects manager") ?> </a>
					<button class="btn btn-default margin-bottom-10" id="restart"><i class="fa fa-refresh"></i> <?php echo _("Restart scan"); ?> Restart scan</button>
					<a class="btn btn-default margin-bottom-10 no-ajax" id="download-file"  href="#"><i class="fa fa-download"></i> <?php echo _("Download cloud points file")?> </a>
				</div>
			</div>
		</div>
	</div>
</div>
