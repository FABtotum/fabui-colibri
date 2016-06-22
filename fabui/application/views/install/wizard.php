<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<form class="lockscreen animated flipInY" action="/index.php" id="wizard-1">
	<div class="logo">
		<h1 class="semi-bold"> FABTOTUM</h1>
	</div>
	
		<div id="bootstrap-wizard-1" class="col-sm-12">
			<div class="form-bootstrapWizard">
				<ul class="bootstrapWizard form-wizard">
					<li class="active" data-target="#welcome-tab">
						<a href="#welcome-tab" data-toggle="tab"> <span class="step">1</span> <span class="title">Welcome</span> </a>
					</li>
					<li data-target="#account-tab">
						<a href="#account-tab" data-toggle="tab"> <span class="step">2</span> <span class="title">Account</span> </a>
					</li>
					<li data-target="#settings-tab">
						<a href="#settings-tab" data-toggle="tab"> <span class="step">3</span> <span class="title">Settings</span> </a>
					</li>
					<li data-target="#finish-tab">
						<a href="#finish-tab" data-toggle="tab"> <span class="step">4</span> <span class="title">Finish</span> </a>
					</li>
				</ul>
				<div class="clearfix"></div>
			</div>
			<div class="tab-content">
				<div class="tab-pane active" id="welcome-tab">
					<br>
					<h3><strong>Step 1 </strong> - Welcome</h3>
					<div class="row">
						<div class="col-sm-12">
							<p class="font-md text-center">Welcome to the installation wizard of the FABtotum User Interface. 
							Follow the steps and enter the data as promted</p>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="account-tab">
					<br>
					<h3><strong>Step 2 </strong> - Create your personal account</h3>
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-user  fa-fw"></i></span>
									<input class="form-control " placeholder="First name" type="text" name="first_name" id="first_name">
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-user  fa-fw"></i></span>
									<input class="form-control " placeholder="Last name" type="text" name="last_name" id="last_name">
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
				</div>
				<div class="tab-pane" id="settings-tab">
					<br>
					<h3><strong>Step 3 </strong> - Settings</h3>
				</div>
				<div class="tab-pane" id="finish-tab">
					<br>
					<h3><strong>Step 4 </strong> - Complete</h3>
				</div>
				<div class="form-actions">
					<div class="row">
						<div class="col-sm-12">
							<ul class="pager wizard no-margin">
								<li class="previous disabled">
									<a href="javascript:void(0);" class="btn btn-lg btn-default"> Previous </a>
								</li>
								<li class="next">
									<a href="javascript:void(0);" class="btn btn-lg txt-color-darken"> Next </a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	