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
<div class="wizard" data-initialize="wizard" id="myWizard">
	<div class="steps-container">
		<ul class="steps">
			<li data-step="1" data-target="#step1" class="active">
				<span class="badge badge-info">1</span><?php echo _("Choose mode"); ?><span class="chevron"></span>
			</li>
			<li data-step="2" data-target="#step2">
				<span class="badge">2</span><?php echo _("Filament"); ?><span class="chevron"></span>
			</li>
			<li data-step="3" data-target="#step3">
				<span class="badge">3</span><?php echo _("Get ready"); ?><span class="chevron"></span>
			</li>
			<li data-step="4" data-target="#step4">
				<span class="badge">4</span><?php echo _("Finish"); ?><span class="chevron"></span>
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
<div class="step-content" id="fuelux-wizard">
	<?php if(isset($step1)) echo $step1; ?>
	<?php if(isset($step2)) echo $step2; ?>
	<?php if(isset($step3)) echo $step3; ?>
	<?php if(isset($step4)) echo $step4; ?>
</div>
