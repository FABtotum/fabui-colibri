<div class="row">
	<div class="col-sm-6 margin-bottom-10">
		<h1 class="txt-color-blueDark"><i class="fa fa-play fa-rotate-90 fa-border"></i> FABtotum Personal Fabricator</h1>
	</div>
	<div class="col-sm-6 margin-bottom-10">
		<div class="well no-padding well-light">
			<table class="table table-striped table-condensed">
				<caption></caption>
				<tbody>
					<tr>
						<td><?php echo _("Os") ?></td>
						<td><span class="pull-right"><?php echo $os_info; ?></span></td>
					</tr>
					<tr>
						<td><?php echo _("Firmware") ?></td>
						<td><span class="pull-right">v<?php echo $fabtotum_info['fw']; ?></span></td>
					</tr>
					<tr>
						<td>FabUI</td>
						<td><span class="pull-right">v<?php echo $fabui_version?></span></td>
					</tr>
					<tr>
						<td><?php echo _("Hardware") ?></td>
						<td><span class="pull-right">v<?php echo $fabtotum_info['hw']; ?></span></td>
					</tr>
					<tr>
						<td><?php echo _("Installed head") ?></td>
						<td><span class="pull-right"><?php echo $unit_configs['hardware']['head']; ?></span></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<hr class="simple">
<div class="row">
	<div class="col-sm-6 margin-bottom-10">
		<h1 class="txt-color-blueDark"> <?php echo _("Board details") ?></h1>
	</div>
	<div class="col-sm-6 margin-bottom-10">
		<div class="well no-padding well-light">
			<table class="table table-striped table-condensed">
				<caption><?php echo _("RAM memory") ?></caption>
				<tbody>
					<tr>
						<td><?php echo _("Free") ?></td>
						<td><span class="pull-right"><?php echo floor($mem_free / 1024); ?> MB</span></td>
					</tr>
					<tr>
						<td><?php echo _("Total") ?></td>
						<td><span class="pull-right"><?php echo floor($mem_total / 1000); ?> MB</span></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 margin-bottom-10">
		<div class="well no-padding well-light">
			<table class="table table-striped">
				<caption><?php echo _("Hardware") ?></caption>
				<tbody>
					<tr>
						<td><?php echo _("Board") ?></td>
						<td><span class="pull-right"><?php echo $rpi_version?></span></td>
					</tr>
					<tr>
						<td><?php echo _("Time alive") ?></td>
						<td><span class="pull-right"><?php echo transformSeconds($time_alive)?></span></td>
					</tr>
					<tr>
						<td><?php echo _("Board temperature") ?></td>
						<td><span class="pull-right"><?php echo $temp . '&deg;'; ?></span></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-sm-6 margin-bottom-10">
		<div class="well no-padding well-light">
			<table class="table table-striped">
				<caption><?php echo _("Network") ?> 
					<span class="pull-right" style="margin-right: 5px;">(eth / wlan)</span>
				</caption>
				<tbody>
					<tr>
						<td><?php echo _("Down") ?> </td>
						<td>
							<span class="pull-right"><?php echo humanFileSize($eth_bytes[0]) ?> / <?php echo isset($wlan_bytes) ? humanFileSize($wlan_bytes[0]) : '.'?></span>
						</td>
					</tr>
					<tr>
						<td><?php echo _("Up") ?> </td>
						<td><span class="pull-right"><?php echo humanFileSize($eth_bytes[1])?> / <?php echo  isset($wlan_bytes) ? humanFileSize($wlan_bytes[1]): '.'?></span></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12 margin-bottom-10">
		<div class="well no-padding well-light">
			<table class="table table-striped">
				<caption><?php echo _("Storage") ?> </caption>
				<thead>
					<tr>
					<?php $col_count = 0; ?>
					<?php foreach($table_header as $header): ?>
						<?php if($header != ''): ?>
						<?php

						switch($col_count) {
							case 4 :
								$class = 'text-center';
								break;
							case 5 :
								$class = 'text-right';
								break;
							default :
								$class = '';
						}
						?>
						<th class="<?php echo $class; ?> th-border-top"><?php echo $header; ?></th>
						<?php $col_count++; ?>
						<?php endif; ?>
					<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach($table_rows as $row): ?>
						<tr>
							<?php $items = explode(' ', $row); ?>
							<?php $col_count = 0; ?>
							<?php foreach($items as $item): ?>
								<?php if($item!==""): ?>
									
									<?php
									switch($col_count) {
										case 4 :
											$class = 'text-center';
											$content = '<div class="progress"><div class="progress-bar bg-color-blue" data-transitiongoal="' . intval($item) . '" style=""></div></div>';
											break;
										case 5 :
											$class = 'text-right';
											$content = $item;
											break;
										default :
											$class = '';
											$content = $item;
									}
									?>
																
									<td class="<?php echo $class; ?>"><?php echo $content; ?></td>
									<?php $col_count++; ?>
								<?php endif; ?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
