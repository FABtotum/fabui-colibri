<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
/* variable initialization */
if( !isset($steps) ) $steps = array();

?>

<div class="wizard" data-initialize="wizard" id="myWizard">
	<div class="steps-container">
		<ul class="steps">
		<?php foreach($steps as $step):?>
		<li data-step="<?php echo $step['number']; ?>" data-target="#step<?php echo $step['number']; ?>" class="<?php echo (!$runningTask && $step['number'] == 1) ? 'active' : ''; ?>">
			<span class="badge"><?php echo $step['number']; ?></span><?php echo $step['title']; ?><span class="chevron"></span>
		</li>
	    <?php endforeach;?>
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
		<?php 
			foreach($steps as $step)
			{
				$active = '';
				if( array_key_exists('active', $step) )
					if($step['active'] == True)
						$active = 'active';
						
				echo '<div class="step-pane '.$active.'" id="step'.$step['number'].'" data-step="'.$step['number'].'">';
				echo $step['content'];
				echo '</div>';
			}
		?>
	</form>
</div>
