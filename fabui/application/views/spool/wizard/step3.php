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

<!-- LOAD ROW  -->
<div class="row get-ready-row" id="load-row">
	<?php if(!isset($head['feeder'])) :?>
		<!--  -->
		<div class="col-sm-4">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-6 hidden-xs">
						<div class="product-image medium text-center">
							<img class="img-responsive" src="/assets/img/controllers/spool/open-cover.png">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">1</span></h1>
							<p class="font-md"><?php echo _("Open the right cover panel"); ?></p>
							<p><?php echo _("The Side cover is magnetically locked. Pull the panel confidently in order to access to the spool compartment."); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--  -->
		<div class="col-sm-4">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-6 hidden-xs">
						<div class="product-image medium text-center">
							<img class="img-responsive" src="/assets/img/controllers/spool/filament_cut_spool.png">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">2</span></h1>
							<p class="font-md"><?php echo _("Cut the filament"); ?></p>
							<p><?php echo _("Prepare the filament by cutting it at an angle. This helps the insertion of the filament."); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--  -->
		<div class="col-sm-4">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-6 hidden-xs">
						<div class="product-image medium text-center">
							<img class="img-responsive" src="/assets/img/controllers/spool/insert-filament.png">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">3</span></h1>
							<p class="font-md"><?php echo _("Insert the filament"); ?></p>
							<p><?php echo _("Insert the filament in the PTFE tube until you reach the feeder (you can feel it: last cm becomes harder and then you cannot push further)"); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php else: ?>
		<!--  -->
		<div class="col-sm-4">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-6 hidden-xs">
						<div class="product-image medium text-center">
							<img class="img-responsive" src="/assets/img/controllers/spool/filament_cut_spool.png">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">1</span></h1>
							<p class="font-md"><?php echo _("Cut the filament"); ?></p>
							<p><?php echo _("Prepare the filament by cutting it at an angle. This helps the insertion of the filament."); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--  -->
		<!--  -->
		<div class="col-sm-4">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-6 hidden-xs">
						<div class="product-image medium text-center">
							<img class="img-responsive" src="">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">2</span></h1>
							<p><?php echo _("Pull the release lever on the back of the Printing Head PRO."); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--  -->
		<!--  -->
		<div class="col-sm-4">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-6 hidden-xs">
						<div class="product-image medium text-center">
							<img class="img-responsive" src="">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">3</span></h1>
							<p><?php echo _("Insert the filament in the head."); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--  -->
	<?php endif; ?>
</div>
<!-- UNLOAD ROW  -->
<div class="row get-ready-row" id="unload-row">
	<?php if(!isset($head['feeder'])) :?>
		<div class="col-sm-12">
			<div class="well well-sm well-light text-center">
				<h3><?php echo _("Press Next to proceed"); ?></h3>
			</div>
		</div>
	<?php else: ?>
		<!--  -->
		<div class="col-sm-6">
			<div class="product-content product-wrap clearfix">
				<div class="row">
					<div class="col-sm-6 hidden-xs">
						<div class="product-image medium text-center">
							<img class="img-responsive" src="">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="description text-center">
							<h1><span class="badge bg-color-blue txt-color-white">1</span></h1>
							<p><?php echo _("Remove the Feeding tube by pushing down the black cap and then pulling the tube itself."); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--  -->
	<?php endif; ?>
</div>

