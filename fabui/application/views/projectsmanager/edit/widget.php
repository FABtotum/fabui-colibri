<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<?php if(!$isOwner): ?>
	<div class="row">
		<div class="col-sm-12">
			<div class="alert alert-warning fade in">
				<i class="fa-fw fa fa-lock"></i> <?php echo _("You don't have permissions to edit this project"); ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<div class="row">
	<div class="col-sm-12">
		<div class="smart-form">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label"><?php echo _("Name") ?></label>
						<label class="input">
							<input <?php if(!$isOwner): ?> readonly="readonly" <?php endif;?> type="text" name="name" id="obj_name" value="<?php echo $object['name'] ?>">
						</label>
					</section>
					<section class="col col-6">
						<?php if($isOwner): ?>
						<label class="label"><?php echo _("Public") ?></label>
						<div class="inline-group">
							<label class="radio">
								<input type="radio" <?php echo $object['public'] == 1 ? 'checked="checked"' : ''; ?> name="public" value="1"><i></i> Yes
							</label>
							<label class="radio">
								<input type="radio" <?php echo $object['public'] == 0 ? 'checked="checked"' : ''; ?> name="public" value="0"><i></i> No
							</label>
						</div>
						<?php endif; ?>
					</section>
				</div>
				<section>
					<label class="label"><?php echo _("Description") ?></label>
					<label class="textarea textarea-resizable">
						<textarea <?php if(!$isOwner): ?> readonly="readonly" <?php endif;?> id="obj_description" rows="5" class="custom-scroll"><?php echo $object['description'] ?></textarea> 
					</label>
				</section>
			</fieldset>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div>
			<table id="files-table" class="table table-responsive table-striped table-bordered table-hover has-tickbox cursor-pointer" width="100%">
				<thead>
					<tr>
						<th><label class="checkbox-inline"><input type="checkbox" id="selectAll" name="checkbox-inline" class="checkbox"><span></span> </label></th>
						<th><?php echo _("Name") ?></th>
						<th class="hidden-xs"><?php echo _("Type") ?></th>
						<th class="hidden-xs"><?php echo _("Note") ?></th>
						<th class="hidden-xs"><?php echo _("Date") ?></th>
						<th><?php echo _("Default action") ?></th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>
