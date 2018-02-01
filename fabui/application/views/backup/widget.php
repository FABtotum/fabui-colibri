<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<!-- BACKUP ROW -->
<div class="row">
	<div class="col-xs-2 col-sm-1 text-center">
		<a class="btn btn-default" data-toggle="modal" data-target="#backupModal"><?php echo _("Backup");?></a>
	</div>
	<div class="col-xs-10 col-sm-11">
		<h6 class="no-margin"><?php echo _("Backup wizard");?></h6>
		<p><?php echo _("The backup wizard helps you to create a backup of your settings and files");?></p>
	</div>
</div>
<hr class="simple">
<!-- RESTORE ROW -->
<div class="row">
	<div class="col-xs-2 col-sm-1 text-center">
		<a class="btn btn-default" data-toggle="modal"  data-target="#restoreModal"><?php echo _("Restore");?></a>
	</div>
	<div class="col-xs-10 col-sm-11">
		<h6 class="no-margin"><?php echo _("Restore wizard");?></h6>
		<p><?php echo _("The restore wizard helps you to restore your data from a backup");?></p>
	</div>
</div>

<!-- backup Modal -->  
<div class="modal fade" id="backupModal" tabindex="-1" role="dialog" aria-hidden="true">  
    <div class="modal-dialog">  
        <div class="modal-content">
        	<div class="modal-header">
            	<h4 class="modal-title" id="myModalLabel"><i class="fa fa-magic"></i> <?php echo _("Backup wizard");?></h4>
            </div>
            <div class="modal-body">
            	<div class="row">
            		<div class="smart-form">
            			<fieldset>
            				<section>
            					<label class="radio">
            						<input type="radio" id="backup_mode" name="backup_mode" value="default" checked="checked"> <i></i> <?php echo _("Default backup");?>
            					</label>
            					<label class="radio">
            						<input type="radio" id="backup_mode" name="backup_mode" value="advanced"> <i></i> <?php echo _("Advanced backup");?>
            					</label>
            				</section>
            			</fieldset>
            			<fieldset id="advanced-backup-fields" style="display:none;">
            				<section>
            					<label class="label"><?php echo _("System");?></label>
            					<div class="inline-group">
            						<label class="checkbox">
            							<input type="checkbox" class="backup-folders" id="system-heads" value="system-heads"><i></i> <?php echo _("Heads");?>
            						</label>
            						<label class="checkbox">
            							<input type="checkbox" class="backup-folders"  id="system-feeders" value="system-feeders"><i></i> <?php echo _("Feeders");?>
            						</label>
            						<label class="checkbox">
            							<input type="checkbox" class="backup-folders"  id="system-settings" value="system-settings"><i></i> <?php echo _("Settings");?>
            						</label>
            						<label class="checkbox">
            							<input type="checkbox" class="backup-folders" id="system-cam" value="system-cam"><i></i> <?php echo _("CAM");?>
            						</label>
            					</div>
            				</section>
            				<section>
            					<label class="label"><?php echo _("Firmware");?></label>
            					<div class="inline-group">
            						<label class="checkbox">
            							<input type="checkbox" id="firmware"  value="firmware-eeprom"><i></i> <?php echo _("EEPROM");?>
            						</label>
            					</div>
            				</section>
            				<section>
            					<label class="label"><?php echo _("Users");?></label>
            					<div class="inline-group">
            						<label class="checkbox">
            							<input type="checkbox" class="backup-folders" value="users-data"><i></i> <?php echo _("Data");?>
            						</label>
            					</div>
            				</section>
            			</fieldset>
            		</div>
            	</div>
            </div>
            <div class="modal-footer">
            	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            	<button type="button" class="btn btn-primary action" data-action="backup"><?php echo _("Backup");?></button>
            </div>
        </div>  
    </div>  
</div>  
<!-- /. bakcup modal -->

<!-- restore Modal -->  
<div class="modal fade" id="restoreModal" tabindex="-1" role="dialog" aria-hidden="true">  
    <div class="modal-dialog">  
        <div class="modal-content">
        	<div class="modal-header">
            	<h4 class="modal-title" id="myModalLabel"><?php echo _("Restore wizard");?></h4>
            </div>
            <div class="modal-body">
            	<div class="row">
            		<div class="col-sm-12">
            			<div id="upload-backup-dropzone" class="dropzone"></div>
            		</div>
            	</div>
            </div>
            <div class="modal-footer">
            	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            	<button type="button" class="btn btn-primary action" data-action="upload" id="upload-button"><?php echo _("Upload");?></button>
            </div>
        </div>  
    </div>  
</div>  
<!-- /. bakcup modal --> 
