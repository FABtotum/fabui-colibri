<?php
/**
 *
* @author Krios Mane
* @author FabTeam
* @version 0.1
* @license https://opensource.org/licenses/GPL-3.0
*
*/
$is_admin = $this->session->user['role'] == 'administrator';
?>
<?php if(!$is_admin):?>
	<div class="alert alert-info animeted fadeIn">
		<i class="fa fa-info-circle"></i> <?php echo _("To edit information below you need administrator privileges");?>.
	</div>
<?php endif; ?>
<div class="panel-group smart-accordion-default" id="accordion">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"> <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> Fabtotum Personal Fabricator </a></h4>
		</div>
		<div id="collapseOne" class="panel-collapse collapse in">
			<div class="panel-body ">
				 <dl class="dl-horizontal">
				 	
				 	<dt><?php echo _("Name");?></dt>
			        <dd><a href="javascript:void(0);" class="host-name edit-field"><?php echo $host_name ?> - <?php echo $avahi_description; ?></a></dd>
				 	
				 	<dt><?php echo _("Serial number");?></dt>
			        <dd><a href="javascript:void(0);" class="unit-serial-number edit-field"><?php echo $serial_number != '' ? strtoupper($serial_number) : '<i>'._("n.a.").'</i>'; ?></a></dd>
			        
			        <dt><?php echo _("Color");?></dt>
			        <dd><a href="javascript:void(0);" class="unit-color edit-field"><?php echo ucfirst(_($unit_color)); ?></a></dd>
			   		
			   		
			        <dt><?php echo _("Os");?></dt>
			        <dd><?php echo $os_info; ?></dd>
			        
			        <dt><?php echo _("Date");?></dt>
			        <dd><a href="javascript:void(0);" class="system-date-time edit-field"><?php  echo trim(shell_exec('date +"%b %a %d %H:%M %Y"')); //echo date('d/m/Y G:i');?></a></dd>
			        
			        <dt><?php echo _("Language");?></dt>
			        <dd><a href="javascript:void(0);" class="language edit-field"><?php echo $language;?></a></dd>
			        
			        <dt class="margin-top-20"><?php echo _("Fabui");?></dt>
			        <dd class="margin-top-20"><?php echo $bundles['fabui']['version']?></dd>
			        
			        <?php if(isset($versions['firmware'])):?>
			        <dt><?php echo _("Firmware");?></dt>
			        <dd><?php echo isset($versions['firmware']['version']) ? $versions['firmware']['version'] : _("n.a.") ?>
			        	<?php echo isset($versions['firmware']['build_date']) ? " - "._("Build date").": ".$versions['firmware']['build_date'] : "" ?>
			        	<?php echo isset($versions['firmware']['author']) ? " - "._("Author").": ".$versions['firmware']['author'] : "" ?>
			        </dd>
			        <?php endif; ?>
			        
			        <dt><?php echo _("Hardware");?></dt>
			        <dd><?php echo isset($versions['production']['batch']) ? $versions['production']['batch'] : _("n.a.") ?></dd>
			        
			        <dt><?php echo _("Installed head");?></dt>
			        <dd><a href="maintenance/head"><?php echo $installed_head['name']; ?></a></dd>
      			</dl>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapse5" class="collapsed"> <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> <?php echo _("Printer settings (M503 output)"); ?> </a></h4>
		</div>
		<div id="collapse5" class="panel-collapse collapse">
			<div class="panel-body">
				<dl class="dl-horizontal big">
					<dt>Steps per unit</dt>
					<dd><code><?php echo isset($eeprom['steps_per_unit']['string']) ? $eeprom['steps_per_unit']['string'] : _("n.a.") ?></code></dd>
					
					<dt>Maximum feedrates (mm/s)</dt>
					<dd><code><?php echo isset($eeprom['maximum_feedrates']['string']) ? $eeprom['maximum_feedrates']['string'] : _("n.a.") ?></code></dd>
					
					<dt>Maximum Acceleration (mm/s2)</dt>
					<dd><code><?php echo isset($eeprom['maximum_accelaration']['string']) ? $eeprom['maximum_accelaration']['string'] : _("n.a.") ?></code></dd> 
					
					<dt>Acceleration: S=acceleration, T=retract acceleration</dt>
					<dd><code><?php echo isset($eeprom['acceleration']['string']) ? $eeprom['acceleration']['string'] : _("n.a.") ?></code></dd>
					
					<dt>Advanced variables</dt>
					<dd><code><?php echo isset($eeprom['advanced_variables']['string']) ? $eeprom['advanced_variables']['string'] : _("n.a.") ?></code></dd> 
					
					<dt>Home offset (mm)</dt>
					<dd><code><?php echo isset($eeprom['home_offset']['string']) ? $eeprom['home_offset']['string'] : _("n.a.") ?></code></dd>
					
					<dt>PID</dt>
					<dd><code><?php echo isset($eeprom['pid']['string']) ? $eeprom['pid']['string'] : _("n.a.") ?></code></dd>
					
					<dt>Servo Endstop</dt>
					<dd><code><?php echo isset($eeprom['servo_endstop']) ? 'R: '.$eeprom['servo_endstop']['r'].' E: '.$eeprom['servo_endstop']['e'] : _("n.a.") ?></code></dd>
					
					<dt>Z Probe Length</dt>
					<dd><code><?php echo isset($eeprom['probe_length']) ? $eeprom['probe_length'] : _("n.a.") ?></code></dd> 
					
					<dt>Installed head ID</dt>
					<dd><code><?php echo isset($eeprom['installed_head']) ? $eeprom['installed_head'] : _("n.a.") ?></code></dd>
					
					<dt>Batch number</dt>
					<dd><code><?php echo isset($eeprom['batch_number']) ? $eeprom['batch_number'] : _("n.a.") ?></code></dd>
					
					<dt>Baudrate</dt>
					<dd><code><?php echo isset($eeprom['baudrate']) ? $eeprom['baudrate'] : _("n.a.") ?></code></dd>
					
					<dt>FABlin version</dt>
					<dd><code><?php echo isset($eeprom['fablin_version']) ? $eeprom['fablin_version'] : _("n.a.") ?></code></dd>
				</dl>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="collapsed"> <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> <?php echo _("Board details"); ?> </a></h4>
		</div>
		<div id="collapseTwo" class="panel-collapse collapse">
			<div class="panel-body">
				<dl class="dl-horizontal">
					<dt><?php echo _("Board");?></dt>
			        <dd><?php echo $rpi_version?></dd>
			        
			        <dt><?php echo _("Time alive");?></dt>
			        <dd><?php echo transformSeconds($time_alive)?></dd>
			        
			        <dt><?php echo _("Temperature") ?></dt>
			        <dd><?php echo $temp . '&deg;'; ?></dd>
			        
			        <dt><?php echo _("RAM memory") ?></dt>
			        <dd><?php echo _("Free ") ?> <?php echo floor($mem_free / 1024); ?> MB</dd>
			        <dd><?php echo _("Used ") ?> <?php echo (floor($mem_total / 1024) - floor($mem_free / 1024) ) ; ?> MB</dd>
			        <dd><?php echo _("Total ") ?> <?php echo floor($mem_total / 1000); ?> MB</dd>
			        
				</dl>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" class="collapsed"> <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> Storage </a></h4>
		</div>
		<div id="collapseThree" class="panel-collapse collapse">
			<div class="panel-body no-padding">
				<table class="table table-bordered table-condensed">
					<thead>
						<tr>
						<?php foreach ($table_header as $header): ?>
							<th><?php echo $header; ?></th>
						<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($table_rows as $row): ?>
						<tr>
						<?php $columns = explode(' ', $row); ?>
						<?php foreach($columns as $column): ?>
							<td><?php echo $column; ?></td>
						<?php endforeach;?>
						</tr>
					<?php endforeach;?>
					</tbody>
				</table>
			</div>
		</div>
		
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseFour" class="collapsed"> <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> <?php echo _("Bundles"); ?> </a></h4>
		</div>
		<div id="collapseFour" class="panel-collapse collapse">
			<div class="panel-body" style="height: 250px; overflow: auto;">
				<dl class="dl-horizontal">
				<?php foreach ($bundles as $bundle):?>
					<dt><?php echo ucfirst($bundle['info']['name']);?></dt>
			        <dd><?php echo $bundle['info']['version']?></dd>
			        <dd><?php echo _("Build date") ?>: <?php echo $bundle['info']['build_date']?></dd>
			        <hr class="simple">
				<?php endforeach;?>
				</dl>
			</div>
		</div>
	</div>
