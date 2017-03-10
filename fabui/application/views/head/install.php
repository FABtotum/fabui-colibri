<div class="row">
	<div class="col-sm-12 alerts-container">
<?php if(!isset($units['hardware']['head']) || $units['hardware']['head'] == ''): ?>
		<div class="alert alert-warning animated  fadeIn" role="alert">
			<i class="fa fa-warning"></i><strong>Warning</strong> Seems that you still have not set the head your are using.
		</div>
<?php else: ?>
		<div class="alert alert-info animated  fadeIn" role="alert">
			<i class="fa fa-info-circle"></i> Currently  your <strong>FABtotum Personal Fabricator</strong> is configured to use <strong><?php echo  $heads[$head]['name']; ?></strong>
		</div>
<?php endif; ?>
	</div>
</div>

<style>.jumbotron{padding:20px;} .jumbotron p {font-size: 15px;} </style>

<div class="row">
	<div class="col-sm-12">
		<div class="well well-light">
			<div class="row margin-bottom-10">
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-12">
							<p class="font-md">Make sure you removed the filament, milling bits and any other accessory on the head.<br>See also <a href="maintenance/spool-management">spool maintenance</a></p>
						</div>
					</div>	
					<div class="smart-form">
						<fieldset style="background: none !important;">
							<label class="label font-md">Please select which head you want to install </label>
							<section >
								<div class="input-group">
									<label class="select"> <?php echo form_dropdown('heads', $heads_list, $head, 'class="input-lg" id="heads"'); ?> <i></i> </label>
									<span class="input-group-btn btn-group-lg">
									  <button type="button" id="edit-button" class="btn btn-default btn-success settings-action" title="<?php echo _("Edit");?>" data-action="edit"><i class="fa fa-pencil" aria-hidden="true"></i></button>
									  <button type="button" id="remove-button" class="btn btn-default btn-danger settings-action" title="<?php echo _("Remove");?>" data-action="remove"><i class="fa fa-trash" aria-hidden="true"></i></button>
									</span>
								</div>
							</section>
						</fieldset>
					</div>
					
					<div class="row" style="margin-top:-30px">
						<div class="col-sm-12">
							<div class="smart-form">
								<fieldset style="background: none !important;">
									<div id="description-container">
										<?php if($head != 'head_shape'): ?>
											<div class="jumbotron">
											<p class="margin-bottom-10 "><?php echo $heads[$head]['description'] ?></p>
											<?php if($heads[$head]['link'] != ''): ?>
											<a style="padding: 6px 12px;" target="_blank" href="<?php echo $heads[$head]['link']; ?>" class="btn btn-default no-ajax">More details</a>
											</div>
											<?php endif; ?>
										<?php endif; ?>
									</div>
								</fieldset>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 text-center image-container">
					<a target="_blank" href="javascript:void(0);"><img id="head_img" style="width: 50%; display: inline; cursor:default;" class="img-responsive" src="<?php echo '/assets/img/head/'.$head.'.png'; ?>"></a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row hidden">
	<?php foreach($heads as $name => $val): ?>
		<div id="<?php echo $name ?>_description">
			<p class="margin-bottom-10"><?php echo $val['description']; ?></p>
			<?php if($val['link'] != ''): ?>
			<a style="padding: 6px 12px;" target="_blank" href="<?php echo $val['link']; ?>" class="btn btn-default no-ajax">More details</a>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>


