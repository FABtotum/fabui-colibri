<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<form class="lockscreen animated flipInY" method="POST" action="<?php echo site_url('install/restore');?>" id="restore-form">
	<input type="hidden" name="browser-date" id="browser-date" />
	<div class="logo text-center">
		<img src="/assets/img/fabtotum_logo.png">
	</div>
	
	
		<div id="bootstrap-wizard-1" class="col-sm-12">
			<div class="form-bootstrapWizard">
				<ul class="bootstrapWizard form-wizard">
					<li class="active" data-target="#welcome-tab">
						<a href="#welcome-tab" data-toggle="tab"> <span class="step">1</span> <span class="title">Welcome</span> </a>
					</li>
					<li data-target="#restore-tab">
						<a href="#restore-tab" data-toggle="tab"> <span class="step">3</span> <span class="title">Restore</span> </a>
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
							<p class="font-md text-center">Welcome to the restore wizard of the FABtotum User Interface. 
							Follow the steps and select the data to be restored.</p>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="restore-tab">
					<br>
					<h3><strong>Step 3 </strong> - Restore</h3>

					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">			
								<label class="checkbox-inline" title="Restore all previously uploaded and created objects and files.">
									 <input type="checkbox" class="checkbox" name="user_files" id="user_files" checked>
									 <span>User Objects and Files</span>
								</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">			
								<label class="checkbox-inline" title="Restore all previously uploaded and created objects and files.">
									 <input type="checkbox" class="checkbox" name="hardware_settings" id="hardware_settings" checked>
									 <span>Hardware Settings</span>
								</label>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">			
								<label class="checkbox-inline"  title="Restore all previous task data.">
									 <input type="checkbox" class="checkbox" name="task_history" id="task_history" checked>
									 <span>Task History</span>
								</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">			
								<label class="checkbox-inline" title="Restore all previously uploaded and created objects and files.">
									 <input type="checkbox" class="checkbox" name="head_settings" id="head_settings" checked>
									 <span>Head Settings</span>
								</label>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">			
								<label class="checkbox-inline"  title="Restore Network settings including WiFi passwords.">
									 <input type="checkbox" class="checkbox" name="network_settings" id="network_settings" checked>
									 <span>Network settings</span>
								</label>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">			
								<label class="checkbox-inline" title="Restore all previously installed plugins.">
									 <input type="checkbox" class="checkbox" name="plugins" id="plugins" checked>
									 <span>Plugins</span>
								</label>
							</div>
						</div>
					</div>

				</div>
				<div class="tab-pane" id="finish-tab">
					<br>
					<br>
					<div class="row margin-top-10">
						<div class="col-sm-12">
							<p class="font-md text-center">You're almost done.<br>Click <strong>restore</strong> to complete</p>
						</div>
					</div>
				</div>
				<div class="form-actions">
					<div class="row">
						<div class="col-sm-12">
							<ul class="pager wizard no-margin">
								<li class="previous disabled">
									<a href="javascript:void(0);" class="btn btn-lg btn-default wizard-button"> Previous </a>
								</li>
								<li class="next">
									<a href="javascript:void(0);" class="btn btn-lg txt-color-darken wizard-button"> Next </a>
								</li>
							</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
