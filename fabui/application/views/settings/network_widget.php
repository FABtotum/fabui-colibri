<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="tab-content padding-10">
	<?php echo $iface_tabs ?>
	<div class="tab-pane fade in" id="dnssd-tab">

		<form class="smart-form" id="hostname-form">
			
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">Name</label>
						<div class="input-group">
							<span class="input-group-addon">http://</span>
							<input style="padding-left:5px" value="<?php echo $current_hostname; ?>" placeholder="<?php echo $current_hostname; ?>" class="form-control" id="dnssd-hostname" type="text">
							<span class="input-group-addon">.local</span>
						</div>
						<div class="note"></div>
					</section>
				</div>
				<div class="row">
					<section class="col col-6">
						<label class="label">Description</label>
						<label class="input">
							<input type="text" value="<?php echo $current_name; ?>" id="dnssd-name"> 
						</label>
					</section>
				</div>
				<div class="row">
					<section class="col col-6">
						<div class="note">
						To easily access to the FABtotum Personal Fabricator by using the name you inserted you need to install on the device you are using to access it a multicast domain name system service discovery such as <abbr title="Bonjour">Bonjour</abbr> or similar
					</div>
					</section>
				</div>
			</fieldset>
		</form>


	</div>
</div>

<!-- PASSWORD MODAL -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fa fa-lock"></i> <span id="passwordModalTitle"></span> <i class="fa fa-wifi"></i></h4>
			</div>
			<div class="modal-body custom-scroll " id="progressModalBody">
				<form class="smart-form" id="passwordModalForm">
					<fieldset>
						<section>
							<label class="input"> <i class="icon-prepend fa fa-lock"></i>
								<input type="password" class="input-password" placeholder="insert password" id="wifiPassword" name="wifiPassword">
							</label>
						</section>
						<section>
							<label class="checkbox">
								<input type="checkbox" data-attribute="modal" class="show-password"> <i></i> Show password
							</label>
						</section>
					</fieldset>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="modalConnectButton"><i class="fa fa-check"></i> Connect </button>
			</div>
		</div>
	</div>
</div>
