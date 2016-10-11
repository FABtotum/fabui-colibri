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
		<div class="smart-form">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">Name</label>
						<label class="input">
							<input type="text" name="name" id="name" value="<?php echo $object['name'] ?>">
						</label>
					</section>
					<section class="col col-6">
						<label class="label">Public</label>
						<div class="inline-group">
							<label class="radio">
								<input type="radio" <?php echo $object['public'] == 1 ? 'checked="checked"' : ''; ?> name="public" value="1"><i></i> Yes
							</label>
							<label class="radio">
								<input type="radio" <?php echo $object['public'] == 0 ? 'checked="checked"' : ''; ?> name="public" value="0"><i></i> Yes
							</label>
						</div>
					</section>
				</div>
				<section>
					<label class="label">Description</label>
					<label class="textarea textarea-resizable">
						<textarea name="description" rows="5" class="custom-scroll"><?php echo $object['description'] ?></textarea> 
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
						<th>Name</th>
						<th>Type</th>
						<th>Note</th>
						<th>Date</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>
