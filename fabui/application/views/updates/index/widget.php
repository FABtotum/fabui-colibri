<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<div class="row">
	<div class="col-sm-12">
		<div class="text-center  update-box">
			<h1>
				<span class="fabtotum-icon">
					<i class="fa fa-play fa-rotate-90 fa-border border-black fa-4x"></i>
					<span><b class="badge"></b></span>
				</span>
			</h1>
			<h4 class="status"></h4>
			<p class="lead"><small class="small"></small></p>
			<div class="button-container"></div>
			
			<div class="tabs-container margin-top-10 text-align-left" style="display:none;">
				<ul id="updateTab" class="nav nav-tabs bordered">
					<li class="active"><a href="#bundles_tab" data-toggle="tab"><i class="fa fa-lg fa-puzzle-piece"></i> Bundles <span id="bundles-badge" class="badge bg-color-red txt-color-white inbox-badge"></span></a></li>
					<li><a href="#boot_tab" data-toggle="tab"><i class="fa fa-lg fa-rocket"></i> Boot <span id="boot-badge" class="badge bg-color-red txt-color-white inbox-badge"></span></a></li>
					<li><a href="#firwmare_tab" data-toggle="tab"><i class="fa fa-lg fa-microchip"></i> Firmware <span id="firmware-badge" class="badge bg-color-red txt-color-white inbox-badge"></span></a></li>
				</ul>
				<div id="updateTabContent" class="tab-content padding-10">
					<div class="tab-pane fade in active" id="bundles_tab"></div>
					<div class="tab-pane fade in" id="boot_tab"></div>
					<div class="tab-pane fade in" id="firwmare_tab"></div>
				</div>
			</div>
			<div class="update-details margin-top-10 text-align-left" style="display:none;"></div>			
		</div>
	</div>
</div>
