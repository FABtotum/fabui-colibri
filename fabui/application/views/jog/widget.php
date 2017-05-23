<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<!-- temperatures sliders row -->
<div class="row">
</div>
<!-- jog controls row -->
<div class="row">
	<!-- xyz steps & feedrates -->
	<div class="col-sm-4 text-center">
		
		<div class="jog-container"></div>
		
		<div class="smart-form">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">XY <?php echo _("Step"); ?> (mm)</label>
						<label class="input">
							<input type="number" min="0.1" step="0.1" value="10" id="xyStep">
						</label>
					</section>
					<section class="col col-6">
						<label class="label">Z <?php echo _("Step"); ?> (mm)</label>
						<label class="input">
							<input type="number" min="0.1" step="0.1" value="5" id="zStep">
						</label>
					</section>
				</div>
				<section>
					<label class="label">XYZ <?php echo _("Feedrate"); ?> (mm/s)</label>
					<label class="input">
						<input type="number" min="1" value="1000" id="xyzFeed">
					</label>
				</section>
			</fieldset>
		</div>
	</div>
	<!-- directions -->
	<div class="col-sm-4 text-center">

		<div class="touch-container">
			<!--img class="touch-container" src="/assets/plugin/fab_laser/img/hybrid_bed_v2_small.jpg" style="opacity: 0.5; filter: alpha(opacity=50);"-->
			<img class="bed-image" src="/assets/img/std/hybrid_bed_v2_small.jpg" >
			
			<div class="button_container">
				<button class="btn btn-primary touch-home-xy" data-rel="tooltip" title="<?php echo _("Before using the touch interface you need to home XY axis first.<br><br>Make sure that the head will not hit anything during homing.");?>" data-container="body" data-html="true">
					<?php echo _("Home XY");?>
				</button>
			</div>
		</div>

	</div> 
	<div class="col-sm-4">
		
		<ul id="myTab3" class="nav nav-tabs tabs-pull-right ">
			<li class="pull-right">
				<a href="#fourthaxis-tab" data-toggle="tab"><?php echo _("4th axis"); ?></a>
			</li>
			<li class="pull-right">
				<a href="#extruder-tab" data-toggle="tab"><?php echo _("Nozzle"); ?></a>
			</li>
			<li class="active pull-right">
				<a href="#functions-tab" data-toggle="tab"><?php echo _("Head/bed"); ?></a>
			</li>
		</ul>
		
		<div id="myTabContent3" class="tab-content">
			<div class="tab-pane fade in" id="extruder-tab">
				<div class="smart-form">
					<fieldset>
						<div class="row">
							<section class="col col-6">
								<button class="btn btn-default btn-block extrude" data-attribute-type="+" style="padding:6px 10px 5px"><i class="fa fa-plus"></i></button>
							</section>
							<section class="col col-6">
								<button class="btn btn-default btn-block extrude" data-attribute-type="-" style="padding:6px 10px 5px"><i class="fa fa-minus"></i></button>
							</section>
						</div>
						<div class="row">
							<section class="col col-6">
								<label class="label"><?php echo _("Step"); ?> (mm)</label>
								<label class="input">
									<input type="number" value="10" min="1" id="extruderStep">
								</label>
							</section>
							<section class="col col-6">
								<label class="label"><?php echo _("Feedrate"); ?></label>
								<label class="input">
									<input type="number" value="300" min="100" id="extruder-feedrate">
								</label>
							</section>
						</div>
						<section>
							<label class="label"><?php echo _("Cold extrusion safety"); ?></label>
						</section>
						<div class="row">
							<section class="col col-6">	
								<button class="btn btn-primary btn-block cold-extrusion" data-attribute="on" style="padding:6px 10px 5px"><?php echo _("Turn on"); ?></button>
							</section>
							<section class="col col-6">
								<button class="btn btn-primary btn-block cold-extrusion" data-attribute="off" style="padding:6px 10px 5px"><?php echo _("Turn off"); ?></button>
							</section>
						</div>
					</fieldset>
				</div>
			</div>
			<div class="tab-pane fade in" id="fourthaxis-tab">
				<div class="knobs-container text-center" id="mode-a">
					<input value="0" class="knob" data-displayPrevious="true" data-width="170" data-height="170" data-cursor="true" data-step="0.5" data-min="0" data-max="360" data-thickness=".3" data-fgColor="#A0CFEC" data-displayInput="true">
				</div>
				<div class="smart-form" style="margin-top:-10px;">
					<fieldset>
						<section>
							<label class="label"><?php echo _("Feedrate"); ?></label>
							<label class="input">
								<input type="number" min="1" value="800" id="4thaxis-feedrate">
							</label>
						</section>
						<section>
							<label class="label"><?php echo _("Cold extrusion safety"); ?></label>
						</section>
						<div class="row">
							<section class="col col-6">
								<button class="btn btn-primary btn-block cold-extrusion" data-attribute="on" style="padding:6px 10px 5px"><?php echo _("Turn on"); ?></button>
							</section>
							<section class="col col-6">
								<button class="btn btn-primary btn-block cold-extrusion" data-attribute="off" style="padding:6px 10px 5px"><?php echo _("Turn off"); ?></button>
							</section>
						</div>
					</fieldset>
				</div>
			</div>
			
			<div class="tab-pane fade in active" id="functions-tab">
				<div class="padding-10"></div>
				
				<?php if($headPrintSupport): ?>
				<div class="col-sm-12 margin-bottom-50">
					<h4><i class="icon-fab-term"></i> <span><?php echo _("Nozzle"); ?></span> <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="<?php echo _("Extruder current temperature");?>"  class="extruder-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="<?php echo _("Extruder target temperature");?>" class="slider-extruder-target">0</span></strong> &deg;C</span></h4>
					<div id="create-ext-target-slider" class="noUiSlider sliders"></div>
				</div>
				<?php endif; ?>
				
				<?php if($headFanSupport): ?>
				<div class="col-sm-12 margin-bottom-50">	
					<h4><?php echo _("Fan"); ?> <span class="pull-right"><strong><span class="slider-task-fan">0</span></strong>%</span></h4>
					<div id="create-fan-slider" class="noUiSlider sliders"></div>
					<!-- <div class="feature-warning"><h4><i class="fa fa-ban" aria-hidden="true"></i> <?php echo _("Head does not have a fan"); ?> </h4></div> -->
				</div>
				<?php endif; ?>
				
				<?php if($headMillSupport): ?>
				<div class="col-sm-12 margin-bottom-50">
					<h4><?php echo _("RPM"); ?> <span class="pull-right"><strong><span class="slider-task-rpm"><?php echo _("Off"); ?></span></strong></span></h4>
					<div id="create-rpm-slider" class="noUiSlider sliders"></div>
					<!--  <div class="feature-warning"><h4><i class="fa fa-ban" aria-hidden="true"></i> <?php echo _("Head does not have a milling motor"); ?></h4></div>-->
				</div>
				<?php endif; ?>
				
				<div class="col-sm-12 margin-bottom-50">
					<?php if($haveBed): ?>
					<h4><i class="icon-fab-term"></i> <?php echo _("Bed"); ?> <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="<?php echo _("Bed current temperature");?>" class="bed-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="<?php echo _("Bed target temperature");?>" class="slider-bed-target">0</span></strong> &deg;C</span></h4>
					<div id="create-bed-target-slider" class="noUiSlider sliders"></div>
					<?php else: ?>
					<div class="feature-warning"><h4><i class="fa fa-ban" aria-hidden="true"></i> <?php echo _("Bed is not installed"); ?></h4></div>
					<?php endif; ?>
				</div>
			</div>
			
		</div>
	</div>
