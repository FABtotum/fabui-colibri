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
						<input type="radio" id="settings_type" name="settings_type" value="default" <?php if($defaultSettings['settings_type'] == 'default') echo 'checked="checked"'; ?>><i></i>Use default settings
					</label>
					<label class="radio">
						<input type="radio" id="settings_type" name="settings_type" value="custom"  <?php if($defaultSettings['settings_type'] == 'custom') echo 'checked="checked"'; ?>><i></i>Use custom settings
					</label>
				</section>
			</fieldset>
			<fieldset class="custom_settings" <?php if($defaultSettings['settings_type'] == 'default'): ?> style="display: none;" <?php endif;?> >
				<div class="row">
					<section class="col col-6">
						<label class="label">Engage/Disengage Feeder option</label>
						<label class="select">
							<?php echo form_dropdown('feeder-show', $yesNoOptions, $customSettings['feeder']['show'], 'id="feeder-show"'); ?> <i></i>
						</label>
					</section>
					<section class="col col-6">
						<label class="label">Invert X Endstop Logic</label>
						<label class="select">
							<?php echo form_dropdown('invert_x_endstop_logic', $yesNoOptions, $customSettings['invert_x_endstop_logic'], 'id="invert_x_endstop_logic"'); ?> <i></i>
						</label>
					</section>
				</div>
				<div class="row">
				<!-- 
					<section class="col col-6">
						<label class="label">Extruder steps per unit <strong>(E mode)</strong></label>
						<label class="input">
							<input type="text" id="e" name="e" value="<?php echo $customSettings['e'] ?>">
							<b class="tooltip tooltip-top-right"><i class="fa fa-refresh txt-color-teal"></i> you must restart the FABtotum to apply these changes</b> </label>
						</label>
					</section>
				 -->
					<section class="col col-6">
						<label class="label">Extruder steps per unit <strong>(A mode)</strong></label>
						<label class="input">
							<input type="text" id="a" name="a" value="<?php echo $customSettings['a'] ?>">
							<b class="tooltip tooltip-top-right"><i class="fa fa-refresh txt-color-teal"></i> you must restart the FABtotum to apply these changes</b> </label>
						</label>
					</section>
				</div>
				<div class="row">
					<section class="col col-12">
						<div class="note">
							If you change values for Extruder steps you have to restart the FABtotum so that can values take effect
						</div>
					</section>
				</div>
				<section>
					<label class="label">Custom overrides</label>
					<label class="textarea">
						<textarea id="custom_overrides" rows="5"></textarea>
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
					<label class="label">Door Safety Messages</label>
					<div class="inline-group">
						<label class="radio">
							<input type="radio" id="safety-door" name="safety-door" value="1" <?php if($defaultSettings['safety']['door'] == 1) echo 'checked="checked"'; ?>><i></i>Enable
						</label>
						<label class="radio">
							<input type="radio" id="safety-door" name="safety-door" value="0" <?php if($defaultSettings['safety']['door'] == 0) echo 'checked="checked"'; ?>><i></i>Disabled
						</label>
					</div>
				</section>
			</fieldset>
			<fieldset>
				<section>
					<label class="label">Machine Limits Collision warning</label>
					<div class="inline-group">
						<label class="radio">
							<input type="radio" id="safety-collision_warning" name="safety-collision_warning" value="1" <?php if($defaultSettings['safety']['collision_warning'] == 1) echo 'checked="checked"'; ?>><i></i>Enable
						</label>
						<label class="radio">
							<input type="radio" id="safety-collision_warning" name="safety-collision_warning" value="0" <?php if($defaultSettings['safety']['collision_warning'] == 0) echo 'checked="checked"'; ?>><i></i>Disabled
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
					<label class="label">Default Homing Direction</label>
					<div class="inline-group">
						<label class="radio">
							<input type="radio" id="switch" name="switch" value="l" <?php if($defaultSettings['switch'] == 1) echo 'checked="checked"'; ?>><i></i>Left
						</label>
						<label class="radio">
							<input type="radio" id="switch" name="switch" value="0" <?php if($defaultSettings['switch'] == 0) echo 'checked="checked"'; ?>><i></i>Right
						</label>
					</div>
				</section>
			</fieldset>
			<fieldset>
				<section>
					<label class="label">Use the Z Touch Probe during homing</label>
					<div class="inline-group">
						<label class="radio">
							<input type="radio" id="zprobe-enable" name="zprobe-enable" value="l" <?php if($defaultSettings['zprobe']['enable'] == 1) echo 'checked="checked"'; ?>><i></i>Enable
						</label>
						<label class="radio">
							<input type="radio" id="zprobe-enable" name="zprobe-enable" value="0" <?php if($defaultSettings['zprobe']['enable'] == 0) echo 'checked="checked"'; ?>><i></i>Disable
						</label>
					</div>
				</section>
				<div class="row">
					<section class="col col-6">
						<label class="label">Z Max Home Pos (mm)</label>
						<label class="input">
							<input type="number" id="zprobe-zmax" name="zprobe-zmax" value="<?php echo $defaultSettings['zprobe']['zmax']; ?>">
						</label>
					</section> 
				</div>
			</fieldset>
		</div>
	</div>
	<!-- customized actions tab -->
	<div class="tab-pane fade in" id="customized-actions-tab">
		<div class="smart-form">
			<fieldset>
				<section>
					<label class="label">Both Y Endstops pressed</label>
					<label class="select">
						<?php echo form_dropdown('customized_actions-bothy', $customizeActionsOptions, $defaultSettings['customized_actions']['bothy'], 'id="customized_action-bothy"'); ?> <i></i>
					</label>
				</section>
				<section>
					<label class="label">Both Z Endstops pressed</label>
					<label class="select">
						<?php echo form_dropdown('customized_actions-bothz', $customizeActionsOptions, $defaultSettings['customized_actions']['bothz'], 'id="customized_action-bothz"'); ?> <i></i>
					</label>
				</section>
			</fieldset>
		</div>
	</div>
	<!-- print tab -->
	<div class="tab-pane fade in" id="print-tab">
		<div class="smart-form">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">Pre-heating Nozzle temperature</label>
						<label class="input">
							<input type="number" id="print-pre_heating-nozzle" name="print-pre_heating-nozzle" value="<?php echo $defaultSettings['print']['pre_heating']['nozzle'] ?>">
						</label>
					</section>
				</div>
				<div class="row">
					<section class="col col-6">
						<label class="label">Pre-heating Bed temperature</label>
						<label class="input">
							<input type="number" id="print-pre_heating-bed" name="print-pre_heating-bed" value="<?php echo $defaultSettings['print']['pre_heating']['bed'] ?>">
						</label>
					</section>
				</div>
			</fieldset>
		</div>
	</div>
	<!-- milling tab -->
	<div class="tab-pane fade in" id="milling-tab">
		<div class="smart-form">
			<fieldset>
				<div class="row">
					<section class="col col-6">
						<label class="label">Sacrificial Layer Thickness (mm)</label>
						<label class="input">
							<input type="number" id="milling-layer_offset" name="milling-layer_offset" value="<?php echo $defaultSettings['milling']['layer_offset'] ?>">
						</label>
					</section>
				</div>
			</fieldset>
		</div>
	</div>
	
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
						<p>Standby</p>
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
