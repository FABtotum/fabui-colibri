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
	<div class="tab-pane fade in active" id="ethernet-tab">
		<div class="smart-form">
			<fieldset>
				<section class="col col-6">
					<label class="label">Address Mode</label>
					<label class="select">
						<?php echo form_dropdown('feeder-show', $addressMode, 'static', 'id="address-mode"'); ?> <i></i>
					</label>
				</section>
			</fieldset>
			<fieldset>
				<section class="col col-6">
					<div class="form-group">
						<label class="label">Address</label>
						<div class="input-group">
							<input type="text" id="eth0-ip" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $iface['eth0']['inet_address']; ?>"/>
							<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
						</div>
					</div>
					<div class="form-group">
						<label class="label">Netmask</label>
						<div class="input-group">
							<input type="text" id="eth0-netmask" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $iface['eth0']['netmask_address']; ?>"/>
							<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
						</div>
					</div>
					<div class="form-group">
						<label class="label">Gateway</label>
						<div class="input-group">
							<input type="text" id="eth-gateway" data-inputmask="'alias': 'ip'" class="form-control ip" value="0.0.0.0"/>
							<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
						</div>
					</div>
				</section>
			</fieldset>
		</div>
	</div>
	<div class="tab-pane fade in" id="wireless-wlan0-tab">
		<div class="smart-form">
			<fieldset>
				<section class="col col-3">
					<label class="label">Address Mode</label>
					<label class="select">
						<?php echo form_dropdown('feeder-show', $addressMode, 'static', 'id="address-mode"'); ?> <i></i>
					</label>
				</section>
			</fieldset>
		</div>
	</div>
	<div class="tab-pane fade in" id="dnssd-tab">

		<form class="smart-form" id="hostname-form">
			
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">Name</label>
						<div class="input-group">
							<span class="input-group-addon">http://</span>
							<input style="padding-left:5px" value="<?php echo $current_hostname; ?>" placeholder="<?php echo $current_hostname; ?>" class="form-control" id="hostname" type="text">
							<span class="input-group-addon">.local</span>
						</div>
						<div class="note"></div>
					</section>
				</div>
				<div class="row">
					<section class="col col-6">
						<label class="label">Description</label>
						<label class="input">
							<input type="text" value="<?php echo $current_name; ?>" id="name"> 
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
