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
		<div class="smart-form">
			<header>Camera settings</header>
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">Iso</label>
						<label class="input">
							<input type="text">
						</label>
					</section>
					<section class="col col-6">
						<label class="label">Size</label>
						<label class="input">
							<input type="text">
						</label>
					</section>
				</div>
				<div class="row">
					<section class="col col-6">
						<label class="label">Slices</label>
						<label class="input">
							<input type="number" value="60" step="1">
						</label>
					</section>
				</div>
			</fieldset>
			<header>Desktop Server</header>
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">IP Address</label>
						<label class="input">
							<input type="text">
						</label>
					</section>
					<section class="col col-3">
						<label class="label">Port</label>
						<label class="input">
							<input type="text">
						</label>
					</section>
					<section class="col col-3"></section>
				</div>
			</fieldset>
		</div>
	</div>
</div>
<!--  -->
<script type="text/javascript">
$(document).ready(function() {
	$(".button-next").attr('data-scan', 'photogrammetry');
});
</script>