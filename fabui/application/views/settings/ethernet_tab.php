<div class="tab-pane fade in <?php echo $active ?>" id="<?php echo $iface;?>-tab" data-attribute="<?php echo $iface; ?>">
	<div class="smart-form">
		<fieldset>
			<section class="col col-6">
				<label class="label"><?php echo _('Address mode')?></label>
				<label class="select">
					<?php echo form_dropdown('address-show', $addressModeEth, $info['address_mode'], array('id' => 'address-mode', 'class' => "address-mode", 'data-attribute' => $iface)); ?> <i></i>
				</label>
			</section>
			
			<section id="dhcp-address-container" style="<?php if($info['address_mode'] != 'dhcp') echo 'display:none;';?>" class="col col-6">
				<table class="table ">
					<tbody>
						<tr>
							<td style="border:0px;" width="200px"> <?php echo _('IP address')?></td>
							<td style="border:0px;"><a class="no-ajax" href="http://<?php echo $info['ipv4_address']; ?>" target="_blank"><?php echo $info['ipv4_address']; ?></a></td>
						</tr>
						<tr>
							<td><?php echo _('MAC address')?></td>
							<td><strong><?php echo $info['mac_address']; ?></strong></td>
						</tr>
					</tbody>
				</table>
			</section>
			
		</fieldset>
		<fieldset>
			<section class="col col-6" id="address-container" style="<?php if($info['address_mode'] == 'dhcp') echo 'display:none;';?>">
				<form class="addressForm">
					<div class="form-group">
						<label class="label"><?php echo _('Address')?></label>
						<label class="input">
						<div class="input-group">
							<input type="text" id="ipv4" name="ipv4" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $info['ipv4_address']; ?>"/>
							<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
						</div>
						</label>
					</div>
					<div class="form-group">
						<label class="label"><?php echo _('Netmask')?></label>
						<label class="input">
						<div class="input-group">
							<input type="text" id="netmask" name="netmask" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $info['netmask_address']; ?>"/>
							<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
						</div>
						</label>
					</div>
					<div class="form-group">
						<label class="label"><?php echo _('Gateway')?></label>
						<label class="input">
						<div class="input-group">
							<input type="text" id="gateway" name="gateway" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo $info['gateway']; ?>"/>
							<span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
						</div>
						</label>
					</div>
				</form>
			</section>
		</fieldset>
	</div>
</div>
