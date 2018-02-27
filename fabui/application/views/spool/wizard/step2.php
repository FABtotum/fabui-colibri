<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * 
 */
$filament_selected = isset($settings['filament']['type']) ? $settings['filament']['type'] : 'pla';
?>

<div class="row">
	<div class="col-sm-12">
		<div class="smart-form">
			<fieldset>
				<section>
					<label class="label" id="filament-title"></label>
					<div class="inline-group">
						<?php foreach($filamentsOptions as $code => $info): ?>
							<label class="radio">
								<input value="<?php echo $code; ?>" data-type="<?php echo $code; ?>" data-details="<?php echo $info['details']?>" data-temperature="<?php echo $info['temperatures']['extrusion']?>"  type="radio" name="filament-type" <?php echo $filament_selected == $code ? 'checked="checked"': ''?>> <i></i> <?php echo $info['name']; ?>
							</label>
						<?php endforeach;?>
					</div>
				</section>
			</fieldset>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="well well-light well-sm margin-bottom-10">
    		<div class="form-inline">
    			<fieldset>
    				<div class="form-group">
    					<label> <i class="fa-fw fa fa-thermometer-three-quarters"></i>  <?php echo _("Extrusion temperature");?> :</label>
    					<div class="input-group">
							<input id="extrusion-temperature" class="form-control" min="<?php echo $head['min_temp'] ?>" max="<?php echo $head['max_temp'];?>" type="number" value="<?php echo $filamentsOptions[$filament_selected]['temperatures']['extrusion']?>">
							<span class="input-group-addon"> &deg;C</span>
						</div>
    				</div>
    				<div class="form-group" style="margin-left:10px;">
    					<label> <a target="_blank" class="no-ajax" id="details" href="<?php echo $filamentsOptions[$filament_selected]['details'] ?>"><i class="fa fa-external-link-alt"></i>  <?php echo _("Details");?></a></label>
    				</div>
    			</fieldset>
    			
    		</div>
		</div>
	</div>
</div>

