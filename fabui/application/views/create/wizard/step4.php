<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="step-pane" id="step4" data-step="4">
	<hr class="simple">
	<div class="text-center">
		<h1 class="tada animated">
			<span style="position: relative;">
				<i class="fa fa-play fa-rotate-90 fa-border border-black fa-4x"></i>
				<span><b style="position:absolute; right: -30px; top:-10" class="badge bg-color-green font-md"><i class="fa fa-check txt-color-black"></i> </b></span>
			</span>
		</h1>
		<h2><strong><?php echo ucfirst($type) ?> completed! </strong></h2>
		<p class="lead semi-bold">Duration: <span class="elapsed-time"></span></p>
		<p class="lead semi-bold">
			<a href="javascript:void(0);" class="btn btn-default restart">Restart Print</a>
			<a href="javascript:void(0);" class="btn btn-default new-print">New Print</a>
		</p>
	</div>
</div>