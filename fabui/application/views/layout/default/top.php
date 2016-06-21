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
		 <span id="logo"> <img src="/assets/img/logo-0.png"></span>
		<!-- END LOGO PLACEHOLDER -->
		<span id="activity" class="activity-dropdown"> <i class="fa fa-user"></i> <b class="badge"> 0 </b> </span>
		<!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
		<div class="ajax-dropdown">
			<!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
			<div class="btn-group btn-group-justified" data-toggle="buttons">
				<label class="btn btn-default">
					<input type="radio" name="activity" id="/ajax/notify/mail.php">
					Msgs (14) </label>
				<label class="btn btn-default">
					<input type="radio" name="activity" id="/ajax/notify/notifications.php">
					notify (3) </label>
				<label class="btn btn-default">
					<input type="radio" name="activity" id="/ajax/notify/tasks.php">
					Tasks (4) </label>
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
			<span> Last updated on: 12/12/2013 9:43AM
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
				<div class="">
					<!-- left column -->
					<div class="btn-group-vertical">
						<button type="button" class="btn btn-default btn-circle btn-xl top-directions" data-attribute-direction="up-left"><i class="fa fa-arrow-left fa-1x fa-rotate-45"></i></button>
						<button type="button" class="btn btn-default btn-circle btn-xl top-directions" data-attribute-direction="left"><i class="fa fa-arrow-left fa-1x"></i></button>
						<button type="button" class="btn btn-default btn-circle btn-xl top-directions" data-attribute-direction="down-left"><i class="fa fa-arrow-down fa-1x fa-rotate-45"></i></button>
					</div>
					<!-- center column -->
					<div class="btn-group-vertical">
						<button type="button" class="btn btn-default btn-circle btn-xl top-directions" data-attribute-direction="up"><i class="fa fa-arrow-up fa-1x"></i></button>
						<button type="button" class="btn btn-default btn-circle btn-xl top-directions" data-attribute-direction=""></button>
						<button type="button" class="btn btn-default btn-circle btn-xl top-directions" data-attribute-direction="down"><i class="fa fa-arrow-down fa-1x"></i></button>
					</div>
					<!-- right column -->
					<div class="btn-group-vertical">
						<button type="button" class="btn btn-default btn-circle btn-xl top-directions" data-attribute-direction="up-right"><i class="fa fa-arrow-up fa-1x fa-rotate-45"></i></button>
						<button type="button" class="btn btn-default btn-circle btn-xl top-directions" data-attribute-direction="right"><i class="fa fa-arrow-right fa-1x"></i></button>
						<button type="button" class="btn btn-default btn-circle btn-xl top-directions" data-attribute-direction="down-right"><i class="fa fa-arrow-right fa-1x fa-rotate-45"></i></button>
					</div>
					<div class="btn-group-vertical text-center">
						<button type="button" class="btn btn-default btn-circle btn-xl top-axisz" data-attribute-function="moveZ" data-attribute-value="up"><i class="fa fa-arrow-up fa-1x "></i></button>
						<span>Z</span>
						<button type="button" class="btn btn-default btn-circle btn-xl top-axisz" data-attribute-function="moveZ" data-attribute-value="down"><i class="fa fa-arrow-down fa-1x"></i></button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- END JOG SHORTCUT BUTTONS -->
	<!-- TEMPERATURES CONTROL BUTTONS -->
	<div class="pull-right pad-container hidden-xs" style="position: relative;border:0px;padding-right:0px;">
		<div class="btn-header transparent">
			<span id="top-temperatures">
				<a href="javascript:void(0)" style="cursor: pointer !important;" rel="tooltip" data-placement="left" data-html="true" data-original-title="Temperatures<br> controls">
					<i class="icon-fab-term"></i> N: <span id="top-bar-nozzle-actual"></span>/<span id="top-bar-nozzle-target"></span> - B: <span id="top-bar-bed-actual"></span>/<span id="top-bar-bed-target"></span>&nbsp;
				</a>
			</span>
			<div class="top-ajax-temperatures-dropdown">
					<h4><i class=" icon-fab-term"></i> Nozzle</h4>
					<div id="top-act-ext-temp"  class="noUiSlider top-act-ext-temp"></div>
					<div id="top-ext-target-temp" class="noUiSlider top-ext-target-temp top-extruder-range"></div>
					<hr class="simple margin-top-60">
					
					<h4 class="margin-top-10"><i class=" icon-fab-term"></i> Bed</h4>
					<div id="top-act-bed-temp" class="noUiSlider top-act-bed-temp"></div>
					<div id="top-bed-target-temp" class="noUiSlider top-bed-target-temp top-bed-range"></div>
				</div>
		</div>
	</div>
	<!-- END TEMPERATURES CONTROL BUTTONS -->	
	<!-- end pulled right: nav area -->
</header>