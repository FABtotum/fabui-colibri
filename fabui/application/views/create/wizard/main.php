<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
?>

<div class="wizard">
	<div class="steps-container">
		<ul class="steps">
			<li data-step="1" data-target="#step1" class="<?php echo !$runningTask ? 'active' : ''; ?>">
				<span class="badge badge-info">1</span>Choose File<span class="chevron"></span>
			</li>
			<li data-step="2" data-target="#step2">
				<span class="badge">2</span>Get Ready<span class="chevron"></span>
			</li>
			<li data-step="3" data-target="#step3" class="<?php echo $runningTask ? 'active' : ''; ?>">
				<span class="badge">3</span>Printing<span class="chevron"></span>
			</li>
			<li data-step="4" data-target="#step4">
				<span class="badge">4</span>Finish<span class="chevron"></span>
			</li>
		</ul>
	</div>
	<div class="actions">
		<button type="button" class="btn btn-sm btn-primary btn-prev">
			<i class="fa fa-arrow-left"></i> <span>Prev</span>
		</button>
		<button type="button" class="btn btn-sm btn-success btn-next" data-last="Finish">
			<span>Next</span> <i class="fa fa-arrow-right"></i>
		</button>
	</div>
</div>
<div class="step-content">
	<form class="form-horizontal" id="fuelux-wizard" method="post">
		<?php if(isset($step1)) echo $step1; ?>
		<?php if(isset($step2)) echo $step2; ?>
		<?php echo $step3; ?>
		<?php echo $step4; ?>
	</form>
</div>