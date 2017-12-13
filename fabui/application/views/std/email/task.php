<?php
/**
 *
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 *
 */
?>
<h3>Hi, <?php echo $user['first_name'];?> <?php echo $user['last_name'];?></h3>
<p class="lead"><?php echo pyformat(_('this e-mail is to inform you that the last {0} you started was just {1}.'), array($task['type'], $task['status'])); ?></p>
<!-- Callout Panel -->
<div class="callout">
	<dl class="dl-horizontal">
		<dt><?php echo _("File");?></dt>
		<dd><?php echo $file['client_name'];?></dd>

		<dt><?php echo _("Project");?></dt>
		<dd><?php echo $project['name'];?></dd>

		<dt><?php echo _("Started on");?></dt>
		<dd><?php echo $task['start_date'];?></dd>

		<dt><?php echo _("Finished on");?></dt>
		<dd><?php echo $task['finish_date'];?></dd>

		<dt><?php echo _("Duration");?></dt>
		<dd><?php echo $task_duration;?></dd>
	</dl>
</div>