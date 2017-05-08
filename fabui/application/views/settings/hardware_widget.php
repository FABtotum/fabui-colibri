<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<div class="tab-content padding-10">
	<!-- hardware tab -->
	<div class="tab-pane fade in active" id="hardware-tab">
		<div class="smart-form">
			<fieldset>
				<section>
					<label class="radio">
						<input type="radio" id="settings_type" name="settings_type" value="default" <?php if($defaultSettings['settings_type'] == 'default') echo 'checked="checked"'; ?>><i></i><?php echo _("Use default settings");?>
					</label>
					<label class="radio">
						<input type="radio" id="settings_type" name="settings_type" value="custom"  <?php if($defaultSettings['settings_type'] == 'custom') echo 'checked="checked"'; ?>><i></i><?php echo _("Use custom settings");?>
					</label>
				</section>
			</fieldset>
			<fieldset class="custom_settings" <?php if($defaultSettings['settings_type'] == 'default'): ?> style="display: none;" <?php endif;?> >
				<div class="row">
					<section class="col col-6">
						<label class="label"><?php echo _('Engage')?>/<?php echo _('Disengage')?> <?php echo _('Feeder option')?></label>
						<label class="select">
							<?php echo form_dropdown('feeder-show', $yesNoOptions, $defaultSettings['feeder']['show'], 'id="feeder-show"'); ?> <i></i>
						</label>
					</section>
					<section class="col col-6">
						<label class="label"><?php echo _('Invert X Endstop Logic')?></label>
						<label class="select">
							<?php echo form_dropdown('custom-invert_x_endstop_logic', $yesNoOptions, $defaultSettings['custom']['invert_x_endstop_logic'], 'id="custom-invert_x_endstop_logic"'); ?> <i></i>
						</label>
					</section>
				</div>
				<div class="row">
					<section class="col col-6">
						<label class="label"><?php echo _('Camera')?></label>
						<label class="select">
							<?php echo form_dropdown('hardware-camera-available', $yesNoOptions, isset($defaultSettings['hardware']['camera']['available']) ? $defaultSettings['hardware']['camera']['available'] : false, 'id="hardware-camera-available"'); ?> <i></i>
						</label>
					</section>
				</div>
				<section>
					<label class="label"><?php echo _('Custom overrides')?></label>
					<label class="textarea">
						<textarea id="custom-overrides" name="cutom-overrides" rows="5"><?php echo $defaultSettings['custom']['overrides']; ?></textarea>
					</label>
				</section>
			</fieldset>
		</div>
	</div>
	<!-- safeyt tab -->
	<div class="tab-pane fade in" id="safety-tab">
		<div class="smart-form">
			<fieldset>
				<section>
					<label class="label"><?php echo _('Door safety messages')?></label>
					<div class="inline-group">
						<label class="radio">
							<input type="radio" id="safety-door" name="safety-door" value="1" <?php if($defaultSettings['safety']['door'] == 1) echo 'checked="checked"'; ?>><i></i><?php echo _('Enable')?>
						</label>
						<label class="radio">
							<input type="radio" id="safety-door" name="safety-door" value="0" <?php if($defaultSettings['safety']['door'] == 0) echo 'checked="checked"'; ?>><i></i><?php echo _('Disabled')?>
						</label>
					</div>
				</section>
			</fieldset>
			<fieldset>
				<section>
					<label class="label"> <?php echo _('Machine limits collision warning')?></label>
					<div class="inline-group">
						<label class="radio">
							<input type="radio" id="safety-collision_warning" name="safety-collision_warning" value="1" <?php if($defaultSettings['safety']['collision_warning'] == 1) echo 'checked="checked"'; ?>><i></i><?php echo _('Enable')?>
						</label>
						<label class="radio">
							<input type="radio" id="safety-collision_warning" name="safety-collision_warning" value="0" <?php if($defaultSettings['safety']['collision_warning'] == 0) echo 'checked="checked"'; ?>><i></i><?php echo _('Disabled')?>
						</label>
					</div>
				</section>
			</fieldset>
			<fieldset class="<?php echo ($unitType == 'PRO' || $defaultSettings['settings_type'] == 'custom') ? '' : 'hidden'?> pro">
				<section>
					<label class="label"> <?php echo _('Wire endstop')?></label>
					<div class="inline-group">
						<label class="radio">
							<input type="radio" id="wire_end" name="wire_end" value="1" <?php if(isset($defaultSettings['wire_end']) && $defaultSettings['wire_end'] == 1) echo 'checked="checked"'; ?>><i></i><?php echo _('Enable')?>
						</label>
						<label class="radio">
							<input type="radio" id="wire_end" name="wire_end" value="0" <?php if(isset($defaultSettings['wire_end']) && $defaultSettings['wire_end'] == 0) echo 'checked="checked"'; ?>><i></i><?php echo _('Disabled')?>
						</label>
					</div>
				</section>
			</fieldset>
		</div>
	</div>
	<!-- homing tab -->
	<div class="tab-pane fade in" id="homing-tab">
		<div class="smart-form">
			<fieldset>
				<section>
					<label class="label"><?php echo _('Default Homing Direction')?></label>
					<div class="inline-group">
						<label class="radio">
							<input type="radio" id="switch" name="switch" value="0" <?php if($defaultSettings['switch'] == 0) echo 'checked="checked"'; ?>><i></i><?php echo _('Left')?>
						</label>
						<label class="radio">
							<input type="radio" id="switch" name="switch" value="1" <?php if($defaultSettings['switch'] == 1) echo 'checked="checked"'; ?>><i></i><?php echo _('Right')?>
						</label>
					</div>
				</section>
			</fieldset>
			<fieldset>
				<section>
					<label class="label"><?php echo _('Use the z touch probe during homing')?></label>
					<div class="inline-group">
						<label class="radio">
							<input type="radio" id="zprobe-enable" name="zprobe-enable" value="1" <?php if($defaultSettings['zprobe']['enable'] == 1) echo 'checked="checked"'; ?>><i></i><?php echo _('Enable')?>
						</label>
						<label class="radio">
							<input type="radio" id="zprobe-enable" name="zprobe-enable" value="0" <?php if($defaultSettings['zprobe']['enable'] == 0) echo 'checked="checked"'; ?>><i></i><?php echo _('Disabled')?>
						</label>
					</div>
				</section>
			</fieldset>
			<!--  
			<fieldset>
				<div class="row">
					<section class="col col-sm-12">
						<label class="label"><?php echo _('Z max home pos')?> (mm)</label>
						<label class="input">
							<input type="number" id="zprobe-zmax" name="zprobe-zmax" value="<?php echo $defaultSettings['z_max_offset']; ?>">
						</label>
					</section> 
				</div>
			</fieldset>
			-->
		</div>
	</div>
	<!-- customized actions tab -->
	<!--
	<div class="tab-pane fade in" id="customized-actions-tab">
		<div class="smart-form">
			<fieldset>
				<section>
					<label class="label"><?php echo _('Both Y endstops pressed')?></label>
					<label class="select">
						<?php echo form_dropdown('customized_actions-bothy', $customizeActionsOptions, $defaultSettings['customized_actions']['bothy'], 'id="customized_actions-bothy"'); ?> <i></i>
					</label>
				</section>
				<section>
					<label class="label"><?php echo _('Both Z endstops pressed')?></label>
					<label class="select">
						<?php echo form_dropdown('customized_actions-bothz', $customizeActionsOptions, $defaultSettings['customized_actions']['bothz'], 'id="customized_actions-bothz"'); ?> <i></i>
					</label>
				</section>
			</fieldset>
		</div>
	</div>
	-->
	<!-- print tab -->
	<div class="tab-pane fade in" id="print-tab">
		<div class="smart-form">
			<fieldset>
				<!--
				<div class="row">
					<section class="col col-sm-12">
						<label class="label"> <?php echo _('Pre-heating nozzle temperature')?></label>
						<label class="input">
							<input type="number" id="print-pre_heating-nozzle" name="print-pre_heating-nozzle" value="<?php echo $defaultSettings['print']['pre_heating']['nozzle'] ?>">
						</label>
					</section>
				</div>
				<div class="row">
					<section class="col col-sm-12">
						<label class="label"><?php echo _('Pre-heating bed temperature')?></label>
						<label class="input">
							<input type="number" id="print-pre_heating-bed" name="print-pre_heating-bed" value="<?php echo $defaultSettings['print']['pre_heating']['bed'] ?>">
						</label>
					</section>
				</div>
				-->
				<div class="row">
					<section class="col col-sm-12">
						<label class="label"><?php echo _('Calibration preference')?></label>
						<label class="select">
							<?php echo form_dropdown('print-calibration', $printCalibrationPreferenceOptions, $defaultSettings['print']['calibration'], 'id="print-calibration"'); ?> <i></i>
						</label>
					</section>
				</div>
			</fieldset>
		</div>
	</div>
	<!-- milling tab -->
	<!--
	<div class="tab-pane fade in" id="milling-tab">
		<div class="smart-form">
			<fieldset>
				
					<section >
						<label class="label"><?php echo _('Sacrificial layer thickness')?> (mm)</label>
						<label class="input">
							<input type="number" id="milling-layer_offset" name="milling-layer_offset" value="<?php echo $defaultSettings['milling']['layer_offset'] ?>">
						</label>
					</section>
				
			</fieldset>
		</div>
	</div>
	-->
	<!-- lighting-tab -->
	<div class="tab-pane fade in" id="lighting-tab">
		<fieldset style="display:none;">
			<input type="number" id="color-r" name="color-r" value="<?php echo $defaultSettings['color']['r']; ?>"/>
			<input type="number" id="color-g" name="color-g" value="<?php echo $defaultSettings['color']['g']; ?>"/>
			<input type="number" id="color-b" name="color-b" value="<?php echo $defaultSettings['color']['b']; ?>"/>
		</fieldset>
		
		<div class="row padding-10">
			<div class="col-sm-6">
				<div class="row margin-top-10">
					<div class="col-sm-12">
						<p><?php echo _('Standby')?> </p>
					</div>
				</div>
				<div class="row margin-top-10">
					<div class="col-sm-12">
						<div class="nouislider standby-color" id="red"></div>
					</div>
				</div>
				<div class="row margin-top-10">
					<div class="col-sm-12">
						<div class="nouislider standby-color standby-green" id="green"></div>
					</div>
				</div>
				<div class="row margin-top-10">
					<div class="col-sm-12">
						<div class="nouislider standby-color standby-blue" id="blue"></div>
					</div>
				</div>
			</div>
			
			<div class="col-sm-6">
				<div class="row margin-top-10">
					<div class="col-sm-12">
						<p>&nbsp;</p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<div class="result" id="result"></div>
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>
