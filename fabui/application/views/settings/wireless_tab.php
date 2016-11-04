<div class="tab-pane fade in" id="<?php echo $iface;?>-tab" data-attribute="<?php echo $iface; ?>">
	<div class="smart-form">
		<fieldset>
			<section class="col col-6">
				<label class="label">Address Mode</label>
				<label class="select">
					<?php echo form_dropdown('address-show', $addressModeWiFi, $info['address_mode'], array('id' => 'address-mode', 'class' => "address-mode", 'data-attribute' => $iface) ); ?> <i></i>
				</label>
			</section>
		</fieldset>
		
		<fieldset>
			
			<section id="address-container" style="<?php if($info['address_mode'] == 'dhcp') echo 'display:none;';?>" class="col col-6">
				<form class="addressForm">
				<div class="form-group">
					<label class="label">Address</label>
					<label class="input">
					<div class="input-group">
						<input type="text" id="ipv4" name="ipv4" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $info['ipv4_address']; ?>"/>
						<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
					</div>
					</label>
				</div>
				<div class="form-group">
					<label class="label">Netmask</label>
					<label class="input">
					<div class="input-group">
						<input type="text" id="netmask" name="netmask" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $info['netmask_address']; ?>"/>
						<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
					</div>
					</label>
				</div>
				<div class="form-group" id="gateway-container" style="<?php if($info['address_mode'] == 'static-ap') echo 'display:none;';?>">
					<label class="label">Gateway</label>
					<label class="input">
					<div class="input-group">
						<input type="text" id="gateway" name="gateway" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $info['gateway']; ?>"/>
						<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
					</div>
					</label>
				</div>
				</form>
			</section>
			
			<section id="ap-container" style="<?php if($info['address_mode'] != 'static-ap') echo 'display:none;';?>" class="col col-6">
				<form id="apForm" class="apForm">
					<div  class="form-group">
						<label class="label">SSID</label>
						<label class="input">
						<div class="input-group">
							<input type="text" id="ap-ssid" name="ssid" placeholder="FABtotum" class="form-control" value="<?php echo ($info['address_mode'] == 'static-ap')?$info['wireless']['ssid']:"FABtotum"; ?>"/>
							<span class="input-group-addon"><i class="fa fa-wifi"></i></span>
						</div>
						</label>
					</div>
					<div class="form-group">
						<label class="label">Password</label>
						<label class="input">
						<div class="input-group">
							<input type="password" id="ap-password" placeholder="enter password" name="password" class="form-control password" value="<?php echo ($info['address_mode'] == 'static-ap')?$info['wireless']['passphrase']:""; ?>"/>
							<span class="input-group-addon"><i class="fa fa-lock"></i></span>
						</div>
						</label>
						<label class="checkbox">
						<input type="checkbox" class="show-password" data-attribute="<?php echo $iface;?>"> <i></i> Show password
						</label>
					</div>
					</form>
				</section>
		</fieldset>
		<form id="hiddenWifiForm" style="display:none;">
			<fieldset>
				<input type="text" id="hidden-address-mode" name="hidden-address-mode" value="<?php echo $info['address_mode']; ?>"/>
				<input type="text" id="hidden-ssid" name="hidden-ssid" value="<?php echo $info['wireless']['ssid']; ?>"/>
				<input type="text" id="hidden-bssid" name="hidden-bssid" value="<?php echo $info['wireless']['bssid']; ?>"/>
				<input type="text" id="hidden-passphrase" name="hidden-passphrase" value="<?php echo $info['wireless']['passphrase']; ?>"/>
				<input type="text" id="hidden-psk" name="hidden-psk" value="<?php echo $info['wireless']['psk']; ?>"/>
			</fieldset>
		</form>
		
	</div>
	
	<div class="row">
		<div class="col-sm-12" id="<?php echo $iface;?>-table-container">
		</div>
	</div>
	
</div>

