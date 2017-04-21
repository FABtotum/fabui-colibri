<?php
/**
 * 
 * @author Krios Mane
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<div class="modal fade" id="dropzone-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h4 class="modal-title"><i class="fa fa-cube"></i> <span class="dropzone-file-name"></span></h4>
			</div>
			<div class="modal-body">
				<div class="row">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" id="dropzone-cancel" class="btn btn-default" data-dismiss="modal"><?php echo _("Cancel");?></button>
				<button type="button" id="dropzone-make"  class="btn btn-primary"><?php echo $type_action; ?></button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->