<fieldset id="project-part-<?php echo $index; ?>">
	<legend><?php echo _("Part");?> <button data-index="<?php echo $index;?>" class="btn btn-default btn-sm remove-part"><i class="fa fa-times"></i> </button></legend>
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
			<div id="dropzone-part-<?php echo $index; ?>"  class="dropzone" style="min-height: 100px;"></div>
		</div>
	</div>
</fieldset>