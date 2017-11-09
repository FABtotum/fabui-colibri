<?php
/**
 * 
 * @author FABteam
 * @version 0.0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<div class="tab-content padding-10">
	<?php echo $laser_tab; ?>
	<!--  -->
	<?php echo $printing_tab; ?>
	<!--  -->
	<?php echo $subscriptions_tab; ?>
</div>
<!-- PROGRESS MODAL -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel"><i class="fa fa-file-image-o"></i> <span class="dropzone-file-name"></span></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12 show-stats">
						<div class="row">
							<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12"> 
								<span class="text"><span class="dropzone-upload-label"></span> <span class="pull-right dropzone-file-upload-percent"></span> </span>
								<div class="progress">
									<div class="progress-bar bg-color-blueDark dropzone-progress-bar" style="width:0%"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>	
		</div>
	</div>
</div>
<!-- END PROGRESS MODAL -->
<!-- DOWNLOAD GCODE MODAL -->
<div class="modal fade" id="downloadGcodeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fa fa-save"></i> <?php echo _("Save GCode"); ?></h4>
			</div>
			<div class="modal-body no-padding">
				<div class="row">
					<div class="col-sm-12">
						<div class="smart-form">
							<fieldset>
								<section>
									<label class="select">
										<select id="project-save-mode-choose">
											<option value="new"><?php echo _("Crate new project"); ?></option>
											<option value="existing"><?php echo _("Add to existing project"); ?></option>
										</select><i></i>
									</label>
								</section>
								<section class="project-mode new-project">
									<label class="input">
										<i class="icon-prepend fa fa-cubes"></i>
										<input type="text" id="new-project-name" style="padding-left:25px !important;">
									</label>
								</section>
								<section class="project-mode existing-project">
									<label class="select">
										<select id="projects-list"></select><i></i>
									</label>
								</section>
								<section>
									<label class="input">
										<i class="icon-prepend fa fa-file-o"></i>
										<input type="text" id="new-file-name" style="padding-left:25px !important;">
									</label>
								</section>
							</fieldset>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close");?></button>
				<button type="button" class="btn btn-default action-button" data-action="save-gcode" id="save-gcode"><i class="fa fa-save"></i> <?php echo _("Save");?></button>
			</div>
		</div>
	</div>
</div>
<!-- END DOWNLOAD GCODE MODAL -->
<?php if(!$isFabid):?>
<!-- FABIDMODAL -->
<div class="modal fade" id="fabidModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fa fa-warning"></i> <?php echo _("Missing FABID"); ?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<p><?php echo _("You must be logged with your FABID account");?></p>
						<p class="margin-top-10"><a target="_blank" href="https://my.fabtotum.com/myfabtotum" class="no-ajax"><?php echo _("Need an account?"); ?></a></p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close");?></button>
				<a data-action="fabidLogin" class="btn btn-default" href="javascript:void(0);" ><i class="fa fa fa-link"></i> <?php echo _("Connect to your FABID account"); ?></a>
			</div>
		</div>
	</div>
</div>
<!-- END FABIDMOAL  -->
<?php endif;?>
<?php if(!$internet):?>
<!-- NOINTERNETDMODAL -->
<div class="modal fade" id="noInternetModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fa fa-warning"></i> <?php echo _("No internet connection found"); ?></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<p><?php echo _("FABtotum must be connected to internet in order to use CAM toolbox");?></p>
						<p><?php echo _("Check network settings and try again")?></p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Close");?></button>
			</div>
		</div>
	</div>
</div>
<!-- END NOINTERNETDMODAL  -->
<?php endif;?>