<?php
/**
 * 
 * @author Krios Mane
 * @author Fabtotum Development Team
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 defined('BASEPATH') OR exit('No direct script access allowed');
?>
<hr class="simple">
<div class="row">
	<!-- benchmark image -->
	<div class="col-sm-6"></div>
	<!-- quality parameters -->
	<div class="col-sm-6"></div>
</div>
<div class="row padding-10">
	<!-- slider -->
	<div class="col-sm-12">
		<div id="rotating-slider" class="noUiSlider"></div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	var rotatingSlider;
	initRotatingSlider();
});
</script>