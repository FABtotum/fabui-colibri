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
	<div class="tab-pane fade in <?php echo $preSelectedInterface == 'dnssd'? 'active' : '' ?>" id="dnssd-tab"  data-attribute="dnssd">
		<form class="smart-form" id="hostname-form">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label"><?php echo _('Name')?></label>
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
						<label class="label"><?php echo _('Description')?></label>
						<label class="input">
							<input type="text" value="<?php echo $current_name; ?>" id="dnssd-name"> 
						</label>
					</section>
				</div>
				<div class="row">
					<section class="col col-6">
						<div class="note">
						<?php echo _('To easily access the FABtotum Personal Fabricator by using the name you inserted it is necessary to have a multicast domain name system service discovery installed, such as Bonjour or similar.')?>
					</div>
					</section>
				</div>
			</fieldset>
		</form>
	</div><!-- DNS-SD -->
	
	<div class="tab-pane fade in <?php echo $preSelectedInterface == 'dns'? 'active' : '' ?>" id="dns-tab"  data-attribute="dns">
		<form class="smart-form" id="dns-form">
			
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<div class="form-group">
							<label class="label"><?php echo _('Head')?></label>
							<label class="input">
								<input type="text" data-attr="head" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo isset($dns['head'][0])?$dns['head'][0]:''; ?>"/>
							</label>
							<label class="input">
								<input type="text" data-attr="head" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo isset($dns['head'][1])?$dns['head'][1]:''; ?>"/>
							</label>
						</div>
					</section>
				</div>
				
				<div class="row">
					<section class="col col-6">
						<div class="note">
						<?php echo _('These DNS settings will be added before DNS which is acquired over DHCP')?>
						</div>
					</section>
				</div>
			</fieldset><!-- Head -->
			
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<div class="form-group">
							<label class="label"><?php echo _('Current')?></label>
							<label class="input">
								<input type="text" data-attr="current" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo isset($dns['current'][0])?$dns['current'][0]:''; ?>" readonly/>
							</label>
							<label class="input">
								<input type="text" data-attr="current" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo isset($dns['current'][1])?$dns['current'][1]:''; ?>" readonly/>
							</label>
						</div>
					</section>
				</div>
			</fieldset><!-- Current -->
			
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<div class="form-group">
							<label class="label"><?php echo _('Tail')?></label>
							<label class="input">
								<input type="text" data-attr="tail" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo isset($dns['tail'][0])?$dns['tail'][0]:''; ?>"/>
							</label>
							<label class="input">
								<input type="text" data-attr="tail" data-inputmask="'alias': 'ip'" class="form-control ip" value="<?php echo isset($dns['tail'][1])?$dns['tail'][1]:''; ?>"/>
							</label>
						</div>
					</section>
				</div>
				
				<div class="row">
					<section class="col col-6">
						<div class="note">
						<?php echo _('These DNS settings will be added after DNS which is acquired over DHCP')?>
						</div>
					</section>
				</div>
			</fieldset><!-- Tail -->
			
		</form>
	</div><!-- DNS -->
	
	<!--
	<div class="tab-pane fade in <?php echo $preSelectedInterface == 'ssh'? 'active' : '' ?>" id="ssh-tab"  data-attribute="ssh">
	</div>
	-->
	
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
								<input type="password" data-inputmask-regex="[-_a-z A-Z0-9$@^`,|%;.~()/\{}:?\[\]=+_#!]*" class="input-password password" placeholder="insert password" id="wifiPassword" name="wifiPassword">
							</label>
						</section>
						<section>
							<label class="checkbox">
								<input type="checkbox" data-attribute="modal" class="show-password"> <i></i> <?php echo _('Show password')?>
							</label>
						</section>
					</fieldset>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel')?></button>
				<button type="button" class="btn btn-primary" id="modalConnectButton"><i class="fa fa-check"></i> <?php echo _('Connect')?> </button>
			</div>
		</div>
	</div>
</div>
