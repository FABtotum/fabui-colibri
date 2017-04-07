<div class="row">
	<div class="smart-form">
		<header><?php echo _("Set the Extended Angle (open position)");?></header>
		<fieldset>
			<div class="row">
				<section class="col col-8">
					<label class="label"><? _("Change the value until you find the one that is perfectly vertical");?></label>
					<label class="input">
						<input id="extend_value" max="165" min="80" type="number" value="<?php echo $eeprom['servo_endstop']['e']?>">
					</label>
				</section>
				<section class="col col-2">
						<label class="label">&nbsp;</label>
						<button data-action="open" class="btn btn-sm btn-default btn-block probe-action"><i class="fa fa-long-arrow-down"></i> <?php echo _("Open probe");?></button>
				</section>
				 
				<section class="col col-2">
						<label class="label">&nbsp;</label>
						<button data-action="open" class="btn btn-sm btn-default btn-block reset"><i class="fa fa-refresh"></i> <?php echo _("Reset");?></button>
				</section>
				
			</div>
		</fieldset>
		<header><?php echo _("Set the Retracted Angle (closed position)");?></header>
		<fieldset>
			<div class="row">
				<section class="col col-8">
					<label class="label"><?php echo _("Change the value until you find the one that is perfectly horizontal");?></label>
					<label class="input">
						<input id="retract_value" min="20" type="number" value="<?php echo $eeprom['servo_endstop']['r']?>">
					</label>
					
				</section>
				<section class="col col-2">
						<label class="label">&nbsp;</label>
						<button data-action="close" class="btn btn-sm btn-default btn-block probe-action"><i class="fa fa-long-arrow-up"></i> <?php echo _("Close probe");?></button>
				</section>
			 
				<section class="col col-2">
						<label class="label">&nbsp;</label>
						<button data-action="close" class="btn btn-sm btn-default btn-block reset"><i class="fa fa-refresh"></i> <?php echo _("Reset");?></button>
				</section>
				
			</div>
		</fieldset>
	</div>
</div>
