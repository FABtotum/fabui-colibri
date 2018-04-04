<div class="row">
	<div class="col-sm-12 alerts-container">
<?php if(!isset($units['hardware']['feeder']) || $units['hardware']['feeder'] == ''): ?>
		<div class="alert alert-warning animated  fadeIn" role="alert">
			<i class="fa fa-exclamation-triangle"></i><?php echo _("<strong>Warning</strong> Seems that you still have not set the feeder your are using.");?>
		</div>
<?php else: ?>
		<div class="alert alert-info animated  fadeIn" role="alert">
			<i class="fa fa-info-circle"></i> <?php echo pyformat( _("Currently  your <strong>FABtotum Personal Fabricator</strong> is configured to use <strong>{0}</strong>"), array($feeders[$feeder]['name']) );?>
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
					<!--div class="row">
						<div class="col-sm-12">
							<p class="font-md">Make sure you removed the filament, milling bits and any other accessory on the head.<br>See also <a href="maintenance/spool-management">spool maintenance</a></p>
						</div>
					</div-->
					<div class="smart-form">
						<fieldset style="background: none !important;">
							<label class="label font-md"><?php echo _("Please select which feeder you want to configure");?> </label>
							<section >
								<div class="input-group">
									<label class="select"> <?php echo form_dropdown('feeders', $feeder_list, $feeder, 'class="input-lg" id="feeders"'); ?> <i></i> </label>
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

											<div class="jumbotron">
											<p class="margin-bottom-10 "><?php echo $feeders[$feeder]['description'] ?></p>
											<?php if($feeders[$feeder]['link'] != ''): ?>
											<a style="padding: 6px 12px;" target="_blank" href="<?php echo $feeders[$feeder]['link']; ?>" class="btn btn-default no-ajax"><?php echo _("More details");?></a>
											</div>
											<?php endif; ?>
									</div>
								</fieldset>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 text-center image-container">
					<a target="_blank" href="javascript:void(0);"><img id="feeder_img" style="width: 50%; display: inline; cursor:default;" class="img-responsive" src="<?php echo '/assets/img/feeder/'.$feeder.'.png'; ?>"></a>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- SETTINGS MODAL -->
