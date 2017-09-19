<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="lockscreen animated flipInY">
	
	<div class="logo text-center">
		<img src="/assets/img/fabui_v1.png">
	</div>
	<div id="bootstrap-wizard-1" class="col-sm-12">
			<div class="form-bootstrapWizard">
				<ul class="bootstrapWizard form-wizard">
					<?php foreach($steps as $index => $step): ?>
					<li class="<?php echo $step['active'] ? 'active' : '' ?>" style="width:<?php echo count($steps) == 5 ? '20' : '25' ?>%" data-target="#<?php echo $step['id'] ?>">
						<a href="#<?php echo $step['id'] ?>" data-toggle="tab"> <span class="step"><?php echo ($index+1) ?></span> <span class="title"><?php echo $step['title'] ?></span> </a>
					</li>
					<?php endforeach;?>
				</ul>
				<div class="clearfix"></div>
			</div>
			<div class="tab-content">
				<div class="tab-pane active" id="welcome-tab">
					<br>
					<h3><strong><?php echo _("Step")?> 1 </strong> - <?php echo _("Welcome")?></h3>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label><?php echo _("Please select your language before proceeding with the installation")?> </label>
								<div class="icon-addon addon-md">
				                    <?php echo langauges_menu('form-control', 'language', 'id="language"', $this->input->post('locale'));?>
				                    <label class="fa fa-flag"></label>
				                </div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label><?php echo _("What is your timezone?")?> </label>
								<div class="icon-addon addon-md">
				                    <?php echo timezone_menu('form-control', 'timezone', 'id="timezone"');?>
				                    <label class="fa fa-map-marker"></label>
				                </div>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="account-tab">
					<br>
					<h3><strong><?php echo _("Step")?> 2 </strong> - <?php echo _("Create your personal account")?></h3>
					<form id="install-form">
						<input type="hidden" name="browser-date" id="browser-date" />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-user  fa-fw"></i></span>
										<input class="form-control " placeholder="<?php echo _("First name")?>" type="text" name="first_name" id="first_name">
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-user  fa-fw"></i></span>
										<input class="form-control " placeholder="<?php echo _("Last name")?>" type="text" name="last_name" id="last_name">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-envelope  fa-fw"></i></span>
										<input class="form-control " placeholder="email@address.com" type="text" name="email" id="email">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-lock  fa-fw"></i></span>
										<input class="form-control " placeholder="Password" type="password" name="password" id="password">
									</div>
								</div>
							</div>
							<div class="col-sm-12">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-lock  fa-fw"></i></span>
										<input class="form-control " placeholder="Confirm password" type="password" name="confirmPassword" id="confirmPassword">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">			
									<label class="checkbox-inline">
										 <input type="checkbox" class="checkbox" name="terms" id="terms">
										 <span><?php echo _("I agree with the")?> <a href="#" data-toggle="modal" data-target="#termsConditionModal"> <?php echo _("Terms & Conditions")?> </a></span>
									</label>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="tab-pane" id="printer-tab">
					<br>
					<h3><strong><?php echo _("Step")?> 3 </strong> - <?php echo _("Printer")?></h3>
					<form id="printer-form">
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label><?php echo _("Assign a name to your FABtotum"); ?></label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-play fa-rotate-90  fa-fw"></i></span>
										<input class="form-control " placeholder="<?php echo _("I'd like to have a name");?>" type="text" name="unit_name" id="unit_name">
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label><?php echo _("Insert serial number"); ?></label>
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-play fa-key  fa-fw"></i></span>
										<input class="form-control uppercase" data-mask="*****-***-*****" data-mask-placeholder= "_" type="text" name="serial_number" id="serial_number">
									</div>
									<p class="note"><?php echo _("The unit's serial number can be found on the back cover."); ?></p>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-12">
								<div class="form-group">
									<label><?php echo _("What color is your FABtotum ")?> </label>
									<div class="icon-addon addon-md">
					                    <?php echo colors_menu('form-control', 'unit_color', 'id="unit_color"');?>
					                    <label class="fa fa-tint"></label>
					                </div>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="tab-pane" id="network-tab">
					<br>
					<h3><strong><?php echo _("Step")?> 4 </strong> - <?php echo _("Network")?> 
						<button id="wifi-scan" class="btn btn-default btn-xs pull-right"><i class="fa fa-search"></i> <span class="hidden-xs"><?php echo _("Scan");?></span></button>
					</h3>
					<div id="wlan-table-container" style="height: 300px; overflow:auto;"></div>
				</div>
				<div class="tab-pane" id="finish-tab">
					<br>
					<br>
					<div class="row">
						<div class="col-sm-12">
							<div class="row margin-top-10">
								<p class="font-md text-center">
									<?php echo _("You're almost done.")?>
									<br>
									<?php echo _("Click <strong>Install</strong> to complete")?>
								</p>
								<!--  
								<p class="text-center">
									<label class="checkbox-inline">
										<input type="checkbox" class="checkbox" name="samples" id="samples" checked>
										<span><?php echo _("Install gcode samples")?> </span>
									</label>
								</p>
								-->
							</div>
						</div>
					</div>

				</div>
				<div class="form-actions">
					<div class="row">
						<div class="col-sm-12">
							<ul class="pager wizard no-margin">
								<li class="previous disabled">
									<a href="javascript:void(0);" class="btn btn-lg btn-default"> <?php echo _("Prev")?> </a>
								</li>
								<li class="next">
									<a href="javascript:void(0);" class="btn btn-lg txt-color-darken"> <?php echo _("Next")?>  </a>
								</li>
							</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="install-animation" style="display:none;" class="col-sm-12 install-animation">
		<div class="text-center">
			<h1 class="font-xl"><span><i class="fa fa-cog fa-spin fa-2x"></i></span></h1>
			<h2><?php echo _("Installation in progress"); ?></h2>
			<p class="lead semi-bold"><?php echo _("This may take a while, please wait"); ?></p>
		</div>
	</div>
