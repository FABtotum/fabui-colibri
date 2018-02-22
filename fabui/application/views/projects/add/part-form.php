<fieldset id="project-part-<?php echo $index; ?>">
	<legend><?php echo _("Part");?> <button data-index="<?php echo $index;?>" class="btn btn-default btn-sm remove-part"><i class="fa fa-times"></i> </button></legend>
	<div class="form-group">
		<label class="col-md-2 control-label"><?php echo _("Name");?></label>
		<div class="col-md-10">
			<input type="text" name="part-<?php echo $index; ?>-name" data-bv-notempty="true" class="form-control" placeholder="<?php echo _("Part name");?>">
		</div>
	</div>
	
	<div class="form-group">
		<label class="col-md-2 control-label"><?php echo _("Description");?></label>
		<div class="col-md-10">
			<textarea class="custom-scroll form-control" data-bv-notempty="true" name="part-<?php echo $index; ?>-description" placeholder="<?php echo _("Part description");?>"></textarea>
		</div>
	</div>
	
	<div class="form-group">
		<label class="col-md-2 control-label"><?php echo _("Tool");?></label>
		<div class="col-md-10">
			<?php echo form_dropdown('part-'.$index.'-creation_tool', $tools, null, 'class="form-control" data-bv-notempty="true"');?> 
		</div>
	</div>
	
	<div class="form-group">
		<label class="col-md-2 control-label"><?php echo _("File(s)");?></label>
		<div class="col-md-5">
			<div id="dropzone-part-<?php echo $index; ?>-source"  class="dropzone" style="min-height: 100px;"></div>
		</div>
		<div class="col-md-5">
			<div id="dropzone-part-<?php echo $index; ?>-machine"  class="dropzone" style="min-height: 100px;"></div>
		</div>
	</div>
</fieldset>

<input type="hidden" name="part-<?php echo $index; ?>-source_file">
<input type="hidden" name="part-<?php echo $index; ?>-machine_file">