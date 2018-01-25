<div class="widget-body-toolbar">
	<div class="btn-group">
		<button class="btn btn-default" data-toggle="dropdown" id="date-picker">
			<i class="fa fa-calendar"></i> <span><?php echo  date('F j, Y', strtotime('today - 30 days')) .' - '.date('F j, Y', strtotime('today')) ?></span> <span class="caret"></span>
		</button>
	</div>
    
	<div class="btn-group">
		<button class="btn btn-default dropdown-toggle" data-toggle="dropdown"> <span id="ajax-type"><?php echo _("Make");?></span> <span class="caret"></span></button>
		<ul class="dropdown-menu">
			<?php foreach($makeList as  $key => $label): ?>
				<li>
					<a data-type="type" data-value="<?php echo $key ?>" href="javascript:void(0);"><?php echo  $label;?></a>
				</li>
			<?php endforeach;?>
			<li class="divider"></li>
			<li>
				<a  data-type="type" data-value="" href="javascript:void(0);"><?php echo _("Make");?></a>
			</li>
		</ul>
	</div>

	<div class="btn-group">
	
		<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			<span id="ajax-status"><?php echo _("Status");?></span> <span class="caret"></span>
		</button>
		<ul class="dropdown-menu">
			<li>
				<a  data-type="status" data-value="completed" href="javascript:void(0);"><?php echo _("Completed");?></a>
			</li>
			<li>
				<a  data-type="status" data-value="aborted" href="javascript:void(0);"><?php echo _("Aborted");?></a>
			</li>
			<li>
				<a  data-type="status" data-value="terminated" href="javascript:void(0);"><?php echo _("Terminated");?></a>
			</li>
			<li class="divider"></li>
			<li>
				<a  data-type="status" data-value="" href="javascript:void(0);"><?php echo _("Status");?></a>
			</li>
		</ul>
	</div>
</div>

<ul id="myTab1" class="nav nav-tabs tabs-pull-right">
	<li class="active">
		<a href="#s1" data-toggle="tab"><i class="fa fa-list"></i> <?php echo _("Tasks");?></a>
	</li>
	<li>
		<a id="stats-click" href="#s2" data-toggle="tab"><i class="fa fa-area-chart"></i> <?php echo _("Stats");?></a>
	</li>
</ul>

<div id="myTabContent1" class="tab-content">
	<div class="tab-pane fade in active" id="s1">
		<table class="table table-bordered table-striped" id="history">
			<thead>
				<tr>
					<th></th>
					<th><i class="fa fa-calendar"></i> <span class="hidden-xs"><?php echo _("When");?></span></th>
					<th><i class="fa fa-play fa-rotate-90 txt-color-blue"></i> <span class="hidden-xs"><?php echo _("Make");?></span></th>
					<th><?php echo _("Status");?></th>
					<th><?php echo _("Description");?></th>
					<th><i class="fa fa-clock-o"></i> <span class="hidden-xs"><?php echo _("Duration");?></span></th>
					<th class="hidden"></th>
					<th class="hidden"></th>
					<th class="hidden"></th>
					<th class="hidden"></th>
					<th class="hidden"></th>
					<th class="hidden"></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	
	<div class="tab-pane fade in padding-10" id="s2">
	</div>
</div>
