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

