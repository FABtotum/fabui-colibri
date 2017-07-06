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
$stats_button_size = $type == 'print' ? 4 : 6;
 
?>
<ul id="createFeed" class="nav nav-tabs bordered">
	<li class="active"><a href="#live-feeds-tab" data-toggle="tab"><?php echo _("Live feeds")?></a></li>
	<li><a href="#controls-tab" data-toggle="tab"><i class="fa fa-sliders"></i> <?php echo _("Controls");?></a></li>
	<li class="pull-right">
		<div class="widget-toolbar" id="switch-2" style="display: block;" role="menu">
			<div class="smart-form">
				<label class="toggle" title="<?php echo _("Send an email when the task is finished")?>" >
					<input type="checkbox" id="email-switch" name="checkbox-toggle">
					<i data-swchon-text="ON" data-swchoff-text="OFF"></i>
					<em class="fa fa-envelope"></em> <span class="hidden-xs"><?php echo _("Email") ?></span>
				</label>
			</div>
		</div>
		<div class="widget-toolbar" role="menu">
			<div class="btn-group">
				<button type="button" data-action="abort" class="btn btn-default action action-abort"><i class="fa fa-stop"></i> <span class="hidden-xs"> <?php echo _("Abort") ?></span></button>
				<button type="button" data-action="pause" class="btn btn-default action isPaused-button action-pause isPaused-button"><i class="fa fa-pause"></i> <span class="hidden-xs"> <?php echo _("Pause") ?></span></button>
			</div>
		</div>
	</li>
