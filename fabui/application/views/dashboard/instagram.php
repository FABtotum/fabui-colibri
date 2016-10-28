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
<div class="row">
	<?php if(count($feedsA) > 0): ?>
	<div class="col-sm-6 col-xs-6">
		<?php foreach($feedsA as $feed):?>
			<?php echo displayInstagramFeedItem($feed);?>
		<?php endforeach;?>
	</div>
	<?php endif; ?>
	<?php if(count($feedsB) > 0): ?>
	<div class="col-sm-6 col-xs-6">
		<?php foreach($feedsB as $feed):?>
			<?php echo displayInstagramFeedItem($feed);?>
		<?php endforeach;?>
	</div>
	<?php endif; ?>
</div>