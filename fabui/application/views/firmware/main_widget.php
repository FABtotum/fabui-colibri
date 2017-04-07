<div class="row">
	<div class="col-sm-12">
		<div class="row">
			
			<div class="col-sm-6">
				<div class="smart-form">
					<label class="label font-md"><?php echo _("Select firmware version");?></label>
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
							<button type="button" id="install-button" class="btn btn-primary disabled flash-button" style="margin-left:5px;"><?php echo _("Flash firmware");?></button>
							
						</fieldset>
					</form>
				</div>
				
				<div class="flash-section">
					<p class="text-center">
						<button type="button" class="btn btn-primary btn-default flash-button"> <?php echo _("Flash firmware");?></button>
					</p>
				</div>
			</div>

			<div class="col-sm-6">
				<table class="table ">
					<tbody>
						<tr>
							<td style="border:0px;"><strong><?php echo _("Installed Firmware");?></strong></td>
						</tr>
						<tr>
							<td style="border:0px;" width="200px"><?php echo _("Version");?></td>
							<td style="border:0px;"><?php echo isset($firmwareInfo['firmware']['version']) ? $firmwareInfo['firmware']['version'] : 'n.a.';?></td>
						</tr>
						<tr>
							<td><?php echo _("Author");?></td>
							<td><?php echo isset($firmwareInfo['firmware']['author']) ? $firmwareInfo['firmware']['author'] : 'n.a.';?></td>
						</tr>
						<tr>
							<td><?php echo _("Build-date");?></td>
							<td><?php echo isset($firmwareInfo['firmware']['build_date']) ? $firmwareInfo['firmware']['build_date'] : 'n.a.';?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</div>


