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

<?php
$iso_default = '400';
$size_default = '1920x1080';
?>

<div class="row">
	<div class="col-sm-12">
		<div class="smart-form">
			<header>Camera settings</header>
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<div class="input-group">
							<span class="input-group-addon"><?php echo _("ISO");?></span>
							<label class="select"> 
								<?php echo form_dropdown('iso', $params['ISO'], $iso_default, 'class="input-sm" id="pg-iso"'); ?> 
								<i></i> 
							</label>
						</div>
					</section>
					<section class="col col-6">
						<div class="input-group">
							<span class="input-group-addon"><?php echo _("Size");?></span>
							<label class="select"> 
								<?php echo form_dropdown('size', $params['size'], $size_default, 'class="input-sm" id="pg-size"'); ?> 
								<i></i> 
							</label>
						</div>
					</section>
				</div>
				<div class="row">
					<section class="col col-6">
						<div class="input-group">
							<span class="input-group-addon"><?php echo _("Slices");?></span>
							<label class="input">
								<input type="number" value="60" step="1" id="pg-slices">
							</label>
						</div>
					</section>
				</div>
			</fieldset>
			<header>Desktop Server</header>
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<div class="input-group">
							<span class="input-group-addon"><?php echo _("IP address");?></span>
							<label class="input">
								<input name="pc-host-address" id="pc-host-address" value="<?php echo $_SERVER["REMOTE_ADDR"] ?>" type="text">
							</label>
						</div>
						<div class="margin-top-10">
						<span class="help-block"><strong><?php echo _('Before proceeding start the desktop server on your pc and check the connection. If you don\'t have the desktop server you can download it <a target="_blank" href="/utilities/FabtotumDesktopServer.jar">here</a>');?></strong></span>
						</div>
					</section>
					
					<section class="col col-3">
						<div class="input-group">
							<span class="input-group-addon"><?php echo _("Port");?></span>
							<label class="input">
								<input name="pc-host-port" id="pc-host-port" value="9898" type="number">
							</label>
						</div>
					</section>
					
					<section class="col col-3">
						<button id="connection_test_button" class="btn btn-sm btn-primary btn-block"><?php echo _("Check Connection");?></button>
						<div class="margin-top-10">
						<span class="help-block" id="connection-note" ></span>
						</div>
					</section>
				</div>
			</fieldset>
		</div>
	</div>
</div>
<!--  -->
<script type="text/javascript">
$(document).ready(function() {
	$(".button-next").attr('data-scan', 'photogrammetry');
	$("#connection_test_button").on('click', checkConnection);
});
</script>
