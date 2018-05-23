<?php
/**
 * 
 * @author FABteam
 * @version 0.0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<!-- CAM APPLICATIONS TAB -->
<div class="tab-pane fade in active" id="cam-apps-tab">
	
	<div id="cam-apps-view">
	</div>
	
	<div id="cam-settings-view" class="hidden">
		<div class="row">
			<!-- PREVIEW -->
			<div class="col-sm-4 text-center" id="cam-preview-container">
				<div class="row margin-bottom-10">
					<div class="col-sm-12">
						<button id="upload-new-file" class="btn btn-default pull-left"><i class="fa fa-plus"></i> Upload new file</button>
					</div>
				</div>
				
				<!-- SOURCE/PREVIEW IMAGES -->
				<div class="row  margin-bottom-10">
					<div class="col-sm-12">
						<div class="owl-carousel owl-theme owl-loaded owl-drag" id="cam-preview-carousel" style="">
							
							<div class="owl-stage-outer">
								<div class="owl-stage" style="transform: translate3d(-367px, 0px, 0px); transition: 0.25s; width: 734px;">
									<div class="owl-item" style="width: 357px; margin-right: 10px;">
										<div class="well well-light">
											<div>
												<img class="img-responsive" id="cam-image-source">
											</div>
										</div>
									</div>
									
									<div class="owl-item active" style="width: 357px; margin-right: 10px;">
										<div class="well well-light" style="overflow: auto; max-height: 550px;">
											<div style="min-height: 260.984px;">
												<img class="img-responsive laser-preview-source" id="cam-preview-source">
												<span id="no-gcode-alert" class="font-md" style="top: 130.492px;">Click on "Generate GCode" to show preview</span>
											</div>
											<div id="engraving-note" class="margin-bottom-10 hidden">
												<span class="note pull-left">Note: black is being burned by the laser</span>
											</div>
										</div>
									</div>
								</div><!-- div class="owl-stage" ... -->
						
							</div><!--div class="owl-stage-outer"-->
					
							<!--div class="owl-nav">
								<div class="owl-prev">Source image</div>
								<div class="owl-next disabled">CAM preview</div>
							</div-->
							
							<div class="owl-dots disabled"></div>
						
						</div>
						
					</div><!--div class="col-sm-12"-->
				</div><!--div class="row  margin-bottom-10"-->
			</div>
			
			<!-- SETTINGS -->
			<div class="col-sm-8 text-center">
				
				<!-- TOOLBAR -->
				<div class="row margin-bottom-10">
					<div class="col-sm-6 col-xs-6">
						<div class="smart-form">
							<label class="select">
								<select name="profile" id="cam-profile">
								</select>
								<i></i>
							</label>
						</div>
					</div>
					
					<div class="col-sm-3 col-xs-3">
						<button type="button" data-action="generate-gcode" data-type="laser" id="cam-generate-gcode" class="btn btn-primary btn-block action-button"><i class="fa fa-cog"></i> Generate GCode</button>
					</div>
					
					<div class="col-sm-3  col-xs-3">
						<div class="row">
							<div class="col-sm-12 text-right">
								<!--span class="laser-status"> Waiting for the GCode </span-->
								<a href="javascript:void(0);" title="Save GCode" class="btn btn-default action-button disabled" data-action="open-save-modal" id="cam-save-gcode"><i class="fa fa-save"></i></a>
								<a href="javascript:void(0);" title="Engrave" class="btn btn-default action-button disabled" data-action="engrave-gcode" id="cam-make-gcode"><i class="fa icon-communication-143"></i></a>
								<a href="javascript:void(0);" title="Download GCode" class="btn btn-default action-button disabled" data-action="download-gcode" id="cam-download-gcode"><i class="fa fa-download"></i></a>
							</div>
						</div>
					</div>
				</div>
				<!-- END TOOLBAR -->
				
				<!-- CONFIG TABS -->
				<form id="cam-config-form">
					<div class="row" id="cam-app-config-view">
						loading...
					</div>
				</form>
				<!-- END CONFIG TABS -->
				
			</div><!--div class="col-sm-8 text-center"-->
			
		</div>
	</div>
	
	<div id="cam-dropzone-view" class="hidden">
		
		<div class="row margin-bottom-10">
			<div class="col-sm-12">
				<button id="back-to-apps" class="btn btn-default pull-left"><i class="fa fa-arrow-left"></i> Back</button>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12">
				<div id="cam-dropzone" class="dropzone"></div>
				<span id="note-dropzone-maxsize" class="pull-left margin-top-10"><?php echo str_replace("{0}", ($max_upload_file_size/1024)." MB", _("Note: max file size is {0}"));?></span>
				<button class="btn btn-primary pull-right margin-top-10 action-button" data-action="upload" data-type="laser" id="cam-upload"><i class="fa fa-upload"></i> <?php echo _("Upload");?></button>
			</div>
		</div>
	</div>
	
</div>
<!-- CAM APPLICATIONS TAB -->
