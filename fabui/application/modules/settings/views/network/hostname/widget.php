<div class="row">
	<div class="col-sm-12">
		<form class="smart-form" id="hostname-form">
			<fieldset>
				<section>
					<label class="label">Hostname</label>
					<div class="input-group">
						<span class="input-group-addon">http://</span>
						<input style="padding-left:5px" value="<?php echo $current_hostname; ?>" placeholder="<?php echo $current_hostname; ?>" class="form-control" id="hostname" type="text">
						<span class="input-group-addon">.local</span>
					</div>
					<div class="note"></div>
				</section>
				<section>
					<label class="label">Description</label>
					<label class="input">
						<input type="text" value="<?php echo $current_name; ?>" id="name"> 
					</label>
				</section>
				
				
			</fieldset>
			<footer>
				<button type="button" id="save" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
			</footer>
		</form>
	</div>
</div>
<form action="<?php site_url('settings/network/hostname') ?>" id="response-form" method="post">
	<input type="hidden" id="response" name="response">
	<input type="hidden" id="new_hostname" name="new_hostname">
</form>