</div>
<?php if($this->session->user['role'] == 'administrator'):?>
<!-- DATETIME MODAL -->
<div class="modal fade" id="dateTimeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo _("Set system date and time") ?></h4>
			</div>
			<div class="modal-body no-padding" >
				<div class="row">
					<div class="col-sm-12">
						<form class="smart-form" id="date-time-form">
							<fieldset>
								<div class="row">
									<section class="col col-2">
										<label class="label"><?php echo _("Day");?></label>
										<label class="select">
											<?php echo days_menu('day', date('d'), 'id="day"');?> <i></i> 
										</label>
									</section> 
									<section class="col col-4">
										<label class="label"><?php echo _("Month"); ?></label>
										<label class="select">
											<?php echo months_menu('month', date('m'), 'id="month"');?> <i></i>
										</label>
									</section>
									<section class="col col-2">
										<label class="label"><?php echo _("Year"); ?></label>
										<label class="select">
											<?php echo years_menu('year', date('Y'), 1975, date('Y'), 'id="year"');?> <i></i> 
										</label>
									</section>
									<section class="col col-2">
										<label class="label"><?php echo _("Hours"); ?></label>
										<label class="select">
											<?php echo hours_menu('hour', date('H'), 'id="hour"');?> <i></i> 
										</label>
									</section>
									<section class="col col-2">
										<label class="label"><?php echo _("Minuti"); ?></label>
										<label class="select">
											<?php echo minutes_menu('minute', date('i'), 'id="minute"');?> <i></i> 
										</label>
									</section> 
								</div>
								<section>
									<label class="label"><?php echo _("Timezone");?></label>
									<label class="select">
										<?php echo timezone_menu('timezone', $current_timezone, 'id="timezone"');?> <i></i>
									</label>
								</section>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel')?></button>
				<button type="button" class="btn btn-primary" id="dateTimeSave"><i class="fa fa-save"></i> <?php echo _('Save')?> </button>
			</div>
		</div>
	</div>
