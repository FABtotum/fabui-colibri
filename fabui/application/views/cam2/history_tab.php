<?php
/**
 * 
 * @author FABteam
 * @version 0.0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<!-- CAM HISTORY TAB -->

<div class="tab-pane fade in active" id="cam-history-tab">
	<div id="history-container" class="row">
		<div class="col-sm-12">
			<h5><?php echo _("Task history");?></h5>
			<table class="table table-bordered" id="history-table">
				
				<thead>
					<tr>
						<th width="80"><?php echo _("Task ID");?></th>
						<th><?php echo _("Status");?></th>
						<th><?php echo _("Application");?></th>
						<th><?php echo _("File"); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($cam['tasks'] as $task): ?>
					<tr>
						<td><?php echo $task['id']; ?></td>
						<td><?php echo $task['status']; ?></td>
						<td><?php echo $task['application']; ?></td>
						<td><?php echo $task['file']['input']['filename']; ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<!-- CAM HISTORY TAB -->
