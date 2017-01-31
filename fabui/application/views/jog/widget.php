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
						<label class="label">XY Step</label>
						<label class="input">
							<input type="number" min="1" value="1" id="xyStep">
						</label>
					</section>
					<section class="col col-6">
						<label class="label">Z Step (mm)</label>
						<label class="input">
							<input type="number" min="1" value="0.5" id="zStep">
						</label>
					</section>
				</div>
				<section>
					<label class="label">XYZ Feedrate</label>
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
				<button class="btn btn-primary touch-home-xy" data-rel="tooltip" title="Before using the touch interface you need to home XY axis first.<br><br>Make sure that the head will not hit anything during homing." data-container="body" data-html="true">
					Home XY
				</button>
			</div>
		</div>

	</div> 
	<div class="col-sm-4">
		
		<ul id="myTab3" class="nav nav-tabs tabs-pull-right ">
			<li class="pull-right">
				<a href="#fourthaxis-tab" data-toggle="tab">4th axis</a>
			</li>
			<li class="pull-right">
				<a href="#extruder-tab" data-toggle="tab">Extruder</a>
			</li>
			<li class="active pull-right">
				<a href="#functions-tab" data-toggle="tab">Head/Bed</a>
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
								<label class="label">Step (mm)</label>
								<label class="input">
									<input type="number" value="10" min="1" id="extruderStep">
								</label>
							</section>
							<section class="col col-6">
								<label class="label">Feedrate</label>
								<label class="input">
									<input type="number" value="300" min="100" id="extruder-feedrate">
								</label>
							</section>
						</div>
						<hr class="simple">
						<div class="row">
							<section class="col col-6">
								<label class="label">Cold Extrusion</label>
								<button class="btn btn-primary btn-block cold-extrusion" data-attribute="on" style="padding:6px 10px 5px">Turn ON</button>
							</section>
							<section class="col col-6">
								<label class="label">&nbsp;</label>
								<button class="btn btn-primary btn-block cold-extrusion" data-attribute="off" style="padding:6px 10px 5px">Turn OFF</button>
							</section>
						</div>
					</fieldset>
				</div>
			</div>
			
			<div class="tab-pane fade in" id="fourthaxis-tab">
				<div class="knobs-container text-center" id="mode-a">
					<input value="0" class="knob" data-displayPrevious="true" data-width="230" data-height="230" data-cursor="true" data-step="0.5" data-min="0" data-max="360" data-thickness=".3" data-fgColor="#A0CFEC" data-displayInput="true">
				</div>
				<div class="smart-form">
					<fieldset>
						<section>
							<label class="label">4th axis Feedrate</label>
							<label class="input">
								<input type="number" min="1" value="800" id="4thaxis-feedrate">
							</label>
						</section>
					</fieldset>
				</div>
			</div>
			
			<div class="tab-pane fade in active" id="functions-tab">
				<div class="padding-10"></div>
				<div class="col-sm-12 margin-bottom-50">
					<h4><i class="icon-fab-term"></i> <span>Nozzle</span> <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="Extruder current temperature"  class="extruder-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="Extruder target temperature" class="slider-extruder-target"></span></strong> &deg;C</span></h4>
					<div id="create-ext-target-slider" class="noUiSlider sliders"></div>
				</div>
				
				<div class="col-sm-12 margin-bottom-50">
					<h4><i class="icon-fab-term"></i> Bed <span class="pull-right"><span rel="tooltip" data-placement="top" data-original-title="Bed current temperature" class="bed-temp"></span> / <strong><span rel="tooltip" data-placement="top" data-original-title="Bed target temperature" class="slider-bed-target"></span></strong> &deg;C</span></h4>
					<div id="create-bed-target-slider" class="noUiSlider sliders"></div>
				</div>
					
				<div class="col-sm-12 margin-bottom-50">
					<h4>Fan <span class="pull-right"><strong><span class="slider-task-fan"></span></strong> %</span></h4>
					<div id="create-fan-slider" class="noUiSlider sliders"></div>
				</div>
				
				<div class="col-sm-12 margin-bottom-50">
					<h4>RPM <span class="pull-right"><strong><span class="slider-task-fan"></span></strong> %</span></h4>
					<div id="create-rpm-slider" class="noUiSlider sliders"></div>
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
					<textarea placeholder=">_ Write command"  id="mdiCommands" class="custom-scroll" rows="10"></textarea>
				</div>
			</div>
			<!-- CHAT REPLY/SEND -->
			<span class="textarea-controls">
				<button class="btn btn-sm btn-primary pull-right" type="button" id="mdiButton">Send</button> 
				<span class="pull-right smart-form" style="margin-top: 3px; margin-right: 10px;"> <label class="checkbox pull-right">
					<input type="checkbox" name="enterSend" id="enterSend" checked="checked">
					<i></i>Press <strong> ENTER </strong> to </label> </span> 
					<a href="#" data-toggle="modal" data-target="#gcodeHelp" class="pull-left btn btn-primary btn-circle"><i class="fa fa-question" aria-hidden="true"></i> </a> 
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
				<button class="btn btn-sm btn-primary pull-right" type="button" id="clearButton">Clear</button> 
			</span>
		</div>
	</div>
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="gcodeHelp">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Supported GCodes</h4>
      </div>
      
      <div class="modal-body">

		<div class="row">
			<div class="col-sm-12">
				<div class="well well-sm">

				   <div class="input-group">
					  <input class="form-control " type="text" id="fa-icon-search" placeholder="Search for a code..." >
					  <div class="input-group-btn">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-fw  fa-search"></i><span class="caret"></span></button>
						<ul class="dropdown-menu dropdown-menu-right">
						  <li><a class="filter-select" data-attr="gcode">GCode</a></li>
						  <li><a class="filter-select" data-attr="desc">Description</a></li>
						</ul>
					  </div><!-- /btn-group -->
					</div><!-- /input-group -->
					
					
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="well well-sm no-padding" style="overflow: auto; height: 300px;">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Code</th>
								<th>Description</th>
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
											<div class="col-sm-1 help-param-header">Param</div>
											<div class="col-sm-7 help-param-header">Desc</div>
											<div class="col-sm-1 help-param-header">Type</div>
											<div class="col-sm-1 help-param-header">Unit</div>
											<div class="col-sm-2 help-param-header">Range</div>
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
											<span class="description">No parameters.</span>
										<?php endif; ?>
										<!--
										<div class="row">
											<div class="col-sm-12 help-param-header">Reply</div>
										</div>
										<div class="row">
											<?php if( isset($info['reply']) ): ?>
											<div class="col-sm-12 code-extra-content"><?php echo $info['reply'];?> </div>
											<?php else:?>
											<div class="col-sm-12 code-extra-content">ok</div>
											<?php endif;?>
										</div>
										-->
									</td>
								</tr>

							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>	
		</div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
