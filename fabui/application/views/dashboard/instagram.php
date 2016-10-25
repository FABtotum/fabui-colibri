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
	<div class="col-sm-6 col-xs-6">
		<?php foreach($feedsA as $feed):?>
			<?php echo displayInstagramFeedItem($feed);?>
		<?php endforeach;?>
	</div>
	<div class="col-sm-6 col-xs-6">
		<?php foreach($feedsB as $feed):?>
			<?php echo displayInstagramFeedItem($feed);?>
		<?php endforeach;?>
	</div>
</div>