<div class="row">
	<div class="col-sm-12">
		<div class="row">
			
			<div class="col-sm-6">
				<div class="smart-form">
					<label class="label font-md">Select firmware version</label>
					<label class="select">
							<?php echo form_dropdown('fw-version', $fw_versions, 'factory', 'class="input-lg" id="fw-version"'); ?>
						<i></i> 
					</label>
				</div>
				
				<hr class="simple">
				
				<div class="upload-section text-center block-center" style="display:none;">
					<form class="form-inline" enctype="multipart/form-data">
						<fieldset>
							
							<div class="form-group">
								<input type="file" class="btn btn-default" id="hex-file" name="hex-file" accept=".hex">
							</div>
							<button type="button" id="install-button" class="btn btn-primary disabled flash-button" style="margin-left:5px;">Flash firmware</button>
							
						</fieldset>
					</form>
				</div>
				
				<div class="flash-section">
					<p class="text-center">
						<button type="button" class="btn btn-primary btn-default flash-button"> Flash firmware</button>
					</p>
				</div>
			</div>

			<div class="col-sm-6">
				<table class="table ">
					<tbody>
						<tr>
							<td style="border:0px;"><strong>Installed Firmware</strong></td>
						</tr>
						<tr>
							<td style="border:0px;" width="200px">Version</td>
							<td style="border:0px;"><?php echo $fw_version;?></td>
						</tr>
						<tr>
							<td >Author</td>
							<td><?php echo $fw_author;?></td>
						</tr>
						<tr>
							<td >Build-date</td>
							<td><?php echo $fw_buildate;?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</div>


