<div class="row">
	<div class="col-sm-12">
		<span><?php echo _("Filters");?></span>
		<?php foreach($capabilities as $key => $value):?>
		<button type="button" class="btn btn-default <?php echo $key == "*" ? 'btn-info' : '';?>  filters-button" data-filter="<?php echo $key?>"><?php echo $value;?></button>
		<?php endforeach; ?>
	</div>
</div>
<hr class="simple">
<div class="row">
	<div class="col-sm-12">
		<div class="owl-carousel owl-theme" id="heads-carousel">
			<?php $counter = 0;?>
			<?php foreach($heads as $index => $head):?>
				<div data-position="<?php echo $counter; ?>" class="panel panel-default item <?php echo implode(" ", $head['capabilities']); ?>  <?php echo $index == $installed_head['filename'] ? 'installed' : 'not-installed' ?>">
					
					<div class="panel-body status">
						<div class="who clearfix">
							<h4 class="text-center ">
								<?php if($head['link'] != ''):?>
									<a title="<?php echo _("More details");?>" class="no-ajax" target="_blank" href="<?php echo $head['link'];?>"><?php echo $head['name']; ?> <small><i class="fa fa-external-link"></i></small></a>
								<?php else:?>
									<?php echo $head['name']; ?>
								<?php endif;?>
							</h4>
						</div>
						<div class="image padding-10 ">
							<img src="/assets/img/head/photo/<?php echo $head['filename']?>.png">
						</div>
						<ul class="links">
							<li class="pull-right" style="padding-right: 0px !important;">
								<a data-action="install" data-head="<?php echo $head['filename']; ?>" class="btn btn-default settings-action <?php echo $index == $installed_head['filename'] ? 'btn-primary ' : ''; ?>  install" ><i class="fa <?php echo $index == $installed_head['filename'] ? 'fa-check' : 'fa-wrench'?>"></i><span> <?php echo $index == $installed_head['filename'] ? _("Installed") : _("Install"); ?></span></a>
							</li>
							<li style="padding-right:0px !important">
								<div class="btn-toolbar">
									<div class="btn-group">
										<a title="<?php echo _("Settings");?>" data-action="edit" data-head="<?php echo $head['filename']; ?>" class="btn btn-default settings-action" ><i class="fa  fa-cog"></i> </a>
										<!--  <a data-action="info" data-head="<?php echo $head['filename']; ?>" class="btn btn-default settings-action" ><i class="fa  fa-info"></i> </a>-->
										<?php if($head['fw_id'] >= 100):?>
										<a data-action="remove" data-head="<?php echo $head['filename']; ?>" class="btn btn-danger settings-action" ><i class="fa  fa-trash"></i> </a>
										<?php endif;?>
									</div>
								</div>
							</li>
						</ul>
					</div>
				</div>
				<?php $counter++;?>
			<?php endforeach; ?>
		</div>
	</div>	
</div>
<div class="row">
	<div class="col-sm-12">
		<div>
			<p class="font-sm"><?php echo _('Before clicking "Install", make sure the head is properly locked in place'); ?></p>
		</div>
	</div>
</div>
<!-- DESCRIPTION MODAL -->
<div class="modal fade" id="descriptionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fabui-head-2"></i> <span id="descriptionModalTitle"></span></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<?php foreach($heads as $index => $head):?>
					<div class="heads-description" id="<?php echo $index ?>_description">
						<p class="center-justified"><?php echo $head['description']?></p>
					</div>
					<?php endforeach;?>
				</div>
			</div>
			<div class="modal-footer">
				<a id="head-more-details" href="" target="_blank" class="btn btn-default pull-left"><?php echo _("More details"); ?> <i class="fa fa-external-link"></i></a>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Close')?></button>
			</div>
		</div>
	</div>
