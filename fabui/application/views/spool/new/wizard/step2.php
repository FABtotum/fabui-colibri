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
<div class="step-pane" id="step2" data-step="2">
	<hr class="simple">
	<div class="row">
		<!--  -->
		<div class="col-sm-12">
			<div class="smart-form">
				<fieldset>
					<section>
						<label class="label font-md"><?php echo _('Select filament to load')?></label>
						<label class="select">
							<?php echo form_dropdown('filament', $filamentsOptions, '', 'id="filament" class="input-lg"'); ?> <i></i>
						</label>
					</section>
				</fieldset>
			</div>
		</div>
		<!--  -->
		<div class="col-sm-12">
			<div class="well well-light" id="filament-description"><?php echo getFilamentDescription('pla'); ?></div>
		</div>
	</div>
</div>
<?php foreach($filamentsOptions as $key => $val): ?>
<div id="<?php echo $key ?>_description" class="hidden"><?php echo getFilamentDescription($key); ?></div>
<?php endforeach;?>