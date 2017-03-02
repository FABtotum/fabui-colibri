<div class="row">
	<div class="col-sm-12">
		<div class="smart-form">
			<section>
				<label class="label"><?php echo _("Name");?></label>
				<label class="input">
					<i class="icon-append fa fa-file-o"></i>
					<input type="text" value="<?php echo $file['client_name']; ?>" id="name" name="name">
				</label>
			</section>
			<section>
				<label class="label"><?php echo _("Note"); ?></label>
				<label class="textarea textarea-expandable">
					<i class="icon-append fa fa-pencil-square-o"></i>
					<textarea rows="4" id="note" name="note" class="custom-scroll"><?php echo $file['note']; ?></textarea>
				</label>
			</section>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="well file-content-small" id="editor" style="display:none;"></div>
	</div>
</div>