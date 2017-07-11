<?php
/**
 * 
 * @author Krios Mane
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<div class="modal fade" id="dropzone-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fa fa-cube"></i> <span class="dropzone-file-name"></span></h4>
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
				<?php if($type == 'print'):?>
					<div class="row">
						<div class="col-sm-6">
							<div class="well well-sm well-light text-center">
								<label><?php echo _('Simple homing'); ?></label>
								<div class="radio">
									<label>
										<input type="radio" value="home_all" class="radiobox" <?php echo $this->session->settings["print"]["calibration"] == 'homing' ? 'checked="checked"' : '' ?>  name="dropzone-calibration">
											<span></span> 
									</label>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="well well-sm well-light text-center">
								<label><?php echo _('Auto bed leveling'); ?></label>
								<div class="radio">
									<label>
										<input value="auto_bed_leveling" type="radio" class="radiobox" <?php echo $this->session->settings["print"]["calibration"] == 'auto_bed_leveling' ? 'checked="checked"' : '' ?> name="dropzone-calibration">
										<span></span> 
									</label>
								</div>
							</div>
						</div>
					</div>
				<?php endif;?>
			</div>
			<div class="modal-footer">
				<button type="button" id="dropzone-cancel" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> <?php echo _("Cancel");?></button>
				<button type="button" id="dropzone-make"   class="btn btn-primary"><?php echo _("Wait");?>...</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->