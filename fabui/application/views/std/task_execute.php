<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
/* variable initialization */
if( !isset($z_height_values) ) $z_height_values = array('0.1' => '0.1 mm', '0.01' => '0.01 mm');
if( !isset($rpm_label) ) $rpm_label = _("RPM");

if($type == 'print' || $type == "prism")
{
	if(!isset($show_temperature_graph)) $show_temperature_graph = true;
	if(!isset($show_speed)) $show_speed = true;
	if(!isset($show_flowrate)) $show_flowrate = true;
	if(!isset($show_fanspeed)) $show_fanspeed = true;
	if(!isset($show_layer_info)) $show_layer_info = true;
	if(!isset($show_temp_info)) $show_temp_info = true;
	if(!isset($show_change_filament)) $show_change_filament = true;
	if(!isset($show_pause_button)) $show_pause_button = true;
}
else if($type == 'mill')
{
	if(!isset($show_temperature_graph)) $show_temperature_graph = false;
	if(!isset($show_speed)) $show_speed = true;
	if(!isset($show_rpm)) $show_rpm = true;
	if(!isset($show_pause_button)) $show_pause_button = false;
}
else if($type == 'laser')
{
	if(!isset($show_temperature_graph)) $show_temperature_graph = false;
	if(!isset($show_speed)) $show_speed = true;
	if(!isset($show_pause_button)) $show_pause_button = false;
}

if(!isset($show_temperature_graph)) $show_temperature_graph = false;
if(!isset($show_flowrate)) $show_flowrate = false;
if(!isset($show_speed)) $show_speed = false;
if(!isset($show_fanspeed)) $show_fanspeed = false;
if(!isset($show_rpm)) $show_rpm = false;
if(!isset($show_layer_info)) $show_layer_info = false;
if(!isset($show_temp_info)) $show_temp_info = false;
if(!isset($show_change_filament)) $show_change_filament = false;
if(!isset($show_pause_button)) $show_pause_button = true;
if(!isset($show_prism_layer_preview)) $show_prism_layer_preview = false;

$split_view = $show_temperature_graph || $show_prism_layer_preview;
$stats_button_size = $show_temperature_graph ? 4 : 6;

//if($stats_button_size == 6 && $show_pause_button == false) $stats_button_size = 12;
?>
<ul id="createFeed" class="nav nav-tabs bordered">
	<li class="active"><a href="#live-feeds-tab" data-toggle="tab"><?php echo _("Live feeds")?></a></li>
	<li><a href="#controls-tab" data-toggle="tab"><i class="fa fa-sliders"></i> <span class="hidden-xs"><?php echo _("Controls");?></span></a></li>
	<li class="pull-right">
		<!-- 
		<div class="widget-toolbar" id="switch-2" style="display: block;" role="menu">
			<div class="smart-form">
				<label class="toggle" title="<?php echo _("Send an email when the task is finished")?>" >
					<input type="checkbox" id="email-switch" name="checkbox-toggle">
					<i data-swchon-text="ON" data-swchoff-text="OFF"></i>
					<em class="fa fa-envelope"></em> <span class="hidden-xs"><?php echo _("Email") ?></span>
				</label>
			</div>
		</div>
		 -->
		<div class="widget-toolbar" role="menu">
			<div class="btn-group">
				<button type="button" data-action="abort" class="btn btn-default action action-abort"><i class="fa fa-stop"></i> <span class="hidden-xs"> <?php echo _("Abort") ?></span></button>
				<?php if($show_pause_button): ?>
				<button type="button" data-action="pause" class="btn btn-default action isPaused-button action-pause isPaused-button"><i class="fa fa-pause"></i> <span class="hidden-xs"> <?php echo _("Pause") ?></span></button>
				<?php endif;?>
			</div>
		</div>
	</li>
