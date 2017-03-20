<?php
/**
 * 
 * @author Krios Mane
 * @author Fabtotum Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<?php if(isset($feeds) && count($feeds)>0): ?>
<div class="row">
	<?php if(count($feedsB) > 0): ?>
	<div class="col-sm-6 col-xs-6">
		<?php foreach($feedsB as $feed):?>
			<?php echo displayInstagramFeedItem($feed);?>
		<?php endforeach;?>
	</div>
	<?php endif; ?>
	<?php if(count($feedsA) > 0): ?>
	<div class="col-sm-6 col-xs-6">
		<?php foreach($feedsA as $feed):?>
			<?php echo displayInstagramFeedItem($feed);?>
		<?php endforeach;?>
	</div>
	<?php endif; ?>
</div>
<?php else: ?>
<div class="panel panel-default">
	<div class="panel-body status">
		<div class="who clearfix">
			<h5><?php echo _("Instagram") ?> <span class="pull-right"><i class="fa fa-instagram"></i></span></h5>
		</div>
		<div class="text">
			<p><?php echo _("Latest posts aren't available") ?></p>
			<p><?php echo _("Please check internet connection and try again") ?></p>
		</div>
	</div>
</div>
<?php endif; ?>