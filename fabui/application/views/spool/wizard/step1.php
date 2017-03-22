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
					<li><button data-action="load" type="button" class="btn btn-default mode-choise">Choose <i class="fa  fa-arrow-right"></i></button></li>
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
					<li><button data-action="unload" type="button" class="btn btn-default mode-choise">Choose <i class="fa  fa-arrow-right"></i></button></li>
				</ul>
			</div>
		</div>
	</div>
</div>

