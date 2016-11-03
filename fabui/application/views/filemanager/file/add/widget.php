<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<form id="object-form" method="post" class="smart-form" action="<?php echo site_url('filemanager/saveObject') . '/' . $object_id; ?>">
    <input type="hidden" name="filesID" id="filesID">
</form>
<!-- uploads row -->
<div class="row">
	<div class="col-sm-12">
		<ul id="myTab1" class="nav nav-tabs">
			<li class="active">
				<a href="#dropzone-tab" data-toggle="tab">Dropzone</a>
			</li>
			<li>
				<a href="#usb-tab" data-toggle="tab">Usb Disk</a>
			</li>
		</ul>
		<div id="myTabContent1" class="tab-content padding-10">
			<div class="tab-pane fade in active" id="dropzone-tab">
				<div id="newObjectDropzone" class="dropzone"></div>
			</div>
			<div class="tab-pane fade in" id="usb-tab">
			</div>
		</div>
	</div>
</div>
<!-- PROGRESS MODAL -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLabel"><i class="fa fa-upload"></i> Upload progress</h4>
			</div>
			<div class="modal-body custom-scroll " id="progressModalBody">
			</div>	
		</div>
	</div>
</div>