</div>
<!-- LANGUAGE FORM -->
<form method="post" id="locale-form"><input type="hidden" name="locale" id="locale" value=""></form>
<!-- END LANGUAGE FORM -->
<!-- TERMS & CONDITIONS MODAL -->
<div class="modal fade" id="termsConditionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title" id="myModalLabel"><?php echo _("Terms & Conditions")?></h4>
		</div>
			<div class="modal-body custom-scroll terms-body">
				<div><?php echo termsAndConditions(); ?></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _("Cancel")?></button>
				<button type="button" class="btn btn-primary" id="i-agree"><i class="fa fa-check"></i> <?php echo _("I agree")?></button>
				<button type="button" class="btn btn-danger pull-left" id="print"><i class="fa fa-print"></i> <?php echo _("Print")?></button>
			</div>
		</div>
	</div>
</div>
<!-- END TERMS & CONDITIONS MODAL -->
<!-- PASSWORD MODAL -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><i class="fa fa-lock"></i> <span id="passwordModalTitle"></span> <i class="fa fa-wifi"></i></h4>
			</div>
			<div class="modal-body custom-scroll " id="progressModalBody">
				<form class="smart-form" id="passwordModalForm" onsubmit="return false;">
					<fieldset>
						<section>
							<label class="input"> <i class="icon-prepend fa fa-lock"></i>
								<input type="password" data-inputmask-regex="[-_a-z A-Z0-9$@^`,|%;.~()/\{}:?\[\]=+_#!\'\*]*" class="input-password password" placeholder="insert password" id="wifiPassword" name="wifiPassword">
							</label>
						</section>
						<section>
							<label class="checkbox">
								<input type="checkbox" data-attribute="modal" class="show-password"> <i></i> <?php echo _('Show password')?>
							</label>
						</section>
					</fieldset>
					<input type="hidden" id="wifiPasswordMinLength">
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel')?></button>
				<button type="button" class="btn btn-primary" id="modalConnectButton"><i class="fa fa-check"></i> <?php echo _('Connect')?> </button>
			</div>
		</div>
	</div>
</div>
<form id="hiddenWifiForm" style="display:none;">
	<fieldset>
		<input type="text" id="address-mode"        name="address-mode" value="dhcp">
		<input type="text" id="hidden-ssid"         name="hidden-ssid" value="">
		<input type="text" id="hidden-bssid"        name="hidden-bssid" value="">
		<input type="text" id="hidden-passphrase"   name="hidden-passphrase" value="">
		<input type="text" id="hidden-psk"          name="hidden-psk" value="8e0f596ccbeb3fff85a4bbb14f193fecc1ca55a471df45a84df1b8f4ec33d426">
	</fieldset>
</div>
