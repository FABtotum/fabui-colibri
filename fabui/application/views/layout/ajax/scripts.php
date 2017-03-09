<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">
	var page = location.pathname + location.hash;
	console.log("page: ", page);
	ga('set', { page: (page)});
	ga('send', 'pageview');
	pageCleanUp();
	pageSetUp();
	transformLinks();
	<?php echo $jsInLine; ?>
	//page related scripts
	<?php echo $jsScripts; ?>
</script>
