<div class="row">
	<div class="col-sm-12">
	
		<div class="well">
			
			<?php if(count($installed_plugins) > 0): ?>
		
			<table class="table table-striped table-forum">
				<thead>
					<tr>
						<th>Plugin</th>
						<th class="text-center hidden-xs">Version</th>
						<th class="text-center hidden-xs">Author</th>
					</tr>
				</thead>
				
				<tbody>
				
				<?php foreach($installed_plugins as $plugin => $plugin_info): ?>
				
					<tr>
						<td>
							<h4>
								<a href="javascript:void(0)"><?php echo $plugin_info['name'] ?></a>
								<small><?php echo $plugin_info['description'] ?> | <a  target="_blank" href="<?php echo $plugin_info['plugin_uri'] ?>"> visit plugin site</a></small>
							</h4>
							<p class="margin-top-10">
								<?php if(isPluginActive($plugin)):  ?>
								<button class="btn btn-xs btn-warning action-button" data-action="deactivate" data-title="<?php echo $plugin ?>" title="Deactivate">Deactivate</button>	
							<?php else: ?>
								<button class="btn btn-xs btn-success action-button" data-action="activate" data-title="<?php echo $plugin ?>" " title="Activate">Activate</button>&nbsp;
								<button class="btn btn-xs btn-danger remove action-button" data-action="remove" data-title="<?php echo $plugin ?>" title="Remove">Remove</button>
							<?php endif; ?>
							</p>
						</td>
						<td class="text-center hidden-xs"><?php echo $plugin_info['version']; ?></td>
						<td class="text-center hidden-xs">
							<a target="_blank" href="<?php echo $plugin_info['author_uri'] ?>"><?php echo $plugin_info['author'] ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
						
				</tbody>
			
			</table>
			
			<?php else: ?>
				
				<h2 class="text-center"><i class="fa fa-plug"></i> No plugin installed</h2>
				<h6 class="text-center">Click "Add New Plugin" button to upload a new plugin</h6>
				
			<?php endif; ?>
		
		</div>
	</div>
</div>
