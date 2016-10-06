<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * 
 */
?>
<div class="step-pane <?php echo !$runningTask ? 'active' : ''; ?>" id="step1" data-step="1">
	<hr class="simple">
	<div class="row">
		<?php foreach($scanModes as $mode_label => $mode): ?>
			<div class="col-sm-<?php echo (12/sizeof($scanModes)); ?> col-xs-6 margin-bottom-10">
				<div class="panel panel-default">
					<div class="panel-body status">
						<div class="who clearfix">
							<h4><?php echo $mode['info']['name']; ?> <small class="hidden-xs">mode</small></h4>
						</div>
						<div class="text hidden-xs">
							<p class="font-sm"><?php echo $mode['info']['description']; ?></p>
						</div>
						<ul class="links text-right">
							<li><button data-scan-mode="<?php echo $mode_label; ?>" type="button" class="btn btn-default mode-choise">Choose <i class="fa  fa-arrow-right"></i></button></li>
						</ul>
					</div>
				</div>
			</div>
		<?php endforeach;?>
	</div>
</div>
