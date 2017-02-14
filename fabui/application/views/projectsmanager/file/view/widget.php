
<div class="row">
	<div class="col-sm-12">

			<div class="row">
				<div class="col-sm-8">
					<div class="form-horizontal">
						<fieldset>
							<div class="form-group">
								<label class="col-md-2 control-label"><?php echo _('Name') ?></label>
								<div class="col-md-10">
									<input type="text" id="name" name="name" class="form-control" value="<?php echo $file['client_name']; ?>" />
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-2 control-label"><?php echo _('Note') ?></label>
								<div class="col-md-10">
									<textarea style="resize: none !important;" id="note" name="note" class="form-control" rows="2"><?php echo $file['note']; ?></textarea>
								</div>
							</div>		
						</fieldset>
					</div>
				</div>
				<?php if($file['print_type'] == 'additive'): ?>
				<div class="col-sm-4">
					<div class="row">
						<div class="col-sm-12 margin-bottom-10">
							<span class="text"><?php echo _('Model size') ?> <span class="pull-right"><strong><?php echo $dimesions; ?></strong></span></span>
						</div>
						<div class="col-sm-12 margin-bottom-10">
							<span class="text"><?php echo _('Filament used') ?> <span class="pull-right"><strong><?php echo $filament; ?></strong></span></span>
						</div>
						<div class="col-sm-12 margin-bottom-10">
							<span class="text"><?php echo _('Estimated time print') ?> <span class="pull-right"><strong><?php echo $estimated_time; ?></strong></span></span>
						</div>
						<div class="col-sm-12 margin-bottom-10">
							<span class="text"><?php echo _('Layers') ?> <span class="pull-right"><strong><?php echo $number_of_layers; ?></strong></span></span>
						</div>
					</div>
				</div>
                <?php else: ?>
				<?php endif; ?>
			</div>
			
			<div class="row row-sm-6">
				<div class="col-sm-12">
					<div class="well file-content-small" id="editor" style="display:none;"></div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12">
					<div class="form-horizontal">
						<div class="form-actions">
							<div class="row">
								
								<div class="col-sm-12">
									<?php if($is_editable): ?>
										<button class="btn btn-default pull-left" type="button" id="load-content"><i class="fa fa-angle-double-down"></i> <?php echo _('View content') ?> </button>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

	</div>
</div>
