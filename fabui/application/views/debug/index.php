<div class="tab-content padding-10">
	
	<div class="tab-pane fade in active" id="monitor-tab">
		<div id="task_monitor"></div>
	</div>
	<div class="tab-pane fade in" id="temperatures-tab">
		<div id="temperatures"></div>
	</div>
	<div class="tab-pane fade in" id="notify-tab">
		<div id="notify"></div>
	</div>
	<div class="tab-pane fade in" id="trace-tab">
		<pre id="trace"></pre> 
	</div>
	<div class="tab-pane fade in" id="settings-tab">
		<div class="row">
			<div class="col-sm-12">
				<button class="btn btn-default" id="save-json-settings"><i class="fa fa-save"></i> Save</button>
				
				<button class="btn btn-default" id="restore-json-settings"><i class="fa fa-refresh"></i> Restore</button>
			</div>
		</div>
		<div class="row margin-top-10">
			<div class="col-sm-12">
				<div id="settings-json"></div>
			</div>
		</div>
	</div>
	<div class="tab-pane fade in" id="json-rpc-tab">
		<div class="row">
			<div class="col-sm-12">
				<ul>
					<li><a href="javascript:void(0);" class="json-rpc" data-action="fab_register_printer">fab_register_printer</a></li>
					<li><a href="javascript:void(0);" class="json-rpc" data-action="fab_info_update">fab_info_update</a></li>
					<li><a href="javascript:void(0);" class="json-rpc" data-action="fab_polling">fab_polling</a></li>
				</ul>
			</div>
		</div>
		<div id="json-rpc-result"></div>
	</div>

</div>