</ul>
<div id="createFeedContent" class="tab-content padding-10">
	<br>
	<div class="tab-pane fade in active" id="live-feeds-tab">
		<div class="row">
			<?php if($type == 'print'): ?>
			<div class="col-sm-6">
				
				<div class="row">
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-6">
						<button type="button" class="btn btn-default btn-block dropdown-toggle" data-toggle="dropdown">
							<span><i class="fab-lg fab-fw icon-fab-term"></i> <span class="hidden-xs"><?php echo _("Nozzle"); ?></span> <span class="hidden-md hidden-sm hidden-lg font-md">N</span> <span class="extruder-temp"></span> / <span class="extruder-target"></span> <span class="pull-right"><i class="fa fa-caret-down"></i></span></span>
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
							<span><i class="fab-lg fab-fw icon-fab-term"></i> <span class="hidden-xs"><?php echo _("Bed"); ?></span> <span class="hidden-md hidden-sm hidden-lg font-md">B</span> <span class="bed-temp"></span> / <span class="bed-target"></span><span class="pull-right"><i class="fa fa-caret-down"></i></span></span>
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

				<div class="row">
					<div id="temperatures-chart" class="chart"> </div>
				</div>
			</div>
			<?php endif; ?>
			<div class="col-sm-<?php echo $type == 'print' ? '6' : '12' ?> show-stats">
				<div class="row ">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"> <span class="text"> <i class="fa fa-cube"></i> <?php echo _("File"); ?> <span class="pull-right"><span class="task-file-name"> (<?php echo _("Loading");?> ..)</span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					<?php if( $type =="print"): ?>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 layer-info hidden"> <span class="text"> <i class="fa fa-database"></i> <?php echo _("Layer"); ?> <span class="pull-right"><span title="<?php echo _("Current layer");?>" class="task-layer-current"></span> <?php echo _("of")?> <span title="<?php echo _("Total layers");?>" class="task-layer-total"></span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					<?php endif; ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> <?php echo _("Progress"); ?> <span class="pull-right task-progress"></span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-progress-bar"></div>
						</div> </div>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> <?php echo _("Speed"); ?> <span class="pull-right"><span class="task-speed"></span> / 500 %</span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-speed-bar"></div>
						</div> </div>
					<?php if($type == 'print'): ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> <?php echo _("Flow rate"); ?><span class="pull-right"><span class="task-flow-rate"></span> / 500 %</span></span></span>
						<div class="progress">
							<div class="progress-bar" id="task-flow-rate-bar"></div>
						</div> </div>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> <?php echo _("Fan"); ?> <span class="pull-right"><span class="task-fan"></span> %</span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-fan-bar"></div>
						</div> </div>
					<?php endif; ?>
					<?php if($type == 'mill' || $type == 'laser'): ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"><?php echo $rpm_label; ?> <span class="pull-right"><span class="task-rpm"></span> </span> </span>
						<div class="progress">
							<div class="progress-bar" id="task-rpm-bar"></div>
						</div> </div>
					<?php endif; ?>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> <?php echo _("Elapsed time"); ?> <span class="pull-right"><span class="elapsed-time"></span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> <span class="text"> <?php echo _("Estimated time left"); ?> <span class="pull-right"><span class="estimated-time-left"></span> </span> </span>
						<div class="fake-progress"></div>
					</div>
					<span class="show-stat-buttons"> 
						<span class="col-xs-<?php echo $stats_button_size; ?> col-sm-<?php echo $stats_button_size; ?> col-md-<?php echo $stats_button_size; ?> col-lg-<?php echo $stats_button_size; ?>"> 
							<button type="button" data-action="abort"  class="btn btn-default btn-block  action"><i class="fa fa-stop"></i> <span class="hidden-xs"><?php echo _("Abort"); ?></span> </button> 
						</span> 
						<span class="col-xs-<?php echo $stats_button_size; ?> col-sm-<?php echo $stats_button_size; ?> col-md-<?php echo $stats_button_size; ?> col-lg-<?php echo $stats_button_size; ?>"> 
							<button type="button" data-action="pause"  class="btn btn-default btn-block  action isPaused-button action-pause"><i class="fa fa-pause"></i> <span class="hidden-xs"><?php echo _("Pause"); ?></span> </button> 
						</span>
						<?php if($type=="print"):?>
						<span class="col-xs-<?php echo $stats_button_size; ?> col-sm-<?php echo $stats_button_size; ?> col-md-<?php echo $stats_button_size; ?> col-lg-<?php echo $stats_button_size; ?>"> 
							<button type="button" class="btn btn-default btn-block change-filament-button"><i class="fa fa-circle-o-notch"></i> <span class="hidden-xs hidden-sm"><?php echo _("Change filament"); ?></span> </button> 
						</span>
						<?php endif;?> 
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
		<?php if($type == 'print'): ?>
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
			<div class="col-sm-<?php echo $type == 'print' ?  4 : 6 ?> margin-bottom-50">
				<h4><?php echo _("Speed"); ?> <span class="pull-right"><strong><span class="slider-task-speed"></span></strong>  %</span></h4>
				<div id="create-speed-slider" class="noUiSlider sliders"></div>
			</div>
			<?php if($type == 'print'): ?>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-4 margin-bottom-50">
				<h4><?php echo _("Flow rate"); ?> <span class="pull-right"><strong><span class="slider-task-flow-rate"></span></strong> %</span></h4>
				<div id="create-flow-rate-slider" class="noUiSlider sliders"></div>
			</div>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-4 margin-bottom-50">
				<h4><?php echo _("Fan"); ?> <span class="pull-right"><strong><span class="slider-task-fan"></span></strong> %</span></h4>
				<div id="create-fan-slider" class="noUiSlider sliders"></div>
			</div>
			<?php endif;?>
			<?php if($type == 'mill' || $type == 'laser'):?>
			<hr class="simple hidden-md hidden-sm hidden-lg">
			<div class="col-sm-6 margin-bottom-50">
				<h4><?php echo $rpm_label; ?> <span class="pull-right"><strong><span class="slider-task-rpm"></span></strong> </span></h4>
				<div id="create-rpm-slider" class="noUiSlider sliders"></div>
			</div>
			<?php endif;?>
		</div>
	</div>
</div>
<?php if($type=="print"):?>
<div class="modal fade" id="filament-change-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
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
				<div class="row">
					<div id="filament-load-description"   class="col-sm-12  filament-action-descritpion hidden">
						<div class="well well-sm well-ligth">
							<p>Load instructions</p>
						</div>
					</div>
					<div id="filament-unload-description" class="col-sm-12 filament-action-descritpion hidden">
						<div class="well well-sm well-ligth">
							<p>Unload instructions</p>
						</div>
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