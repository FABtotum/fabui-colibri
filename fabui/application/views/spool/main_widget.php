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
<div class="row fuelux">
	<div class="col-sm-12">
	<?php echo $wizard?>
	</div>
</div>
<div class="row spool-slider hidden">
	<div class="col-sm-12">
		<span id="slider-title margin-bottom-10"><?php echo _("Get more filaments");?></span><br>
		<?php foreach($filament_types as $key => $value):?>
		<button type="button" class="btn btn-default <?php echo $key == "*" ? 'btn-info' : '';?>  filters-button margin-top-10" data-filter="<?php echo $key?>"><?php echo $value;?></button>
		<?php endforeach; ?>
	</div>
</div>
<hr class="simple spool-slider hidden">
<div class="row spool-slider">
	<div class="col-sm-12">
		<div class="owl-carousel owl-theme"></div>
	</div>
</div>