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
<?php foreach($feeds as $feed):?>
	<?php echo displayTwitterFeedItem($feed);?>
<?php endforeach;?>