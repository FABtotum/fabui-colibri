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
<p class="lead"><?php echo pyformat(_('this e-mail is to inform you that the last {0} you started was just paused.'), array($task['type'])); ?></p>
<p><?php echo _("If you paused the task please ignore this email");?></p>
