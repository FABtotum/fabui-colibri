<?php
/**
 * 
 * @author Krios Mane
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 * List of all files
 * 
 */
 
?>
<?php if(!$runningTask): ?>
	<ul id="filesTab"  class="nav nav-tabs bordered">
		<li class="active">
			<a href="#files-tab" data-toggle="tab"><?php echo _("All files")?></a>
		</li>
		<?php if( isset($get_reacent_url) ): ?>
		<li class="">
			<a href="#recent-prints-tab" data-toggle="tab"><?php echo _("Recent")?></th></a>
		</li>
		<?php endif?>
	</ul>
	<div id="filesTabContent" class="tab-content ">
		<div class="tab-pane fade in active" id="files-tab">
			<div class="">
				<table id="files_table" class="table table-striped table-bordered table-hover smart-form has-tickbox cursor-pointer" width="100%">
					<thead>
						<tr>
							<th width="20" class="text-center"></th>
							<th><?php echo _("File");?></th>
							<th class="hidden-xs"><?php echo _("Project")?></th>
							<th class="hidden"></th>
							<th class="hidden"></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
		<?php if( isset($get_reacent_url) ): ?>
		<div class="tab-pane fade in" id="recent-prints-tab">
			<div>
				<table id="recent_files_table" class="table table-striped table-bordered table-hover smart-form has-tickbox cursor-pointer" width="100%">
					<thead>
						<tr>
							<th width="20" class="text-center"></th>
							<th><?php echo _("File");?></th>
							<th class="hidden-xs"><?php echo _("Project")?></th>
							<th class="hidden"></th>
							<th class="hidden"></th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
		<?php endif?>
	</div>
<?php endif;?>
