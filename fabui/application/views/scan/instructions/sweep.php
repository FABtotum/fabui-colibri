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
<div class="row">
	<div class="col-sm-12">
		<h2 class="text-center"><i class="fa fa-thumbs-up"></i> If you are ready click "next" to start scan </h2>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	enableButton('.button-next');
	$(".button-next").attr('data-action', 'start');
});
</script>