</div>
<hr class="simple">
<!-- mdi & console -->
<div class="row">
	<div class="col-sm-6">
		<div class="chat-footer">
			<div class="textarea-div">
				<div class="typearea">
					<textarea placeholder=">_ <?php echo _("Write command");?>"  id="mdiCommands" class="custom-scroll" rows="10"></textarea>
				</div>
			</div>
			<!-- CHAT REPLY/SEND -->
			<span class="textarea-controls">
				<button class="btn btn-sm btn-primary pull-right" type="button" id="mdiButton"><?php echo _("Send"); ?></button> 
				<span class="pull-right smart-form" style="margin-top: 3px; margin-right: 10px;"> <label class="checkbox pull-right">
					<input type="checkbox" name="enterSend" id="enterSend" checked="checked">
					<i></i><?php echo _("Press <strong>Enter</strong> to"); ?></label> </span> 
					<a href="#" rel="tooltip" title="<?php echo _("Help");?>" data-toggle="modal" data-target="#gcodeHelp" class="pull-left btn btn-primary btn-circle"><i class="fa fa-question" aria-hidden="true"></i> </a> 
			</span>
		</div>
	</div>
	<div class="col-sm-6">
		<div class="chat-footer">
			<div class="textarea-div">
				<div class="typearea jogResponseContainer2">
					<div class="consoleContainer custom-scroll"></div>
				</div>
			</div>
			<!-- CHAT REPLY/SEND -->
			<span class="textarea-controls">
				<button class="btn btn-sm btn-primary pull-right" type="button" id="clearButton"><?php echo _("Clear"); ?></button> 
			</span>
		</div>
	</div>
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="gcodeHelp">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _("Supported GCodes"); ?></h4>
      </div>
     	<div class="modal-body no-padding">
     		<div class="row margin-bottom-10 margin-top-10">
				<div class="col-sm-12" style="padding-left:20px;padding-right:20px;">
					<div class="input-group">
						<input class="form-control" type="text" id="fa-icon-search" placeholder="<?php echo  _("Search for a code");?>..." >
						<div class="input-group-btn">
							<button type="button" class="btn btn-default" tabindex="-1"><i class="fa fa-fw  fa-search"></i></button>
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1" aria-expanded="false">
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li><a class="filter-select" data-attr="gcode"><?php echo _("GCode"); ?></a></li>
								<li><a class="filter-select" data-attr="desc"><?php echo _("Description"); ?></a></li>
								<li class="divider"></li>
								<li><a href="javascript:void(0);"><?php echo _("Cancel")?></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
     	
     		<div class="row">
     			<div class="col-sm-12">
     				<div style="overflow: auto; height: 300px;padding:1px;">
			     		<div class="panel-group smart-accordion-default" id="accordion">
			     			<?php foreach($gcodes as $code => $info): ?>
			     			<div class="panel panel-default code" data-attr="<?php echo $code; ?>">
			     				<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $code?>" class="no-ajax collapsed">
										<i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> 
										<strong><?php echo $code?></strong> 
										<?php echo isset($info['desc']) ? '<span style="margin-left: 10px">'.word_limiter($info['desc'], 10, ' ...').'</span>' : ''; ?> </a>
									</h4>
								</div>
								<div id="collapse<?php echo $code?>" class="panel-collapse collapse">
									<div class="panel-body">
										<?php if(isset($info['desc'])): ?>
										<p class="description" id="<?php echo $code; ?>-desc"><?php echo $info['desc'] ?></p>
										<?php endif; ?>
										<?php if(isset($info['params'])): ?>
										<p><b><?php echo _("Parameters") ?></b></p>
										<ul class="list-unstyled">
										<?php foreach($info['params'] as $p => $details): ?>
											<li>
												<code><?php echo $p;?></code>
												<?php if(isset($details['type'])): ?>  <small>(<?php echo $details['type']?>)</small> <?php endif;?>
												<?php if(isset($details['unit'])): ?>  <small>(<?php echo $details['unit']?>)</small> <?php endif;?>
												<?php if(isset($details['range'])): ?> <small>[<?php echo $details['range'][0]." .. ".$details['range'][1];?>]</small> <?php endif;?>
												<span><?php echo $details['desc'] ?></span>
											</li>
										<?php endforeach; ?>
										</ul>
										<?php endif; ?>
									</div>
								</div>
			     			</div>
			     			<?php endforeach; ?>
			     		</div>
		     		</div>
	     		</div>
   			</div>
			<!-- 
			<div class="row" style="padding-left:5px;">
				<div class="col-sm-12">
					<div style="overflow: auto; height: 300px;padding:1px;">
						<table class="table table-hover">
							<thead>
								<tr>
									<th><?php echo _("Code"); ?></th>
									<th><?php echo _("Description"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($gcodes as $code => $info): ?>
									<?php if( isset($info['unused']) ) continue; ?>
									<?php if( array_key_exists('ref',$info) ) { 
											$desc = $info['desc'];
											$ref  = $info['ref'];
											$info = $gcodes[$ref]; 
											$info['desc'] = $desc;
										} ?>
									
									<tr data-toggle="collapse" data-target="#<?php echo $code; ?>-extra" class="clickable code" data-attr="<?php echo $code; ?>">
										<td width="150px;"><strong><?php echo $code; ?></strong></td>
										<td><p class="description" id="<?php echo $code; ?>-desc"><?php echo $info['desc']; ?></p></td>
									</tr>
									<tr id="<?php echo $code; ?>-extra" class="collapse"  data-attr="<?php echo $code; ?>">
										<td colspan="2" class="code-extra">
											<?php if( array_key_exists('params',$info) ): ?>
											<div class="row">
												<div class="col-sm-1 help-param-header"><?php echo _("Param"); ?></div>
												<div class="col-sm-7 help-param-header"><?php echo _("Desc"); ?></div>
												<div class="col-sm-1 help-param-header"><?php echo _("Type"); ?></div>
												<div class="col-sm-1 help-param-header"><?php echo _("Unit"); ?></div>
												<div class="col-sm-2 help-param-header"><?php echo _("Range"); ?></div>
											</div>
											
												<?php foreach($info['params'] as $param => $info): ?>
												<div class="row">
													<div class="col-sm-1 code-extra-content"><?php echo $param;?></div>
													<div class="col-sm-7"><?php echo $info['desc'];?></div>
													
													<div class="col-sm-1"><?php echo $info['type'];?></div>
													<?php if(isset($info['unit'])): ?>
														<div class="col-sm-1"><?php echo $info['unit'];?></div>
													<?php else: ?>
														<div class="col-sm-1">&nbsp;</div>
													<?php endif; ?>
													
													<?php if( isset($info['range']) ): ?>
														<div class="col-sm-2"> <?php echo $info['range'][0]." .. ".$info['range'][1];?></div>
													<?php endif; ?>
												</div>
												
												<?php endforeach; ?>
											<?php else: ?>
												<span class="description"><?php echo _("No parameters"); ?>.</span>
											<?php endif; ?>
	
												<?php if( isset($info['reply']) ): ?>
												<div class="row">
													<div class="col-sm-12 help-param-header"><?php echo _("Reply"); ?></div>
												</div>
												<div class="row">
													<div class="col-sm-12 code-extra-content"><?php echo $info['reply'];?> </div>
												</div>
												<?php endif;?>
											</div>
										</td>
									</tr>
	
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div> -->	
			</div>
      		<div class="modal-footer">
        		<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close"); ?></button>
      		</div>
    	</div><!-- /.modal-content -->
  	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
