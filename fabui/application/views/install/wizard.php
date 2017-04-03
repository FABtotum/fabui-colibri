<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<form class="lockscreen animated flipInY" method="POST" action="<?php echo site_url('install/do');?>" id="install-form">
	<input type="hidden" name="browser-date" id="browser-date" />
	<div class="logo text-center">
		<img src="/assets/img/fabui_v1.png">
	</div>
	
	
		<div id="bootstrap-wizard-1" class="col-sm-12">
			<div class="form-bootstrapWizard">
				<ul class="bootstrapWizard form-wizard">
					<li class="active" data-target="#welcome-tab">
						<a href="#welcome-tab" data-toggle="tab"> <span class="step">1</span> <span class="title"><?php echo _("Welcome")?></span> </a>
					</li>
					<li data-target="#account-tab">
						<a href="#account-tab" data-toggle="tab"> <span class="step">2</span> <span class="title"><?php echo _("Account")?></span> </a>
					</li>
					<li data-target="#printer-tab">
						<a href="#printer-tab" data-toggle="tab"> <span class="step">3</span> <span class="title"><?php echo _("Printer")?></span> </a>
					</li>
					<li data-target="#settings-tab">
						<a href="#settings-tab" data-toggle="tab"> <span class="step">4</span> <span class="title"><?php echo _("Settings")?></span> </a>
					</li>
					<li data-target="#finish-tab">
						<a href="#finish-tab" data-toggle="tab"> <span class="step">5</span> <span class="title"><?php echo _("Finish")?></span> </a>
					</li>
				</ul>
				<div class="clearfix"></div>
			</div>
			<div class="tab-content">
				<div class="tab-pane active" id="welcome-tab">
					<br>
					<h3><strong><?php echo _("Step")?> 1 </strong> - <?php echo _("Welcome")?></h3>
					<div class="row">
						<div class="col-sm-12">
							<p class="font-md text-center"><?php echo _("Welcome to the installation wizard of the FABtotum User Interface")?>. 
							<?php echo _("Follow the steps and enter the data as prompted")?></p>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="account-tab">
					<br>
					<h3><strong><?php echo _("Step")?> 2 </strong> - <?php echo _("Create your personal account")?></h3>
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
				</div>
				<div class="tab-pane" id="printer-tab">
					<br>
					<h3><strong><?php echo _("Step")?> 3 </strong> - <?php echo _("Printer")?></h3>
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
				</div>
				<div class="tab-pane" id="settings-tab">
					<br>
					<h3><strong><?php echo _("Step")?> 4 </strong> - <?php echo _("Settings")?></h3>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label><?php echo _("Select your language")?> </label>
								<div class="icon-addon addon-md">
				                    <?php echo langauges_menu('form-control', 'language', 'id="language"');?>
				                    <label class="fa fa-flag"></label>
				                </div>
							</div>
						</div>
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
				<div class="tab-pane" id="finish-tab">
					<br>
					<br>
					<div class="row margin-top-10">
						<div class="col-sm-12">
							<p class="font-md text-center"><?php echo _("You're almost done.")?><br><?php echo _("Click <strong>Install</strong> to complete")?></p>
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
</form>
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
	
