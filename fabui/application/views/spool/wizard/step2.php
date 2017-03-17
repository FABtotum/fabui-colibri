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

<hr class="simple">
<div class="row">
	<!--  -->
	<div class="col-sm-12">
		<h3 id="filament-title"></h3>
		<div class="btn-group btn-group-justified">
		<?php foreach($filamentsOptions as $code => $label):?>
			<a class="btn btn-default filament <?php echo $code; ?>" data-type="<?php echo $code; ?>" href="javascript:void(0);"><?php echo $label?> <span></span></a>
		<?php endforeach; ?>
		</div>					
	</div>
</div>
<hr class="simple">
<div class="row">
	<!--  -->
	<div class="col-sm-12">
		<div class="well well-light" id="filament-description"><?php echo getFilamentDescription('pla'); ?></div>
	</div>
</div>

<?php foreach($filamentsOptions as $key => $val): ?>
<div id="<?php echo $key ?>_description" class="hidden"><?php echo getFilamentDescription($key); ?></div>
<?php endforeach;?>
