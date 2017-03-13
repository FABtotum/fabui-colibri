<?php
/**
 *
* @author Krios Mane
* @author FabTeam
* @version 0.1
* @license https://opensource.org/licenses/GPL-3.0
*
*/
?>
<div class="panel-group smart-accordion-default" id="accordion">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"> <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> Fabtotum Personal Fabricator </a></h4>
		</div>
		<div id="collapseOne" class="panel-collapse collapse in">
			<div class="panel-body ">
				 <dl class="dl-horizontal">
				 	<dt><?php echo _("Serial number");?></dt>
			        <dd><?php echo strtoupper(getSerialNumber()); ?></dd>
			        
			        <dt><?php echo _("Os");?></dt>
			        <dd><?php echo $os_info; ?></dd>
			        
			        <dt><?php echo _("Fabui");?></dt>
			        <dd><?php echo $bundles['fabui']['version']?></dd>
			        
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
			        <dd><?php echo $installed_head['name']; ?></dd>
      			</dl>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#collapse5" class="collapsed"> <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> <?php echo _("Print settings (M503 output)"); ?> </a></h4>
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
			<div class="panel-body">
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

