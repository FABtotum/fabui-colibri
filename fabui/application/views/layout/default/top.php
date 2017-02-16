<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<header id="header">
	<div id="logo-group">
		<!-- PLACE YOUR LOGO HERE -->
		 <span id="logo"> <img src="/assets/img/logo/fabui_6.png"></span>
		<!-- END LOGO PLACEHOLDER -->
		<span id="activity" class="activity-dropdown"> <i class="fa fa-user"></i> <b class="badge"> 0 </b> </span>
		<!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
		<div class="ajax-dropdown">
			<!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
			<div class="btn-group btn-group-justified" data-toggle="buttons">
				<label class="btn btn-default"><input type="radio" name="activity" id="/fabui/updates/notifications">Updates <span class="updates-number"></span></label>
				<label class="btn btn-default"><input type="radio" name="activity" id="/fabui/control/notifications">Notify</label>
				<label class="btn btn-default"><input type="radio" name="activity" id="/fabui/control/runningTasks">Tasks <span class="tasks-number"></span></label>
			</div>
			<!-- notification content -->
			<div class="ajax-notifications custom-scroll">
				<div class="alert alert-transparent">
					<h4>Click a button to show messages here</h4>
					This blank page message helps protect your privacy, or you can show the first message here automatically.
				</div>
				<i class="fa fa-lock fa-4x fa-border"></i>
			</div>
			<!-- end notification content -->
			<!-- footer: refresh area -->
			<span> Last updated on: <span class="last-update-time"></span>
				<button type="button" data-loading-text="<i class='fa fa-refresh fa-spin'></i> Loading..." class="btn btn-xs btn-default pull-right">
					<i class="fa fa-refresh"></i>
				</button> </span>
			<!-- end footer -->
		</div>
		<!-- END AJAX-DROPDOWN -->
	</div>
	<!-- pulled right: nav area -->
	<div class="pull-right top-bar">
		<!-- collapse menu button -->
		<div id="hide-menu" class="btn-header pull-right">
			<span> <a href="javascript:void(0);" title="Collapse Menu" data-action="toggleMenu"><i class="fa fa-reorder"></i></a> </span>
		</div>
		<!-- end collapse menu -->
		<!-- logout button -->
		<div id="logout" class="btn-header transparent pull-right">
			<span> <a href="<?php echo site_url('login/out') ?>" title="Power Off/Log Out" data-action="fabUserLogout" data-logout-msg="What do you want to do?" ><i class="fa fa-power-off"></i></a> </span>
		</div>
		<!-- reset controller button -->
		<div  class="btn-header transparent pull-right">
			<span> <a href="javascript:void(0)"  data-action="resetController" data-reset-msg="This button will reset control board, continue?" rel="tooltip" data-placement="left" data-html="true" data-original-title="Reset Controller.<br>This will reset control board"><i class="fa fa-bolt"></i></a> </span>
		</div>
		<!-- end reset controller button -->
		<!-- emergency button -->
		<div  class="btn-header transparent pull-right">
			<span> <a href="javascript:void(0)"  data-action="emergencyButton" data-reset-msg="his button will stop all the operations, continue?" rel="tooltip" data-placement="left" data-html="true" data-original-title="Emergency Button. <br>This will stop all operations on the FABtotum"><i class="fa fa-warning"></i></a> </span>
		</div>
		<!-- end emergency button -->
	</div>
	<!-- JOG SHORTCUT BUTTONS -->
	<div class="pull-right pad-container hidden-xs" style="position: relative;">
		<div class="btn-header transparent">
			<span id="jog-shortcut">
				<a href="javascript:void(0)" style="cursor: pointer !important;" title="Jog" rel="tooltip" data-placement="left" data-html="true" data-original-title="Jog"><i class="fa fa-gamepad"></i></a>
			</span>
			<div class="top-ajax-jog-dropdown">

					<!-- jog controls placeholder -->
					<div class="top-ajax-jog-controls-holder"></div>

			</div>
		</div>
	</div>
	<!-- END JOG SHORTCUT BUTTONS -->
	<!-- TEMPERATURES CONTROL BUTTONS -->
	<div class="pull-right pad-container hidden-xs" style="position: relative;border:0px;padding-right:0px;">
		<div class="btn-header transparent">
			<span id="top-temperatures">
				<a href="javascript:void(0)" style="cursor: pointer !important;" rel="tooltip" data-placement="left" data-html="true" data-original-title="Temperatures<br> controls">
					<i class="icon-fab-term"></i>  
					<span class="hidden-xs" id="top-temperatures-info"><strong>N</strong>: <span class="top-bar-nozzle-actual font-md"></span> - <strong>B</strong>: <span class="top-bar-bed-actual font-md" ></span>&nbsp;</span>
				</a>
			</span>
			<div class="top-ajax-temperatures-dropdown">
					<h4><i class=" icon-fab-term"></i> Nozzle  <small> (<span class="top-bar-nozzle-actual"></span> /<span class="top-bar-nozzle-target"></span> &deg; )</small></h4>
					<div id="top-act-ext-temp"  class="noUiSlider top-act-ext-temp"></div>
					<div id="top-ext-target-temp" class="noUiSlider top-ext-target-temp top-extruder-range"></div>
					<h4 class="margin-top-50"><i class=" icon-fab-term"></i> Bed <small>( <span class="top-bar-bed-actual" ></span>/<span class="top-bar-bed-target" ></span> &deg;)</small></h4>
					<div id="top-act-bed-temp" class="noUiSlider top-act-bed-temp"></div>
					<div id="top-bed-target-temp" class="noUiSlider top-bed-target-temp top-bed-range"></div>
				</div>
		</div>
	</div>
	<!-- END TEMPERATURES CONTROL BUTTONS -->
	<!-- end pulled right: nav area -->
</header>
