<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<?php if(isset($alert['type'])): ?>
	<div class="row">
		<div class="col-sm-12">
			<?php echo showAlert($alert['type'], $alert['message']); ?>
		</div>
	</div>
<?php endif; ?>
<div class="row">
	<div class="col-sm-12">
		<div>
			<table id="objects-table" class="table table-responsive table-striped table-bordered table-hover has-tickbox cursor-pointer" width="100%">
				<thead>
					<tr>
						<th><label class="checkbox-inline"><input type="checkbox" id="selectAll" name="checkbox-inline" class="checkbox"><span></span> </label></th>
						<th>Name</th>
						<th class="hidden-xs"><?php echo _("Description") ?></th>
						<th class="hidden-xs"><?php echo _("Date") ?></th>
						<th class="hidden-xs"><i class="fa fa-files-o"></i></th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>
