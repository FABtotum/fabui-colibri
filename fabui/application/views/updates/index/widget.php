<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="row" id="first-row">
	<div class="col-sm-3">
		<div class="text-center">
			<h6 id="status"></h6>
			<h1 class=" animated">
				<span style="position: relative;">
					<i class="fa fa-play fa-rotate-90 fa-border border-black fa-4x"></i>
					<span><b class="badge fabtotum-badge font-md"><span id="badge-icon"></span> </b></span>
				</span>
			</h1>
		</div>
	</div>
	<div class="col-sm-9">
		<h3 id="response"></h3>
		<div id="pre-update-button-container">
			<a class="btn btn-default" href="javascript:void(0);" id="check-again">Check again</a>
			<a class="btn btn-default" href="javascript:void(0);" id="update">Update</a>
			<a class="btn btn-default" href="javascript:void(0);" id="details-button">show details <i class="fa fa-angle-double-down"></i></a>
		</div>
		<div id="update-button-container" style="display:none;">
			<a class="btn btn-default" href="javascript:void(0);" id="abort-update">Cancel</a>
			<a class="btn btn-default" href="javascript:void(0);" id="update-button">show details <i class="fa fa-angle-double-down"></i></a>
		</div>
		<div class="details margin-top-10" style="display:none;">
		</div>
	</div>
</div>
<!-- changelog modal -->
<div class="modal fade" id="changelog-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h4 class="modal-title" id="changelog-modal-title"></h4>
			</div>
			<div class="modal-body" id="changelog-modal-body" style="white-space: pre;">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>