<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="row">
	<div class="col-sm-12">
		<form class="form-horizontal" id="create-project-form" method="POST"
						data-bv-message="This value is not valid"
						data-bv-feedbackicons-valid="glyphicon glyphicon-ok"
						data-bv-feedbackicons-invalid="glyphicon glyphicon-remove"
						data-bv-feedbackicons-validating="glyphicon glyphicon-refresh">
			<fieldset>
				<legend><?php echo _("Project basic info");?></legend>
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Name");?></label>
					<div class="col-md-10">
						<input type="text" class="form-control" name="project-name" placeholder="<?php echo _("Project name");?>" 
							data-bv-notempty="true" 
							data-bv-notempty-message="<?php echo _("Project name is required and cannot be empty");?>">
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Description");?></label>
					<div class="col-md-10">
						<textarea class="custom-scroll form-control" name="project-description" placeholder="<?php echo _("Project description");?>" 
							data-bv-notempty="true" 
							data-bv-notempty-message="<?php echo _("Project description is required and cannot be empty");?>"></textarea>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Category");?></label>
					<div class="col-md-10">
						<?php echo form_dropdown('project-categories', $categories, null, 'multiple class="select2" data-bv-notempty="true"');?> 
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Visibility");?></label>
					<div class="col-md-10">
						<label class="radio radio-inline">
							<input type="radio" class="radiobox" name="project-visibility" value="PUBLIC" data-bv-notempty="true"> <span><?php echo _("Public");?></span> 
						</label>
						<label class="radio radio-inline">
							<input type="radio" class="radiobox" name="project-visibility" value="PRIVATE" data-bv-notempty="true"> <span><?php echo _("Private");?></span> 
						</label>
					</div>
				</div>
			</fieldset>
			
			<fieldset id="partTemplate">
				<legend><?php echo _("Part");?></legend>
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Name");?></label>
					<div class="col-md-10">
						<input type="text" name="part-0-name" data-bv-notempty="true" class="form-control" placeholder="<?php echo _("Part name");?>">
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Description");?></label>
					<div class="col-md-10">
						<textarea class="custom-scroll form-control" name="part-0-description" data-bv-notempty="true" placeholder="<?php echo _("Part description");?>"></textarea>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Tool");?></label>
					<div class="col-md-10">
						<?php echo form_dropdown('part-0-tool', $tools, null, 'class="form-control" data-bv-notempty="true"');?> 
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("File(s)");?></label>
					<div class="col-md-10">
						<div id="dropzone-part-0"  class="dropzone" style="min-height: 100px;"></div>
					</div>
				</div>
			</fieldset>
			
			<input type="hidden" name="part-0-files">
			
			<div class="form-actions">
				<div class="row">
					<div class="col-md-12">
						<button class="btn btn-default" id="add-part" type="button"><i class="fa fa-plus"></i> <?php echo _("Add part") ?></button>
						<button class="btn btn-default btn-primary" type="button" id="save-project"><i class="fa fa-save"></i> <?php echo _("Save");?></button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