</ul>
<div id="createFeedContent" class="tab-content padding-10">
	<br>
	<div class="tab-pane fade in active" id="live-feeds-tab">
		<div class="row">
			<!-- TEMPERATURES GRAPH -->
			<?php if($show_temperature_graph): ?>
			<div class="col-sm-6">
				
				<div class="row hidden-xs">
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-6">
						<button type="button" class="btn btn-default btn-block dropdown-toggle" data-toggle="dropdown">
							<span>
								<span class="pull-right"><i class="fa fa-caret-down"></i></span>
								<i class="fab-lg fab-fw icon-fab-term"></i> 
								<span class="hidden-xs"><?php echo _("Nozzle"); ?></span> 
								<span class="extruder-temp"></span> / 
								<span class="extruder-target"></span> 
							</span>
						</button>
						<ul class="dropdown-menu">
							<li>
								<div class="checkbox">
									<label><input type="checkbox" class="checkbox graph-line-selector" checked="checked" name="ext-actual"><span><?php echo _("Actual"); ?></span></label>
								</div>
							</li>
							<li>
								<div class="checkbox">
									<label><input type="checkbox" class="checkbox graph-line-selector" name="ext-target"><span><?php echo _("Target"); ?></span></label>
								</div>
							</li>
						</ul>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-6">
						<button type="button" class="btn btn-default btn-sm btn-block dropdown-toggle" data-toggle="dropdown">
							<span>
								<span class="pull-right"><i class="fa fa-caret-down"></i></span>
								<i class="fab-lg fab-fw icon-fab-term"></i> 
								<span class="hidden-xs"><?php echo _("Bed"); ?></span>  
								<span class="bed-temp"></span> / 
								<span class="bed-target"></span>
							</span>
						</button>
						<ul class="dropdown-menu">
							<li>
								<div class="checkbox">
									<label><input type="checkbox" class="checkbox graph-line-selector" checked="checked" name="bed-actual"><span><?php echo _("Actual"); ?></span></label>
								</div>
							</li>
							<li>
								<div class="checkbox">
									<label><input type="checkbox" class="checkbox graph-line-selector" name="bed-target"><span><?php echo _("Target"); ?></span></label>
								</div>
							</li>
						</ul>
					</div>
				</div>

				<div class="row hidden-xs">
					<div id="temperatures-chart" class="chart"> </div>
				</div>
			</div>			
			<?php endif;?>
			<!-- END TEMPERATURES GRAPH -->
			
			<!-- PRISM PREVIEW LAYER -->
			<?php if($show_prism_layer_preview):?>
				<div class="col-sm-6">
					<div style="position:relative; min-height: 220px;">
						<img class="img-responsive" style=" height: 400px; position:absolute; left:100px; top:-100px; transform:rotate(90deg);"  id="prism-preview-layer">
					</div>
					
				</div>
			<?php endif;?>
			<!-- END PRISM PREVIEW LAYER -->
			
			<div class="col-sm-<?php echo $split_view ? '6' : '12' ?> show-stats">
				<div class="row ">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"> <span class="text"> <i class="fa fa-cube"></i> <?php echo _("File"); ?> <span class="pull-right"><span class="task-file-name"> (<?php echo _("Loading");?> ..)</span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					
					<?php if($show_layer_info): ?>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 layer-info hidden"> <span class="text"> <i class="fa fa-database"></i> <?php echo _("Layer"); ?> <span class="pull-right"><span title="<?php echo _("Current layer");?>" class="task-layer-current"></span> <?php echo _("of")?> <span title="<?php echo _("Total layers");?>" class="task-layer-total"></span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					<?php endif ?>
					
					<?php if($show_temp_info): ?>
					<div class="col-xs-12 col-sm-6 col-md-12 col-lg-12 hidden-sm hidden-md hidden-lg"> <span class="text"> <?php echo _("Nozzle"); ?> <span class="pull-right"> <span class="extruder-temp"></span> / <span class="extruder-target"></span></span> </span>
						<div class="fake-progress"></div>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-12 col-lg-12 hidden-sm hidden-md hidden-lg"> <span class="text"> <?php echo _("Bed"); ?> <span class="pull-right"><span class="bed-temp"></span> / <span class="bed-target"></span> </span>
						<div class="fake-progress"></div>
					</div>
					<?php endif; ?>
					
					<div class="col-xs-12 col-sm-6 col-md-12 col-lg-12 "> <span class="text"> <?php echo _("Progress"); ?> <span class="pull-right task-progress"></span> </span>
						<div class="progress hidden-xs">
							<div class="progress-bar " id="task-progress-bar"></div>
						</div>
						<div class="fake-progress visible-xs"></div>
					</div>
						
					<?php if($show_speed): ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12 hidden-xs" > <span class="text"> <?php echo _("Speed"); ?> <span class="pull-right"><span class="task-speed"></span> / 500 %</span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-speed-bar"></div>
						</div> </div>
					<?php endif;?>
					
					<?php if($show_flowrate): ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12 hidden-xs"> <span class="text"> <?php echo _("Flow rate"); ?><span class="pull-right"><span class="task-flow-rate"></span> / 500 %</span></span></span>
						<div class="progress">
							<div class="progress-bar" id="task-flow-rate-bar"></div>
						</div> </div>
					<?php endif;?>
					
					<?php if($show_fanspeed): ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12 hidden-xs"> <span class="text"> <?php echo _("Fan"); ?> <span class="pull-right"><span class="task-fan"></span> %</span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-fan-bar"></div>
						</div> </div>
					<?php endif;?>
					
					<?php if($show_rpm): ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12 hidden-xs"> <span class="text"><?php echo $rpm_label; ?> <span class="pull-right"><span class="task-rpm"></span> </span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-rpm-bar"></div>
						</div> </div>
					<?php endif; ?>
					
					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6"> <span class="text"> <?php echo _("Elapsed time"); ?> <span class="pull-right"><span class="elapsed-time"></span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6"> <span class="text"> <?php echo _("Est. time left"); ?> <span class="pull-right"><span class="estimated-time-left"></span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					<span class="show-stat-buttons margin-top-10" style="padding-left:13px;padding-right:13px;"> 
						
						<?php if($show_change_filament):?>
							<span class="col-xs-<?php echo $stats_button_size; ?> col-sm-<?php echo $stats_button_size; ?> col-md-<?php echo $stats_button_size; ?> col-lg-<?php echo $stats_button_size; ?> pull-right"> 
								<button type="button" class="btn btn-default btn-block change-filament-button"><i class="fa fa-circle-o-notch"></i> <span class="hidden-xs hidden-sm"><?php echo _("Change filament"); ?></span> </button> 
							</span>
						<?php endif;?> 
						
						<?php if($show_pause_button): ?>
							<span class="col-xs-<?php echo $stats_button_size; ?> col-sm-<?php echo $stats_button_size; ?> col-md-<?php echo $stats_button_size; ?> col-lg-<?php echo $stats_button_size; ?> pull-right"> 
								<button type="button" data-action="pause"  class="btn btn-default btn-block  action isPaused-button action-pause"><i class="fa fa-pause"></i> <span class="hidden-xs"><?php echo _("Pause"); ?></span> </button> 
							</span>
						<?php endif;?>
						
						<span class="col-xs-<?php echo $stats_button_size; ?> col-sm-<?php echo $stats_button_size; ?> col-md-<?php echo $stats_button_size; ?> col-lg-<?php echo $stats_button_size; ?> pull-right"> 
							<button type="button" data-action="abort"  class="btn btn-default btn-block  action"><i class="fa fa-stop"></i> <span class="hidden-xs"><?php echo _("Abort"); ?></span> </button> 
						</span> 
						
					</span>
				</div>
			</div>
		</div>
		<hr class="simple">
		<div class="row">
			<div class="col-sm-12">
				<div class="textarea-div">
					<div class="typearea">
						<div class="custom-scroll trace-console" ></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="tab-pane fade in" id="controls-tab">
		<div class="row margin-bottom-20">
			<div class="col-sm-6 col-xs-12">
				<span class="col-xs-12 col-sm-12 margin-bottom-10">
					<?php if($type == 'print'): ?>
					<label class="text-center" data-original-title="<i class='fa fa-info-circle'></i> <?php echo _("Override Z height"); ?>" data-html="true"  rel="popover-hover" data-placement="top"  data-content="<?php echo _("If first layers are too high or too close to the bed, use this function to finely calibrate the distance from the nozzle and the bed"); ?><br><?php echo _("Usually 0.05mm increments are enough to make a difference");?>"><i class='fabui-nozzle'></i> <?php echo _("Override Z height"); ?> : <strong><span class="z-height"></span> mm</strong></label>
					<?php else: ?>
					<label><?php echo _("Override Z height"); ?>: <strong><span class="z-height"></span> mm</strong></label>
					<?php endif; ?>
				</span>
				<span class="show-stat-buttons"> 
					<span class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> 
						<button data-rel="tooltip" data-placement="bottom" data-original-title="<?php echo _("Away from the nozzle"); ?>" type="button" data-action="zHeight" data-attribute="+" class="btn btn-default btn-block action"><i class="fa fa-arrow-down"></i></button>
					</span> 
					<span class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> 
						<label style="width:100%;">
							<?php echo form_dropdown('zHeight', $z_height_values, '', 'id="zHeight" class="form-control"'); ?> <i></i>
						</label>
					</span>
					<span class="col-xs-4 col-sm-4 col-md-4 col-lg-4"> 
						<button data-rel="tooltip" data-placement="bottom" title="<?php echo _("Closer to the nozzle"); ?>" type="button" data-action="zHeight" data-attribute="-" class="btn btn-default btn-block action"><i class="fa fa-arrow-up"></i></button>
					</span>
				</span>
			</div>
		</div>
		<hr class="simple hidden-md hidden-sm hidden-lg">
		<?php if($show_temp_info): ?>
		<div class="row padding-10">
			<div class="col-sm-6 margin-bottom-50">
				<h4><i class="icon-fab-term"></i> <span><?php echo _("Nozzle"); ?></span> <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="Extruder current temperature"  class="extruder-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="Extruder target temperature" class="slider-extruder-target"></span></strong> &deg;C</span></h4>
				<div id="create-ext-target-slider" class="noUiSlider sliders"></div>
			</div>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-6 margin-bottom-50">
				<h4><i class="icon-fab-term"></i> <?php echo _("Bed"); ?> <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="Bed current temperature" class="bed-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="Bed target temperature" class="slider-bed-target"></span></strong> &deg;C</span></h4>
				<div id="create-bed-target-slider" class="noUiSlider sliders"></div>
			</div>
		</div>
		<hr class="simple">
		<?php endif; ?>
		<div class="row padding-10">
			<?php if($show_speed): ?>
			<div class="col-sm-<?php echo $type == 'print' ?  4 : 6 ?> margin-bottom-50">
				<h4><?php echo _("Speed"); ?> <span class="pull-right"><strong><span class="slider-task-speed"></span></strong>  %</span></h4>
				<div id="create-speed-slider" class="noUiSlider sliders"></div>
			</div>
			<?php endif;?>
			<?php if($show_flowrate): ?>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-4 margin-bottom-50">
				<h4><?php echo _("Flow rate"); ?> <span class="pull-right"><strong><span class="slider-task-flow-rate"></span></strong> %</span></h4>
				<div id="create-flow-rate-slider" class="noUiSlider sliders"></div>
			</div>
			<?php endif;?>
			<?php if($show_fanspeed): ?>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-4 margin-bottom-50">
				<h4><?php echo _("Fan"); ?> <span class="pull-right"><strong><span class="slider-task-fan"></span></strong> %</span></h4>
				<div id="create-fan-slider" class="noUiSlider sliders"></div>
			</div>
			<?php endif;?>
			<?php if($show_rpm):?>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-6 margin-bottom-50">
				<h4><?php echo $rpm_label; ?> <span class="pull-right"><strong><span class="slider-task-rpm"></span></strong> </span></h4>
				<div id="create-rpm-slider" class="noUiSlider sliders"></div>
			</div>
			<?php endif;?>
		</div>
	</div>