<!-- SETTINGS MODAL -->
<div class="modal fade" tabindex="-1" role="dialog" id="settingsModal">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo _("Head settings");?></h4>
			</div><!-- /.modal-header -->

			<div class="modal-body">
				
				<form action="" class="smart-form" id="head-settings">
					<fieldset>
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('Name');?></label>
								<label class="input">
									<input type="text" data-inputmask-regex="[_a-z A-Z0-9]*" class="plugin-adaptive-meta" id="head-name" name="name" placeholder="My New Head">
								</label>
							</section>
							
							<section class="col col-6 url-container">
								<label class="label"><?php echo _('URL');?></label>
								<label class="input">
									<input type="text" class="plugin-adaptive-meta" id="head-link" name="link" placeholder="More info link">
								</label>
							</section>
							
						</div>
						
						<section class="description-container">
							<label class="label"><?php echo _('Description');?></label>
							<label class="textarea">
								<textarea id="head-description" name="description" rows="3" placeholder="Head description"></textarea>
							</label>
						</section>

						<div class="row">
							
							<section class="col col-6">
								<label class="label"><?php echo _('Capabilities');?></label>
								<div class="row" id="capabilities-container">
									<div class="col col-6">
										<label class="checkbox">
											<input type="checkbox" id="cap-print" name="capability[]" data-attr="print" class="capability">
											<i></i>Print</label>
										<label class="checkbox">
											<input type="checkbox" id="cap-mill" name="capability[]" data-attr="mill" class="capability">
											<i></i>Mill</label>
										<label class="checkbox">
											<input type="checkbox" id="cap-laser" name="capability[]" data-attr="laser" class="capability">
											<i></i>Laser</label>
									</div>
									<div class="col col-6">
										<label class="checkbox">
											<input type="checkbox" id="cap-fan" name="capability[]" data-attr="fan" class="capability">
											<i></i>Fan</label>
										<label class="checkbox">
											<input type="checkbox" id="cap-feeder" name="capability[]" data-attr="feeder" class="capability">
											<i></i>Feeder</label>
										<label class="checkbox">
											<input type="checkbox" id="cap-scan" name="capability[]" data-attr="scan" class="capability">
											<i></i>Scan</label>

									</div>
								</div>
							</section>
							
							<section class="col col-6">
								<label class="label"><?php echo _('Custom initialization');?></label>
								<label class="textarea">
									<textarea class="gcodearea" id="head-custom_gcode" name="custom_gcode" rows="5" placeholder="Gcode initialization sequence"></textarea>
								</label>
							</section>
							
						</div>
					</fieldset>
					
					<fieldset>
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _("Working mode");?></label>
								<label class="select">
									<select id="head-working_mode" name="working_mode">
										<option value="0">Hybrid</option>
										<option value="1">FFF</option>
										<option value="2">Laser</option>
										<option value="3" selected>CNC</option>
										<option value="4">Scan</option>
										<option value="5">SLA</option>
									</select> <i></i> </label>
							</section>
							
							<section class="col col-6">
								<label class="label"><?php echo _('Soft ID')?></label>
								<label class="input">
									<input type="number" id="head-fw_id" name="fw_id" min="0" max="255" value="100">
								</label>
							</section>
						</div>
					</fieldset>
					
					<fieldset class="nozzle-settings" style="display:none">
						<div class="row">
							<section class="col col-4">
								<label class="label"><?php echo _('PID')?></label>
								<label class="input">
									<input type="text" id="head-pid" name="pid" value="M301 P20 I3.5 D30">
								</label>
							</section>
							
							<section class="col col-4">
								<label class="label"><?php echo _("Thermistor");?></label>
								<label class="select">
									<select id="head-thermistor_index" name="thermistor_index">
										<option value="0">Fabtotum</option>
										<option value="1">Standard 100k</option>
									</select> <i></i> </label>
							</section>
							
							<section class="col col-4">
								<label class="label"><?php echo _('Max temperature')?></label>
								<label class="input">
									<input type="number" id="head-max_temp" name="max_temp" min="180" max="300" value="250">
								</label>
							</section>
							
							<input type="hidden" id="head-probe_offset" name="probe_offset" value="0"/>
						</div>
					</fieldset>
					
					<fieldset class="motor-settings" style="display:none">
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('Min RPM')?></label>
								<label class="input">
									<input type="number" id="head-min_rpm" name="min_rpm" value="6000">
								</label>
							</section>
							
							<section class="col col-6">
								<label class="label"><?php echo _('Max RPM')?></label>
								<label class="input">
									<input type="number" id="head-max_rpm" name="max_rpm" value="14000">
								</label>
							</section>
						</div>
					</fieldset>
					
					<fieldset class="feeder-settings" style="display:none">
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('Feeder step')?></label>
								<label class="input">
									<input type="number" id="head-feeder_step" name="feeder_step" min="1" max="50000" step="0.1" value="200">
								</label>
							</section>
						</div>
					</fieldset>
					
				</form>
				
				
			</div><!-- /.modal-body -->

			<div class="modal-footer">
			<input id="inputId" type="file" style="display:none" accept=".json">
			<button type="button" class="btn btn-default settings-action pull-left factory-head-button" data-action="factory-reset" title="<?php echo _("Restore factory settings")?>"><i class="fa fa-refresh"></i> <?php echo _("Factory reset");?></button>
			<button type="button" class="btn btn-default settings-action custom-head-button" data-action="import" title="<?php echo _("Import from file")?>"><i class="fa fa-upload"></i> <?php echo _("Import");?></button>
			<button type="button" class="btn btn-default settings-action" data-action="export" title="<?php echo _("Export to file")?>"><i class="fa fa-download"></i> <?php echo _("Export");?></button>
			<button type="button" class="btn btn-primary settings-action" data-action="save" data-dismiss="modal"><i class="fa fa-save"></i> <?php echo _("Save");?></button>
			</div><!-- /.modal-footer -->

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


