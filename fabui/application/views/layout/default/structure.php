<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>
<!DOCTYPE html>
<html>
	<head>{head}</head>
	<body class="smart-style-6">
		{ga}
		{top}
		{sidebar}
		<div id="main" role="main">
			{ribbon}
			<div id="content">{content}</div>
			<form id="lock-screen-form" action="<?php echo site_url('lock') ?>" method="POST"> </form>
		</div>
		{footer}
		{scripts}
	</body>
</html>