</div>
<!-- UNIT COLOR MODAL -->
<div class="modal fade" id="unitColorModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo _("Set unit color") ?></h4>
			</div>
			<div class="modal-body no-padding" >
				<div class="row">
					<div class="col-sm-12">
						<form class="smart-form" id="unit-color-form">
							<fieldset>
								<section>
									<label class="select">
										<?php echo colors_menu('unit_color', _(getUnitColor()), 'id="unit_color"');?> <i></i> 
									</label>
								</section>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel')?></button>
				<button type="button" class="btn btn-primary" id="unitColorSave"><i class="fa fa-save"></i> <?php echo _('Save')?> </button>
			</div>
		</div>
	</div>
</div>
<!-- SERIAL NUMBER MODAL -->
<div class="modal fade" id="unitSerialNumberModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo _("Set serial number") ?></h4>
			</div>
			<div class="modal-body no-padding" >
				<div class="row">
					<div class="col-sm-12">
						<form class="smart-form" id="unit-serial-number-form">
							<fieldset>
								<section>
									<label class="input">
										<input class="uppercase" data-mask="*****-***-*****" data-mask-placeholder= "_" type="text" name="unit_serial_number" id="unit_serial_number" value="<?php echo getSerialNumber();?>">
									</label>
								</section>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel')?></button>
				<button type="button" class="btn btn-primary" id="unitSerialNumberSave"><i class="fa fa-save"></i> <?php echo _('Save')?> </button>
			</div>
		</div>
	</div>
</div>
<!-- HOST NAME MODAL -->
<div class="modal fade" id="hostNameModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo _("Change a name to your FABtotum") ?></h4>
			</div>
			<div class="modal-body no-padding" >
				<div class="row">
					<div class="col-sm-12">
						<form class="smart-form" id="unit-serial-number-form">
							<fieldset>
								<section>
									<label class="label"><?php echo _("Name");?></label>
									<label class="input">
										<input type="text" id="dnssd-hostname" value="<?php echo getHostName(); ?>">
									</label>
								</section>
								<section>
									<label class="label"><?php echo _("Description");?></label>
									<label class="input">
										<input type="text" id="dnssd-name" value="<?php echo getAvahiServiceName(); ?>">
									</label>
								</section>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel')?></button>
				<button type="button" class="btn btn-primary" id="hostNameSave"><i class="fa fa-save"></i> <?php echo _('Save')?> </button>
			</div>
		</div>
	</div>
</div>
<!-- LANGUAGE MODAL -->
<div class="modal fade" id="languageModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo _("Set language") ?></h4>
			</div>
			<div class="modal-body no-padding" >
				<div class="row">
					<div class="col-sm-12">
						<form class="smart-form" id="language-form">
							<fieldset>
								<section>
									<label class="select">
										<?php echo langauges_menu('form-control', 'language-language', 'id="language-select"',getCurrentLanguage());?> <i></i>
									</label>
								</section>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Cancel')?></button>
				<button type="button" class="btn btn-primary" id="langaugeSave"><i class="fa fa-save"></i> <?php echo _('Save')?> </button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
