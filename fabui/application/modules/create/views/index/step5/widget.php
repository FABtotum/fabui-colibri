<ul id="myTab1" class="nav nav-tabs bordered">
	<li class="active">
		<a href="#live-feeds" data-toggle="tab" aria-expanded="true"> <i class="fa fa-fw fa-lg fa-bar-chart"></i> Live Feeds</a>
	</li>
	<li class="disabled controls-tab">
		<a href="#controls" data-toggle=""  aria-expanded="false"><i class="fa fa-fw fa-lg fa-sliders"></i> Controls</a>
	</li>
	
	<li class="pull-right">
		<a href="javascript:void(0);" data-action="stop" id="stop-button" class="stop txt-color-red"> <i class="fa fa-fw fa-lg fa-times-circle"></i> Cancel <?php echo $label ?></a>
	</li>
	<li class="pull-right">
		<span style="position:relative; top:3px;" rel="tooltip" title="Send a notification mail at the end of the print" data-placement="left">Mail</span>
		<span class="onoffswitch" style="padding-top:9px; position:relative;">
			<input data-action="mail" type="checkbox" name="mail" class="onoffswitch-checkbox controls" id="mail">
			<label class="onoffswitch-label" for="mail"> 
				<span class="onoffswitch-inner"  data-swchon-text="YES" data-swchoff-text="NO"></span> 
				<span class="onoffswitch-switch" style="margin-top:8px;"></span> 
			</label> 
		</span>

	</li>
	
