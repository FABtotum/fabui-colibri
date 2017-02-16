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
<!-- TWITTER WIDGET  -->
<?php
if(isset($feeds)){
	foreach($feeds as $feed){
		echo displayTwitterFeedItem($feed);
	}
}else{
?>
<div class="panel panel-default">
	<div class="panel-body status">
		<div class="who clearfix">
			<h5><?php echo _("Twitter") ?> <span class="pull-right"><i class="fa fa-twitter"></i></span></h5>
		</div>
		<div class="text">
			<p><?php echo _("Latest tweets aren't available") ?></p>
			<p><?php echo _("Please check internet connection and try again") ?></p>
		</div>
	</div>
</div>
<?php
}
?>
<!-- END TWITTER WIDGET -->