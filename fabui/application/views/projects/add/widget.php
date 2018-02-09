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
		<form class="form-horizontal" id="crate-project-form" data-bv-message="This value is not valid"
						data-bv-feedbackicons-valid="glyphicon glyphicon-ok"
						data-bv-feedbackicons-invalid="glyphicon glyphicon-remove"
						data-bv-feedbackicons-validating="glyphicon glyphicon-refresh">
			<fieldset>
				<legend><?php echo _("Project basic info");?></legend>
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Name");?></label>
					<div class="col-md-10">
						<input type="text" class="form-control" placeholder="<?php echo _("Project name");?>" data-bv-notempty="true" data-bv-notempty-message="<?php echo _("Project name is required and cannot be empty");?>">
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Description");?></label>
					<div class="col-md-10">
						<textarea class="custom-scroll form-control" placeholder="<?php echo _("Project description");?>"></textarea>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Category");?></label>
					<div class="col-md-10">
						<?php echo form_dropdown('categories', $categories, null, 'multiple class="select2"');?> 
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Visibility");?></label>
					<div class="col-md-10">
						<label class="radio radio-inline">
							<input type="radio" class="radiobox" name="visibility"> <span><?php echo _("Public");?></span> 
						</label>
						<label class="radio radio-inline">
							<input type="radio" class="radiobox" name="visibility"> <span><?php echo _("Private");?></span> 
						</label>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend><?php echo _("Part");?></legend>
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Name");?></label>
					<div class="col-md-10">
						<input type="text" class="form-control" placeholder="<?php echo _("Part name");?>">
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Description");?></label>
					<div class="col-md-10">
						<textarea class="custom-scroll form-control" placeholder="<?php echo _("Part description");?>"></textarea>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("Tool");?></label>
					<div class="col-md-10">
						<?php echo form_dropdown('tool', $tools, null, 'class="form-control"');?> 
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-md-2 control-label"><?php echo _("File(s)");?></label>
					<div class="col-md-10">
						<div id="dropzone-part-0"  class="dropzone" style="min-height: 100px;"></div>
					</div>
				</div>
			</fieldset>
		</form>
	</div>
</div>
