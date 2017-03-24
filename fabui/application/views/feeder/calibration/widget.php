<div class="row">
	<div class="col-sm-12">
		<div class="smart-form">
			<header><?php echo _("Quick settings");?></header>
			<fieldset>
				<div class="row">
					<section class="col col-8">
						<label class="label"><?php echo _("Actual extruder steps");?> </label>
						<label class="input">
							<input type="text" id="actual-step" readonly="readonly" value="<?php echo isset($eeprom['steps_per_unit']['e']) ? $eeprom['steps_per_unit']['e'] : _("n.a."); ?>">
						</label>
					</section>
					<section class="col col-4">
						<label class="label">&nbsp;</label>
						<a href="javascript:void(0);" class="btn btn-sm btn-default btn-block step-change-modal-open" id="button-change-value"><i class="fa fa-pencil"></i> Change value</a>
					</section>
				</div>
			</fieldset>
			<header><?php echo _("Measure and calibrate extruder step");?></header>
			<fieldset>
				<div class="row">
					<section class="col col-8">
						<label class="label"> <?php echo _("Filament to extrude (mm)");?> </label>
						<label class="input">
							<input type="number" value="100" id="filament-to-extrude" readonly="readonly">
						</label>
					</section>
					<section class="col col-4">
						<label class="label">&nbsp;</label>
						<a href="javascript:void(0);" class="btn btn-sm btn-default btn-block extrude" id="button-start-extrude"><i class="fab-lg fab-fw icon-fab-e"></i> <?php echo _("Start to extrude");?></a>
					</section>
				</div>
				
				<div class="row calc-row" style="display:none;">
					<section class="col col-8">
						<label class="label"><?php echo _("Enter the measure of the filament extruded (mm)");?> </label>
						<label class="input">
							<input type="number" placeholder="100" value="100" id="filament-extruded">
						</label>
					</section>
					<section class="col col-4">
						<label class="label">&nbsp;</label>
						<a href="javascript:void(0);" class="btn btn-sm btn-default btn-block recalculate" id="button-recalculate"><i class="fa fa-calculator"></i> <?php echo _("Recalculate");?></a>
					</section>
				</div>
			</fieldset>
		</div>
	</div>
</div>
<div class="row">
<!--  -->
	<div class="col-sm-12 response-container"></div>
</div>

<div class="modal fade" id="change-value-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel"><?php echo _("Set new value for extruder step");?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<input id="feeder-step-new-value" type="number" class="form-control" step="0.01" />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default step-change-modal-cancel" data-dismiss="modal"><?php echo _("Cancel");?></button>
				<button type="button" id="change-extruder-step-value-button" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo _("Change");?></button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
