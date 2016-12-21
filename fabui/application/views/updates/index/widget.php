<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<div class="row margin-bottom-10">
	<div class="col-sm-12">
		<div class="status"></div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12 col-sx-12">
		<ul class="demo-btns">
			<li><button class="btn btn-default  action-buttons" id="check-again"> Check again</button></li>
			<li><button class="btn btn-default  action-buttons" id="do-update"><i class="fa fa-refresh"></i> Update</button></li>
			<li><button class="btn btn-default  action-buttons" id="do-abort"><i class="fa fa-times"></i> Cancel</button></li>
		</ul>
	</div>
</div>
<hr class="simple">
<div class="row">
	<div class="col-sm-12">
		<!-- TAB -->
		<ul id="updateTab" class="nav nav-tabs bordered">
			<li class="active"><a href="#bundles_tab" data-toggle="tab"><i class="fa fa-lg fa-puzzle-piece"></i> Bundles <span id="bundles-badge" class="badge bg-color-red txt-color-white inbox-badge"></span></a></li>
			<li><a href="#boot_tab" data-toggle="tab"><i class="fa fa-lg fa-rocket"></i> Boot</a></li>
			<li><a href="#firwmare_tab" data-toggle="tab"><i class="fa fa-lg fa-microchip"></i> Firmware</a></li>
		</ul>
		<div id="updateTabContent" class="tab-content padding-10">
			<div class="tab-pane fade in active" id="bundles_tab"></div>
			<div class="tab-pane fade in" id="boot_tab"></div>
			<div class="tab-pane fade in" id="firwmare_tab"></div>
		</div>
		<!-- ENDTAB -->
	</div>
</div>