</ul>
<div id="myTabContent1" class="tab-content padding-10">

	<div class="tab-pane fade active in" id="live-feeds">

		<div class="row padding-10">
			<div class="col-sm-4 stats-well">
				<p><i class="fa fa-file-o"></i> File <i><span class="pull-right file_name font-md"></span></i></p>
				<p><i class="fa fa-folder-open"></i> Object <i><span class="pull-right object_name "></span></i></p>
				
				<hr class="simple">
				<!-- PROGRESS -->
				<p>Progress  <span class="pull-right"><span class="hidden layers" style="margin-left:10px;"> (layer: <span class="layer-actual font-md"></span> / <span class="layer-total"></span> )</span></span> <span class="pull-right progress-status font-md"></span></p>
				<div class="progress progress-sm progress-striped active">
					<div id="lines-progress" class="progress-bar bg-color-blue" style="width: 0%;"></div>
				</div>
				
				<p>Speed <span class="pull-right"><span class="label-velocity font-md"></span> / 500%</span> </p>
				<div class="progress progress-xs">
					<div class="progress-bar  bg-color-blue speed-progress" style="width:"></div>
				</div>
				
				<?php if($type == 'additive'): ?>
					<p class="additive-print">Flow rate<span class="pull-right"> <span class="label-flow-rate font-md"></span> / <span>500 %</span>  </span></p>
					<div class="progress progress-xs additive-print">
						<div class="progress-bar  bg-color-blue flow-rate-progress" style="width: "></div>
					</div>
					<p class="additive-print">Fan <span class="pull-right"> <span class="label-fan font-md"></span>  </span></p>
					<div class="progress progress-xs additive-print">
						<div class="progress-xs">
							<div class="progress-bar bg-color-blue fan-progress" style="width:"></div>
						</div>
					</div>
				<?php endif; ?>
				
				<?php if($type == 'subtractive'): ?>
					<p class="subtractive-print">RPM <span class="pull-right"><span class="label-rpm font-md"></span><span> / 14000</span></span></p>
					<div class="progress progress-xs subtractive-print">
						<div class="progress-bar  bg-color-blue rpm-progress" style="width:"></div>
					</div>
				<?php endif; ?>
				
				<hr class="simple">
				<p>Elapsed Time <span class="pull-right"> <span class="elapsed-time"></span> </span> </p>
				<p>Esitmated time left <span class="pull-right"> <span class="estimated-time-left"></span> </span> </p>
			</div>
			<?php if($type == 'additive'): ?>
				<div class="col-sm-4 additive-print">
					<h5 class="text-center"><i class="fab-lg fab-fw icon-fab-term "></i> Nozzle (<span class="nozzle-temperature"></span> / <span class="nozzle-target"></span> &deg;C)</h5>
					<div id="nozzle-chart" class="chart"> </div>
				</div>
				
				<div class="col-sm-4 additive-print">
					<h5 class="text-center"><i class="fab-lg fab-fw icon-fab-term "></i> Bed (<span class="bed-temperature"></span> / <span class="bed-target"></span> &deg;C)</h5>
					<div id="bed-chart" class="chart"> </div>
				</div>
			<?php endif; ?>
		</div>
		
		<div class="row padding-10 hidden-mobile">
			<div class="col-sm-12">
				<pre class="console " id="ace-editor" style="height: 250px;"></pre>
			</div>
		</div>
		
	</div>
	
	<!-- CONTROLS -->
	<div class="tab-pane fade" id="controls">
		<div class="row">	
			<div class="col-sm-12">
				<div class="well">
					<div class="row">
						<div class="col-sm-4">
							<div class="row">
								<div class="col-sm-3"></div>
								<div class="col-sm-6 text-center">
									<label>Change Z Height</label>
								</div>
								<div class="col-sm-3"></div>
							</div>
							
							<div class="row">
								<div class="col-sm-3 ">
									<!--<a href="javascript:void(0);" class="btn btn-default  controls" data-action="zup" title="Change Z height: + 0.1mm" rel="tooltip"><i class="fa fa-angle-double-down"></i>&nbsp;Z</a>-->
									<button data-action="zup" type="button" class="form-control btn btn-default controls"><i class="fa fa-angle-double-down"></i>&nbsp;Z</button>
								</div>
								
								<div class="col-sm-6">
									<select class="form-control text-center" id="z-height">
										<option value="0.1">0.1</option>
										<option value="0.01">0.01</option>
									</select>
									<p class="note text-center">Z Override: <span class="z_override"></span></p>
								</div>
								<div class="col-sm-3 ">
									<!--<a href="javascript:void(0);" class="btn btn-default controls" data-action="zdown" title="Change Z height: - 0.1mm" rel="tooltip"><i class="fa fa-angle-double-up"></i>&nbsp;Z</a>-->
									<button data-action="zdown" type="button" class="form-control btn btn-default controls"><i class="fa fa-angle-double-up"></i>&nbsp;Z</button>
								</div>
							</div>
						</div>
						<div class="col-sm-8">
							<div class="chat-footer">
								<!-- CHAT TEXTAREA -->
								<div class="textarea-div">
									<div class="typearea">
										<textarea placeholder="Write notes..." id="notes" class="custom-scroll" rows="10"></textarea>
									</div>
								</div>
								<span class="textarea-controls"><button data-action="notes" type="button" class="btn btn-sm btn-primary pull-right controls">Save Notes</button></span>
				
							</div>
						</div>		
					</div>
				</div>
			</div>
		</div>
		
		<?php if($type == 'additive'): ?>
			<div class="row additive-print">	
				<div class="col-sm-6">
					<div class="well">
						<span class="text"> <i class="fab-lg fab-fw icon-fab-term "></i> Nozzle
							<span class="pull-right">
								<label id="label-temp1-target" class="label label-info pull-right"></label>
								<label id="label-temp1" class="label label-danger pull-right margin-right-5"></label>  
							</span>
						</span>
						<div id="act-ext-temp" class="noUiSlider margin-top-10"></div>
						<div id="temp1" data-action="temp1" class="sliders extruder-range margin-bottom-10"></div>
						<div class="margin-top-40"></div>
					</div>
				</div>
				
				
				<div class="col-sm-6">
					<div class="well">
						<span class="text"> <i class="fab-lg fab-fw icon-fab-term "></i> Bed
							<span class="pull-right">
								<label id="label-temp2-target" class="label label-info pull-right"></label>
								<label id="label-temp2" class="label label-danger pull-right margin-right-5"></label>  
							</span>
						</span>
						<div id="act-bed-temp" class="noUiSlider margin-top-10"></div>
						<div id="temp2" data-action="temp2" class="sliders bed-range margin-bottom-10"></div>
						<div class="margin-top-40"></div> 
					</div>
				</div>
			</div>
		<?php endif; ?>
		
		<div class="row">
			<div class="col-sm-4 speed-well">
				<div class="well">
					<span class="text">Speed
						<span class="pull-right">
							<label  class="label label-warning label-velocity"></label>
						</span>
					</span>
					<div class="margin-top-10"></div>
					<div id="velocity" data-action="velocity" class="sliders speed-range margin-bottom-10"></div>
					<div class="margin-top-40"></div>
					<div class="margin-top-10"></div>
				</div>
			</div>
			
			<?php if($type == 'additive'): ?>
				<div class="col-sm-4 additive-print">
					<div class="well">
						<span>Flow rate
							<span class="pull-right">
								<label class="label label-warning label-flow-rate"></label>
							</span>
						</span>
						<div class="margin-top-10"></div>
						<div id="flow-rate" data-action="flow-rate" class="sliders flow-rate-range margin-bottom-10"></div>
						<div class="margin-top-40"></div>
					</div>
				</div>
				
				<div class="col-sm-4 additive-print">
					<div class="well">
						<span>Fan
							<span class="pull-right">
								<label  class="label label-warning label-fan"></label>
							</span>
						</span>
						<div class="margin-top-10"></div>
						<div id="fan" data-action="fan" class="sliders fan-range margin-bottom-10 bg-color-teal" ></div>
						<div class="margin-top-40"></div>
					</div>
				</div>
			<?php endif; ?>
			
			<?php if($type == 'subtractive'): ?>
				<div class="col-sm-6 rpm-well subtractive-print">
					<div class="well">
						<span>RPM
							<span class="pull-right">
								<label class="label label-warning label-rpm pull-right"></label>
							</span>	
						</span>
						<div class="margin-top-10"></div>
						<div id="rpm" data-action="rpm" class="sliders rpm-range margin-bottom-10"></div>
						<div class="margin-top-40"></div>
					</div>
				</div>
			<?php endif; ?>		
		</div>
	</div>
</div>