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

<div class="row margin-bottom-10">
	<div class="col-sm-12">
		<div class="well well-sm well-light">
			<ul class="list-unstyled">
	            <li><i class="fa fa-wrench"></i> <?php echo _("A pair of scissors or clippers are suggested"); ?></li>
	            <li><i class="fa fa-warning"></i> <?php echo _("Operate according to safety instructions provided. Nozzle and bed can be hot, exercise caution accordingly"); ?></li>
	            <li><i class="fa fa-info-circle"></i> <?php echo _("Do not push or pull the filament too hard, as it can break inside the filament mechanism during the operations"); ?></li>
         	</ul>
		</div>
	</div>
</div>

<div class="row">
	<!--  -->
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-body status">
				<div class="who clearfix">
					<h4 class="text-center"><?php echo _("Load"); ?></h4>
				</div>
				<div class="text hidden-xs text-center">
					<p><?php echo _("Automatically load the filament into the machine"); ?></p>
				</div>
				<ul class="links text-center">
					<li><button data-action="load" type="button" class="btn btn-default mode-choise" id="spool-load-choice">Choose <i class="fa  fa-arrow-right"></i></button></li>
				</ul>
			</div>
		</div>
	</div>
	<!--  -->
	<div class="col-sm-6">
		<div class="panel panel-default">
			<div class="panel-body status">
				<div class="who clearfix">
					<h4 class="text-center"><?php echo _("Unload"); ?></h4>
				</div>
				<div class="text hidden-xs text-center">
					<p><?php echo _("Automatically unload the filament from the machine"); ?></p>
				</div>
				<ul class="links text-center">
					<li><button data-action="unload" type="button" class="btn btn-default mode-choise" id="spool-unload-choice">Choose <i class="fa  fa-arrow-right"></i></button></li>
				</ul>
			</div>
		</div>
	</div>
</div>

