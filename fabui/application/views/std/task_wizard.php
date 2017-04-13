<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
/* variable initialization */

$this->load->helper('std_helper');
if( !isset($steps) ) $steps = array();
$steps = initializeSteps($steps);

if( !isset($runningTask) ) $runningTask = 0;
if( !isset($warning) ) $warning = '';
if( !isset($safety_check) ) $safety_check = array("all_is_ok" => true);

if($runningTask) $safety_check['all_is_ok'] = true;

?>

<?php if($warning): ?>
<div class="alert alert-warning animated  fadeIn" role="alert">
	<i class="fa fa-warning"></i><strong><?php echo _("Warning");?></strong> <?php echo $warning;?>
</div>
<?php endif; ?>

<div id="safety-check-content">
<?php if(!$safety_check['all_is_ok']): ?>
<?php echo $safety_check['content']; ?>
<?php endif; ?>
</div>
<div id="task-wizard-content" style="<?php echo $safety_check["all_is_ok"]?"":"display:none" ?>">
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
			<button type="button" class="btn btn-sm btn-primary button-prev" id="wizard-button-prev">
				<i class="fa fa-arrow-left"></i> <span><?php echo _("Prev");?></span>
			</button>
			<button type="button" class="btn btn-sm btn-success button-next" data-last="<?php echo _("Finish"); ?>" id="wizard-button-next">
				<span><?php echo _("Next");?></span> <i class="fa fa-arrow-right"></i>
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
					{
						if($step['active'] == true)
						{
							$active = 'active';
						}
					}
					
					echo '<div class="step-pane '.$active.'" id="step'.$step['number'].'" data-step="'.$step['number'].'">';
					echo '<hr class="simple">';
					echo $step['content'];
					
					// Sub wizard
					 
					if( array_key_exists('steps', $step) )
					{
						$step['steps'] = initializeSteps($step['steps']);
						
						
						foreach($step['steps'] as $sub_step)
						{
							$active = 'display:none';
							if($sub_step['active'])
							{
								$active = '';
							}
							
							echo '<div style="'.$active.'" id="step'.$step['number'].'-'.$sub_step['number'].'" data-step="'.$sub_step['name'].'">';
							echo $sub_step['content'];
							echo '</div>';
						}
						
						
						echo '<div class="wizard" data-initialize="wizard" id="subWizard-'.$step['name'].'">';
						echo '<div class="steps-container">';
						echo '<ul class="steps">';
						
						foreach($step['steps'] as $sub_step) {
							$active = '';
							if($sub_step['active'])
							{
								$active = 'active';
							}
							echo '<li data-step="'. $step['number'].'" data-target="#step'. $sub_step['number']. '" class="'.$active.'">';
							echo '<span class="badge">'.$step['number'].'.'.$sub_step['number'].'</span>'.$sub_step['title'].'<span class="chevron"></span>';
							echo '</li>';
						}
						
						echo '
						<div class="actions">
							<button type="button" class="btn btn-sm btn-primary button-prev">
								<i class="fa fa-arrow-left"></i> <span>'._("Prev").'</span>
							</button>
							<button type="button" class="btn btn-sm btn-success button-next" data-last="'. _("Finish").'">
								<span>'._("Next").'</span> <i class="fa fa-arrow-right"></i>
							</button>
						</div>';
						
						echo '</ul></div></div>';
						
						
					}
					
					echo '</div>';
				}
			?>
		</form>
	</div>
</div>
