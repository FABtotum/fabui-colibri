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
								<a class="btn btn-xs btn-warning" href="<?php echo 'plugin/deactivate/'.$plugin ?>" title="Deactivate">Deactivate</a>	
							<?php else: ?>
								<a class="btn btn-xs btn-success" href="<?php echo 'plugin/activate/'.$plugin ?>" title="Activate">Activate</a>&nbsp;
								<a class="btn btn-xs btn-danger remove" data-title="<?php echo $plugin_info['name'] ?>"  data-href="<?php echo 'plugin/remove/'.$plugin ?>" title="Remove">Remove</a>
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
				<h6 class="text-center">Click "Add new" button to upload a new plugin</h6>
				
			<?php endif; ?>
		
		</div>
	</div>
</div>
