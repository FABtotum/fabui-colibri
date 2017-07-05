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
<div class="row">
	
	<?php if(!isset($head['feeder'])) :?>
	
	<?php else: ?>
	
		<div class="col-sm-12 printing-head-pro-unload-final-step" style="display:none;">
			<div class="well well-light margin-bottom-10">
				<div class="row">
					<div class="col-sm-12">
						<div class="row prohead-spool-instructions-box">
							<div class="col-sm-6">
								<div class="text-center hidden-xs">
									<img id="pro_head_unload_spool_gif" class="img-responsive" src="">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="description text-center">
									<h1><span class="badge bg-color-blue txt-color-white">2</span></h1>
									<p class="font-md"><?php echo _(" Pull the release lever on the back of the Printing Head PRO."); ?></p>
									<p class="font-md"><?php echo _(" Pull the filament out of the head"); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

</div>
<div class="row">
	<div class="col-sm-12">
		<div class="well well-sm well-light text-center">
			<h3><i class="fa fa-check"></i> <?php echo _("Procedure complete"); ?></h3>
			<button id="restart-button" class="btn btn-default hidden"><?php echo _("Load new filament"); ?></button>
		</div>
	</div>
</div>

