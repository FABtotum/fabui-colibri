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

<div class="row">
	<!--  -->
	<div class="col-sm-12">
		<h3 id="filament-title"></h3>
		<div class="btn-group btn-group-justified" id="filament-choice">
		<?php foreach($filamentsOptions as $code => $info):?>
			<a class="btn btn-default filament <?php echo $code; ?>" data-type="<?php echo $code; ?>" href="javascript:void(0);"><?php echo $info['name']?> <span></span></a>
		<?php endforeach; ?>
		</div>					
	</div>
</div>
<hr class="simple">
<div class="row">
	<div class="col-sm-12">
		<div class="well well-light well-sm margin-bottom-10">
			<i class="fa-fw fa fa-thermometer-three-quarters"></i>  <?php echo _("Extrusion temperature");?>: <span><strong  class="extrusion-temperature"></strong></span> &deg;C 
		</div>
	</div>
</div>
<div class="row">
	<!--  -->
	<div class="col-sm-12">
		<div class="well well-light" id="filament-description"><?php echo getFilamentDescription('pla'); ?></div>
	</div>
</div>
<?php foreach($filamentsOptions as $key => $val): ?>
<div id="<?php echo $key ?>_description" data-temperature="<?php echo $val['temperatures']['extrusion']?>" class="hidden">
	<?php echo getFilamentDescription($key); ?>
</div>
<?php endforeach;?> 