</div>
<!-- MODAL   -->
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
				<h4 class="modal-title"><?php echo _("Head settings");?> </h4>
			</div><!-- /.modal-header -->

			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="alert alert-warning fade in">
							<i class="fa-fw fa fa-warning"></i>
							<strong><?php echo _("Warning");?></strong> <?php echo _("changing these settings may affect the proper functioning of the printer"); ?>
						</div>
					</div>
				</div>
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

						<div class="row advanced-settings">
							<section class="col col-6">
								<label class="label"><?php echo _('Capabilities');?></label>
								<div class="row" id="capabilities-container">
									<div class="col col-4">
										<label class="checkbox">
											<input type="checkbox" id="cap-print" name="capability[]" data-attr="print" class="capability">
											<i></i><?php echo _('3D Print');?></label>
										<label class="checkbox">
											<input type="checkbox" id="cap-mill" name="capability[]" data-attr="mill" class="capability">
											<i></i><?php echo _("Mill");?></label>
										<label class="checkbox">
											<input type="checkbox" id="cap-laser" name="capability[]" data-attr="laser" class="capability">
											<i></i><?php echo _("Laser");?></label>
									</div>
									<div class="col col-4">
										<label class="checkbox">
											<input type="checkbox" id="cap-scan" name="capability[]" data-attr="scan" class="capability">
											<i></i><?php echo _("Scan");?></label>
										<label class="checkbox">
											<input type="checkbox" id="cap-fan" name="capability[]" data-attr="fan" class="capability">
											<i></i><?php echo _("Fan");?></label>
									</div>
									<div class="col col-4">
										<label class="checkbox">
											<input type="checkbox" id="cap-feeder" name="capability[]" data-attr="feeder" class="capability">
											<i></i><?php echo _("Feeder");?></label>
										<label class="checkbox">
											<input type="checkbox" id="cap-4thaxis" name="capability[]" data-attr="4thaxis" class="capability">
											<i></i><?php echo _("4th axis");?></label>
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
					
					<fieldset class="advanced-settings">
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
									<input type="number" id="head-fw_id" name="fw_id" min="100" max="255" value="100">
								</label>
							</section>
						</div>
					</fieldset>

					<fieldset>
						<div class="row">
							<ul class="nav nav-tabs pull-left">
								<li class="active print-settings" style="display:none"><a id="print-tab-button"   data-toggle="tab" href="#print-tab"><?php echo _('Print')?></a></li>
								<li class="mill-settings"         style="display:none"><a id="mill-tab-button"    data-toggle="tab" href="#mill-tab"><?php echo _('Mill')?></a></li>
								<li class="feeder-settings"       style="display:none"><a id="feeder-tab-button"  data-toggle="tab" href="#feeder-tab"><?php echo _('Feeder')?></a></li>
								<li class="4thaxis-settings"      style="display:none"><a id="4thaxis-tab-button" data-toggle="tab" href="#4thaxis-tab"><?php echo _('4th axis')?></a></li>
								<li class="laser-settings"        style="display:none"><a id="laser-tab-button"   data-toggle="tab" href="#laser-tab"><?php echo _("Laser");?></a></li>
							</ul>
						</div>
					</fieldset>
					
					<div class="tab-content padding-10">
					
					<fieldset class="tab-pane fade in" id="print-tab">
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('PID')?></label>
								<label class="input">
									<input type="text" id="head-pid" name="pid" value="">
								</label>
							</section>
							
							<section class="col col-6 advanced-settings">
								<label class="label"><?php echo _("Thermistor");?></label>
								<label class="select">
									<select id="head-thermistor_index" name="thermistor_index">
										<option value="0">Fabtotum</option>
										<option value="1">Standard 100k</option>
									</select> <i></i> </label>
							</section>
						</div>
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('Max temperature')?></label>
								<label class="input">
									<input type="number" id="head-max_temp" name="max_temp" min="180" max="300" value="250">
								</label>
							</section>
							<section class="col col-6">
								<label class="label"><?php echo _('Min temperature')?></label>
								<label class="input">
									<input type="number" id="head-min_temp" name="min_temp" min="100" max="300" value="175">
								</label>
							</section>
							
							<input type="hidden" id="head-nozzle_offset" name="nozzle_offset" value="0"/>
				
						</div>
					</fieldset>
					
					<fieldset class="tab-pane fade in" id="mill-tab">
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
					
					<fieldset class="tab-pane fade in" id="feeder-tab">
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
						<div class="row advanced-settings">
							<section class="col col-4">
								<label class="label"><?php echo _('Max E acceleration (mm/s<sup>2</sup>)')?></label>
								<label class="input">
									<input type="number" id="feeder-max_acceleration" name="max_acceleration" min="0" max="10000" value="100">
								</label>
							</section>
							<section class="col col-4">
								<label class="label"><?php echo _('Max E feedrate (mm/s)')?></label>
								<label class="input">
									<input type="number" id="feeder-max_feedrate" name="max_feedrate" min="0" max="500" value="100">
								</label>
							</section>
							<section class="col col-4">
								<label class="label"><?php echo _('Max E jerk (mm)')?></label>
								<label class="input">
									<input type="number" id="feeder-max_jerk" name="max_jerk" min="0" max="200" value="100">
								</label>
							</section>
							
						</div>
						<div class="row advanced-settings">
							<section class="col col-4">
								<label class="label"><?php echo _('Retract acceleration (mm/s<sup>2</sup>)')?></label>
								<label class="input">
									<input type="number" id="feeder-retract_acceleration" name="retract_acceleration" min="0" max="10000" value="100">
								</label>
							</section>
							<section class="col col-4">
								<label class="label"><?php echo _('Retraction speed (mm/s)')?></label>
								<label class="input">
									<input type="number" id="feeder-retract_feedrate" name="retract_feedrate" min="1" max="500" value="12">
								</label>
							</section>
							<section class="col col-4">
								<label class="label"><?php echo _('Retraction amount (mm)')?></label>
								<label class="input">
									<input type="number" id="feeder-retract_amoun" name="retract_amoun" min="0" max="20" value="4">
								</label>
							</section>
							
						</div>
						<input type="hidden" id="feeder-factory" name="factory" value="0"/>
					</fieldset>
					
					<fieldset class="tab-pane fade in" id="4thaxis-tab">
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('Steps per degree');?></label>
								<label class="input">
									<input type="number" id="4thaxis-steps_per_angle" name="steps_per_angle" min="1" max="5000" value="200" step=0.1>
								</label>
							</section>
						</div>
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _('Max Aacceleration (mm/s<sup>2</sup>)')?></label>
								<label class="input">
									<input type="number" id="4thaxis-max_acceleration" name="max_acceleration" min="0" max="10000" value="100">
								</label>
							</section>
							<section class="col col-6">
								<label class="label"><?php echo _('Max A feedrate (mm/s)')?></label>
								<label class="input">
									<input type="number" id="4thaxis-max_feedrate" name="max_feedrate" min="0" max="500" value="100">
								</label>
							</section>
							<section class="col col-6">
								<label class="label"><?php echo _('Max A jerk (mm)')?></label>
								<label class="input">
									<input type="number" id="4thaxis-max_jerk" name="max_jerk" min="0" max="200" value="100">
								</label>
							</section>
						</div>
						<input type="hidden" id="4thaxis-factory" name="factory" value="0"/>
					</fieldset>
					
					<!-- LASER TAB -->
					<fieldset class="tab-pane fade in" id="laser-tab">
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _("Z focusing distance");?></label>
								<label class="input">
									<input type="number" id="head-focus" name="focus" min="1" max="20" value="2">
								</label>
							</section>
						</div>
						
						<div class="row laser-pro">
							<section class="col col-3">
								<label class="label"><?php echo _("Laser cross offset");?></label>
								<label class="input">
									<span class="icon-prepend">X</span>
									<input type="number" id="offset-laser_cross-x" step="0.1" name="laser_cross-x">
								</label>
							</section>
							<section class="col col-3">
								<label class="label">&nbsp;</label>
								<label class="input">
									<span class="icon-prepend">Y</span>
									<input type="number" id="offset-laser_cross-y" step="0.1" name="laser_cross-y">
								</label>
							</section>
							<section class="col col-3">
								<label class="label"><?php echo _("Laser point offset");?></label>
								<label class="input">
									<span class="icon-prepend">X</span>
									<input type="number" id="offset-laser_point-x" step="0.1" name="laser_point-x">
								</label>
							</section>
							<section class="col col-3">
								<label class="label">&nbsp;</label>
								<label class="input">
									<span class="icon-prepend">Y</span>
									<input type="number" id="offset-laser_point-y" step="0.1" name="laser_point-y">
								</label>
							</section>
						</div>
						
					</fieldset>
					<!-- END LASER TAB -->
					</div><!-- <div class="tab-content padding-10"> -->
					
					<input type="hidden" id="tool" name="tool" value="" />
					<input type="hidden" id="plugins" name="plugins" value="">
				</form>
			</div><!-- /.modal-body -->

			<div class="modal-footer">
				<input id="inputId" type="file" style="display:none" accept=".json">
				<button type="button" class="btn btn-default settings-action pull-left factory-head-button" data-action="factory-reset" title="<?php echo _("Restore factory settings")?>"><i class="fa fa-refresh"></i> <?php echo _("Factory reset");?></button>
				<button type="button" class="btn btn-default settings-action custom-head-button" data-action="import" title="<?php echo _("Import from file")?>"><i class="fa fa-upload"></i> <?php echo _("Import");?></button>
				<button type="button" class="btn btn-default settings-action" data-action="export" title="<?php echo _("Export to file")?>"><i class="fa fa-download"></i> <?php echo _("Export");?></button>
				<button type="button" class="btn btn-primary settings-action" data-action="save" data-dismiss="modal"><i class="fa fa-save"></i> <?php echo _("Save");?></button>
				<button type="button" class="btn btn-success settings-action" data-action="save-install" data-dismiss="modal"><i class="fa fa-wrench"></i> <?php echo _("Save & Install");?></button>
			</div><!-- /.modal-footer -->
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->