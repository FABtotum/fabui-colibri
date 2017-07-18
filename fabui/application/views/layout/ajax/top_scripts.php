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
	<?php if(ENVIRONMENT == 'production'): ?>
		var page = location.pathname + location.hash;
		ga('set', { page: (page)});
		ga('send', 'pageview');
	<?php endif; ?>
	pageCleanUp();
</script>
<?php echo $this->jsInLineTop; ?>
