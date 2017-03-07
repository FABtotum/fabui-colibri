<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
/* variable initialization */
if( !isset($safety_check) ) $safety_check = array( 'all_is_ok' => false, 'head_is_ok' => false, 'bed_is_ok' => false );
?>

<div class="row text-center">
<h1><strong>Safety check</strong></h1>
</div>

<div class="row">
	
	<div class="col-sm-6 col-md-6 text-center">
		<div class="row">
			<img id="safety-check-head-image" class="" src="<?php echo $safety_check['head_in_place']?$safety_check['head_info']['image_src']:"/assets/img/head/head_shape.png"; ?>" style="height:320px">
		</div>
		
		<div id="safety-check-head-message">
		<?php if($safety_check['head_is_ok']): ?>
		<h4><strong><?php echo _("Correct head installed");?></strong> <i class="fa fa-check-circle text-success fa-2x"></i></h4>
		<?php else: ?>
		<h4><strong><?php echo $safety_check['head_in_place']?_("Wrong head installed"):_("No head installed");?></strong> <i class="fa fa-times-circle text-danger fa-2x"></i></h4>
		<h3><?php echo pyformat( _("Please install a {0} head."), [$type]); ?></h3>
		<?php endif; ?>
		</div>
	</div>
	
	<div class="col-sm-6 col-md-6 text-center">
		<div class="row">
			<img id="safety-check-bed-image" class="" src="/assets/img/controllers/bed/hybrid_bed_<?php echo $safety_check['bed_in_place']?"glass":"mill";?>.png"  style="height:320px">
		</div>
		
		<div id="safety-check-bed-message">
		<?php if($safety_check['bed_is_ok']): ?>
		<h4><strong><?php echo _("Bed inserted correctly");?></strong> <i class="fa fa-check-circle text-success fa-2x"></i></h4>
		<?php else:?>
		<h4><strong><?php echo _("Bed inserted incorrectly");?></strong> <i class="fa fa-times-circle text-danger fa-2x"></i></h4>
		<h3><?php echo  _("Please flip the bed to the other side."); ?></h3>
		<?php endif;?>
		</div>
	</div>
	
</div>

<div class="row">
	&nbsp;
</div>