</div>
<?php if($type=='print'):?>
<div class="modal fade" id="filament-change-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fa fa-circle-o-notch"></i> <?php echo _("Change filament");?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="smart-form">
							<fieldset>
								<section>
									<label class="label"><?php echo _("Filament");?></label>
									<label class="select">
										<?php echo form_dropdown('filament', $filamentsOptions, 'large', 'id="filament"');?> <i></i>
									</label>
								</section>
							</fieldset>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<span class=""> 
							<span class="col-xs-6 col-sm-6 col-md-6 col-lg-6"> 
								<button type="button" data-action="load"  class="btn btn-default btn-block filament-button-choose-action"><span></span> <?php echo _("Load");?> </button> 
							</span> 
							<span class="col-xs-6 col-sm-6 col-md-6 col-lg-6"> 
								<button type="button" data-action="unload"  class="btn btn-default btn-block filament-button-choose-action"><span></span> <?php echo _("Unload");?> </button> 
							</span>
						</span>
					</div>
				</div>
				<hr class="simple">
				<div class="row">
					<div id="filament-load-description"   class="col-sm-12  filament-action-descritpion hidden">
					
						<?php if(in_array('feeder', $head['capabilities'])):?>
							<div class="row">
								<div class="col-sm-6 margin-bottom-10">
									<div class="well well-light text-center">
										<h1><span class="badge bg-color-blue txt-color-white">1</span></h1>
										<p><?php echo _("Prepare the filament by cutting it at an angle. This helps the insertion of the filament."); ?></p>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="well well-light text-center">
										<h1><span class="badge bg-color-blue txt-color-white">2</span></h1>
										<p><?php echo _("Pull the release lever on the back of the Printing Head PRO."); ?></p>
										<p><?php echo _("Insert the filament until it starts coming out from the nozzle");  ?></p>
										<p><?php echo _("Release the release lever ") ?></p>
										<p><?php echo _("Lock the feeding tube in place ") ?></p>
									</div>
								</div> 
							</div>
						<?php else:?>
							<div class="row">
								<div class="col-sm-4 margin-bottom-10">
									<div class="well well-light text-center">
										<h1><span class="badge bg-color-blue txt-color-white">1</span></h1>
										<p><?php echo _("The Side cover is magnetically locked. Pull the panel confidently in order to access to the spool compartment."); ?></p>
									</div>
								</div>
								<div class="col-sm-4 margin-bottom-10">
									<div class="well well-light text-center">
										<h1><span class="badge bg-color-blue txt-color-white">2</span></h1>
										<p><?php echo _("Prepare the filament by cutting it at an angle. This helps the insertion of the filament."); ?></p>
									</div>
								</div>								
								<div class="col-sm-4 margin-bottom-10">
									<div class="well well-light text-center">
										<h1><span class="badge bg-color-blue txt-color-white">3</span></h1>
										<p><?php echo _("Insert the filament in the PTFE tube until you reach the feeder (you can feel it: last cm becomes harder and then you cannot push further)"); ?></p>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<div id="filament-unload-description" class="col-sm-12 filament-action-descritpion hidden">
						
						<?php if(in_array('feeder', $head['capabilities'])):?>
							<div class="row">
								<div class="col-sm-6 margin-bottom-10">
									<div class="well well-light text-center">
										<h1><span class="badge bg-color-blue txt-color-white">1</span></h1>
										<p><?php echo _("Remove the Feeding tube by pushing down the black cap and then pulling the tube itself."); ?></p>
										<p><?php echo _("Press start to begin procedure"); ?></p>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="well well-light text-center">
										<h1><span class="badge bg-color-blue txt-color-white">2</span></h1>
										<p><?php echo _("After the filament was pulled out from the nozzle");?></p>
										<p><?php echo _("Pull the release lever on the back of the Printing Head PRO."); ?></p>
										<p><?php echo _("Pull the filament out of the head"); ?></p>
									</div>
								</div>
							</div>
						<?php else: ?>
						<?php endif;?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"> <?php echo _("Close");?></button>
				<button type="button" id="filament-start-button" class="btn btn-success" data-action=""> <?php echo _("Start");?></button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php endif;?>