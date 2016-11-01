<div class="tab-pane fade in" id="<?php echo $iface;?>-tab">
	<div class="smart-form">
		<fieldset>
			<section class="col col-6">
				<label class="label">Address Mode</label>
				<label class="select">
					<?php echo form_dropdown('address-show', $addressModeWiFi, $info['address_mode'], 'id="'.$iface.'-address-mode"'); ?> <i></i>
				</label>
			</section>
		</fieldset>
		<fieldset>
			<section class="col col-6">
				<div class="form-group">
					<label class="label">Address</label>
					<label class="input">
					<div class="input-group">
						<input type="text" id="<?php echo $iface;?>-ip" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $info['ipv4_address']; ?>"/>
						<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
					</div>
					</label>
				</div>
				<div class="form-group">
					<label class="label">Netmask</label>
					<label class="input">
					<div class="input-group">
						<input type="text" id="<?php echo $iface;?>-netmask" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $info['netmask_address']; ?>"/>
						<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
					</div>
					</label>
				</div>
				<div class="form-group">
					<label class="label">Gateway</label>
					<label class="input">
					<div class="input-group">
						<input type="text" id="<?php echo $iface;?>-gateway" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $info['gateway']; ?>"/>
						<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
					</div>
					</label>
				</div>
			</section>
		</fieldset>
		<fieldset>
			<section class="col col-6">
				<div class="form-group">
					<label class="label">SSID</label>
					<label class="input">
					<div class="input-group">
						<input type="text" id="<?php echo $iface;?>-ssid" placeholder="FABtotum" class="form-control" value="FABtotum"/>
						<span class="input-group-addon"><i class="fa fa-wifi"></i></span>
					</div>
					</label>
				</div>
				<div class="form-group">
					<label class="label">Password</label>
					<label class="input">
					<div class="input-group">
						<input type="password" id="<?php echo $iface;?>-password" class="form-control" value="fabtotumwifi"/>
						<span class="input-group-addon"><i class="fa fa-lock"></i></span>
					</div>
					</label>
					<label class="checkbox">
					<input type="checkbox" class="show-password"> <i></i> Show password
					</label>
				</div>

			</section>
		</fieldset>
	</div>
</div>
