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
	fabApp.clearIntervals();
	pageSetUp();
	transformLinks();
	<?php echo ajaxJSInline($this->jsInLine, count($this->js) == 0); ?>
	<?php echo ajaxJScriptsInclusion($this->js); ?> 
</script>
