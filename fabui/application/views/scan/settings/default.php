<?php
/**
 * 
 * @author Krios Mane
 * @author Fabtotum Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
?>
<hr class="simple">
<div class="row">
	<div class="col-sm-3">
		<div class="smart-form">
			<fieldset>
				<section>
					<label class="radio">
						<input type="radio" id="object_type" name="object_type" checked="checked" value="new"><i></i> Create new object 
					</label>
					<label class="radio">
						<input type="radio" id="object_type" name="object_type" value="add"><i></i> Add to an existing object
					</label>
				</section>
				
			</fieldset>
		</div>
	</div>
	<div class="col-sm-9">
		<div class="smart-form">
			<fieldset>
				<section class="section-new-object">
					<label class="input">
						<i class="icon-prepend fa fa-folder-open"></i>
						<input type="text" placeholder="Type object name">
					</label>
				</section>
				<section class="section-existing-object" style="display: none;">
					<label class="select">
						<?php echo form_dropdown('shirts', $objectsForDropdown, ''); ?> <i></i>
					</label>
				</section>
				<section>
					<label class="input">
						<i class="icon-prepend fa fa-file-o"></i>
						<input type="text" placeholder="Type file name">
					</label>
				</section>
			</fieldset>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(':radio[name="object_type"]').on('change', setObjectMode);
</script>
<?php echo $content; ?>