<div class="modal fade" tabindex="-1" role="dialog" id="settingsModal">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">

			<div class="modal-header">
				<span class="pull-right">
					<span class="onoffswitch-title"><?php echo _("Advanced settings");?></span> 
					<span class="onoffswitch">
						<input type="checkbox"  name="advanced_settings_switch" class="onoffswitch-checkbox" id="advanced_settings_switch">
						<label class="onoffswitch-label" for="advanced_settings_switch"> 
							<span class="onoffswitch-inner" data-swchon-text="ON" data-swchoff-text="OFF"></span> 
							<span class="onoffswitch-switch"></span> 
						</label> 
					</span> 										
				</span>
				<h4 class="modal-title"><?php echo _("Feeder settings");?></h4>
			</div><!-- /.modal-header -->

			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="alert alert-warning fade in">
							<i class="fa-fw fa fa-exclamation-triangle"></i>
							<strong><?php echo _("Warning");?></strong> <?php echo _("changing these settings may affect the proper functioning of the printer"); ?>
						</div>
					</div>
				</div>
				<form action="" class="smart-form" id="feeder-settings">
					<fieldset>
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('Name');?></label>
								<label class="input">
									<input type="text" data-inputmask-regex="[-_a-z A-Z0-9]*" class="plugin-adaptive-meta" id="feeder-name" name="name" placeholder="My new feeder">
								</label>
							</section>
							
							<section class="col col-6 url-container">
								<label class="label"><?php echo _('URL');?></label>
								<label class="input">
									<input type="text" class="plugin-adaptive-meta" id="feeder-link" name="link" placeholder="More info link">
								</label>
							</section>
							
							<input type="hidden" id="feeder-factory" name="factory" value="0"/>
							
						</div>
						
						<section class="description-container">
							<label class="label"><?php echo _('Description');?></label>
							<label class="textarea">
								<textarea id="feeder-description" name="description" rows="3" placeholder="Feeder description"></textarea>
							</label>
						</section>
					</fieldset>
					
					<fieldset class="feeder-settings">
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('Steps per unit');?></label>
								<label class="input">
									<input type="number" id="feeder-steps_per_unit" name="steps_per_unit" min="1" max="5000" value="3048.16" step=0.1>
								</label>
							</section>
							<section class="col col-6">
								<label class="label"><?php echo _('Tube length (mm)');?></label>
								<label class="input">
									<input type="number" id="feeder-tube_length" name="tube_length" min="0" max="2000" value="0">
								</label>
							</section>
						</div>
					</fieldset>
					
					<fieldset class="4thaxis-settings" style="display:none">
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('Steps per degree');?></label>
								<label class="input">
									<input type="number" id="feeder-steps_per_angle" name="steps_per_angle" min="1" max="5000" value="3048.16" step=0.1>
								</label>
							</section>
						</div>
					</fieldset>
					
					<fieldset class="advanced-settings">
						<div class="row">
							<section class="col col-4">
								<label class="label"><?php echo _('Max acceleration (mm/s<sup>2</sup>)')?></label>
								<label class="input">
									<input type="number" id="feeder-max_acceleration" name="max_acceleration" min="0" max="10000" value="100">
								</label>
							</section>
							<section class="col col-4">
								<label class="label"><?php echo _('Max feedrate (mm/s)')?></label>
								<label class="input">
									<input type="number" id="feeder-max_feedrate" name="max_feedrate" min="0" max="500" value="100">
								</label>
							</section>
							<section class="col col-4">
								<label class="label"><?php echo _('Max jerk (mm)')?></label>
								<label class="input">
									<input type="number" id="feeder-max_jerk" name="max_jerk" min="0" max="200" value="100">
								</label>
							</section>
							
						</div>
					</fieldset>
					<fieldset class="advanced-settings">
						<div class="row">
							<section class="col col-4 feeder-settings">
								<label class="label"><?php echo _('Retraction speed (mm/s)')?></label>
								<label class="input">
									<input type="number" id="feeder-retract_feedrate" name="retract_feedrate" min="1" max="500" value="12">
								</label>
							</section>
							
							<section class="col col-4 feeder-settings">
								<label class="label"><?php echo _('Retract acceleration (mm/s<sup>2</sup>)')?></label>
								<label class="input">
									<input type="number" id="feeder-retract_acceleration" name="retract_acceleration" min="0" max="10000" value="100">
								</label>
							</section>
							<section class="col col-4">
								<label class="label"><?php echo _('Retraction amount (mm)')?></label>
								<label class="input">
									<input type="number" id="feeder-retract_amount" name="retract_amount" min="0" max="20" value="4">
								</label>
							</section>
						</div>
					</fieldset>
					<fieldset class="advanced-settings">
						<section>
							<label class="label"><?php echo _('Custom initialization');?></label>
							<label class="textarea">
								<textarea class="gcodearea" id="feeder-custom_gcode" name="custom_gcode" rows="5" placeholder="Gcode initialization sequence"></textarea>
							</label>
						</section>
					</fieldset>
				</form>
			</div><!-- /.modal-body -->

			<div class="modal-footer">
			<input id="inputId" type="file" style="display:none" accept=".json">
			<button type="button" class="btn btn-default settings-action pull-left factory-feeder-button" data-action="factory-reset" title="<?php echo _("Restore factory settings")?>"><i class="fa fa-refresh"></i> <?php echo _("Factory reset");?></button>
			<button type="button" class="btn btn-default settings-action custom-feeder-button" data-action="import" title="<?php echo _("Import from file")?>"><i class="fa fa-upload"></i> <?php echo _("Import");?></button>
			<button type="button" class="btn btn-default settings-action" data-action="export" title="<?php echo _("Export to file")?>"><i class="fa fa-download"></i> <?php echo _("Export");?></button>
			<button type="button" class="btn btn-primary settings-action" data-action="save" data-dismiss="modal"><i class="fa fa-save"></i> <?php echo _("Save");?></button>
			<button type="button" class="btn btn-success settings-action" data-action="save-install" data-dismiss="modal"><i class="fa fa-wrench"></i> <?php echo _("Save & Configure");?></button>
			</div><!-- /.modal-footer -->

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
