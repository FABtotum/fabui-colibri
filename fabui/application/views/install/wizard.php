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
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">			
								<label class="checkbox-inline">
									 <input type="checkbox" class="checkbox" name="terms" id="terms">
									 <span>I agree with the <a href="#" data-toggle="modal" data-target="#termsConditionModal"> Terms and Conditions </a></span>
								</label>
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
					<br>
					<div class="row margin-top-10">
						<div class="col-sm-12">
							<p class="font-md text-center">You're almost done.<br>Click <strong>install</strong> to complete</p>
						</div>
					</div>
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
</form>
<!-- TERMS & CONDITIONS MODAL -->
<div class="modal fade" id="termsConditionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Terms & Conditions</h4>
			</div>
				<div class="modal-body custom-scroll terms-body">
					<div>
						<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam non justo nec orci bibendum eleifend id eget ex. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Curabitur scelerisque luctus orci. Aliquam nec dolor sed nunc placerat aliquam. Suspendisse condimentum convallis leo eu convallis. Phasellus nec tempus elit, a luctus metus. Proin vel bibendum urna, in mattis turpis. Integer varius eget justo at luctus. Vestibulum consequat arcu dolor, quis pretium dolor porttitor id. Praesent sed arcu ante. Cras sed purus ornare, varius dui a, commodo justo.

Proin sagittis convallis tortor, non dictum leo sodales sed. Aliquam erat volutpat. Nunc nec interdum est. Donec nec pharetra nibh, ac sodales elit. Donec nec ultricies elit. Mauris sit amet elementum sem, sed volutpat elit. Praesent facilisis turpis aliquet, congue mi at, scelerisque tellus. Proin egestas nisl vel iaculis egestas. Pellentesque accumsan id leo tincidunt volutpat. Proin in felis magna. Mauris elit turpis, porttitor ut mollis eget, malesuada fringilla quam. Fusce sit amet ante metus. Integer quis nunc vitae est consectetur convallis nec non nulla. Fusce cursus blandit odio et porttitor. Mauris dictum, nunc quis dapibus egestas, enim ante tempus felis, non suscipit ligula augue sed nisl. Vivamus non lacinia dolor.

Nam sit amet mauris purus. Cras malesuada lacus vel molestie pretium. Suspendisse nec maximus dolor. Integer ex lacus, mattis eget ullamcorper quis, cursus nec augue. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Phasellus gravida bibendum felis, quis venenatis lectus condimentum id. Maecenas rutrum malesuada erat. Curabitur sed turpis ac libero sodales aliquet. Phasellus rhoncus vel arcu ac aliquet. Proin rhoncus nisl arcu. Donec consequat diam eu orci elementum, ut dapibus leo tempus.

Mauris posuere massa et lectus malesuada, in varius enim volutpat. Vivamus pharetra, odio eget consequat vestibulum, orci metus rutrum felis, non sollicitudin neque dolor at mauris. Praesent molestie tristique augue ut ultrices. Maecenas ut metus ut tellus accumsan varius vitae id sem. Fusce elementum velit justo, sit amet sagittis elit lacinia eget. Suspendisse mattis porta lorem id malesuada. Phasellus sed libero efficitur, semper est at, hendrerit sem. Aliquam semper magna neque, congue mollis magna venenatis et. Maecenas iaculis non sapien non dictum. Etiam convallis, libero vitae pulvinar sodales, orci erat aliquet purus, ut condimentum dui erat ut risus. Morbi in urna bibendum, laoreet urna quis, viverra metus. Curabitur quis eros id diam molestie faucibus. Etiam tempor lobortis fringilla.

Donec lacus velit, lobortis vel elementum et, commodo quis mauris. Vivamus efficitur, urna a rhoncus commodo, mauris ante accumsan lacus, in imperdiet urna felis eget purus. Integer semper augue eu lorem lobortis, ac pulvinar ex maximus. Pellentesque fermentum eleifend cursus. Cras gravida dignissim nulla, quis dictum mauris cursus in. Aliquam est nulla, suscipit sed posuere porttitor, feugiat nec ex. Nulla scelerisque sed turpis sed aliquam. Nunc lectus lorem, tempor at pretium ac, eleifend nec nibh. Maecenas hendrerit malesuada ex, at condimentum lorem ultricies vel. Phasellus elementum sapien eget diam venenatis, nec lobortis nisl dictum. Nulla dignissim condimentum lorem, at interdum mi ultricies quis. Sed sem risus, dignissim sit amet molestie a, bibendum mollis nulla.</p>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">
						Cancel
					</button>
					<button type="button" class="btn btn-primary" id="i-agree">
						<i class="fa fa-check"></i> I Agree
					</button>
					
					<button type="button" class="btn btn-danger pull-left" id="print">
						<i class="fa fa-print"></i> Print
					</button>
				</div>
			</div>
		</div>
	</div>
	