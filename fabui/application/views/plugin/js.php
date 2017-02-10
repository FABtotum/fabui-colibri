<script type="text/javascript">
	
	var installed_plugins = [ <?php foreach($installed_plugins as $plugin => $plugin_info) { echo '"'.$plugin.'", '; } ?> ];
	
	$(function() {
		
		$(".action-button").on('click', confirmation_check);
		
		$("#install-button").on('click', do_upload);
		
		$("#plugin-file").on('change', function(){
			
			//~ var allowed_types = new Array('zip', 'xz' ,'gz', 'bz2', 'tgz');
			var allowed_types = new Array('zip');
			
			$(".type-warning").remove();
			$("#install-button").removeClass("disabled");
			
			
			var files = !!this.files ? this.files : [];
			
			var explode = files[0].name.split(".");
			
			var extension = explode[explode.length-1];
			
			if($.inArray(extension.toLowerCase(), allowed_types) == -1){
				
				$(".well").after('<div class="alert alert-warning type-warning"><i class="fa-fw fa fa-warning"></i><strong>Warning</strong> Only .zip files are allowed</div>');
				$(this).val("");
				$("#install-button").addClass("disabled");
			}
			
		});
		
		load_online_plugins();
		
	});
	
	function load_online_plugins()
	{
		console.log(installed_plugins);
		$.get("<?php echo site_url('plugin/online') ?>", function(data, status){
			populate_online_table(data);
		});
	}
	
	function populate_online_table(plugins)
	{
		var table_html = '<table class="table table-striped table-forum"><thead><tr>\
					<th>Plugin</th><th class="text-center hidden-xs">Version</th>\
					<th class="text-center hidden-xs">Author</th>\
				</tr></thead><tbody>';
				
		$.each(plugins, function(i, plugin) {
			console.log('plugin', plugin);
			table_html += '<tr><td><h4>' + plugin.name + '<small>' + plugin.desc + ' | <a class="no-ajax" target="_blank" href="'+plugin.url+'"> visit plugin site</a></small><p class="margin-top-10">';
			
			//if( installed_plugins.indexOf(plugin.slug) == -1 )
			{
				table_html += '<button class="btn btn-xs btn-primary action-button" data-action="update" data-title="'+plugin.slug+'" " title="Install">Install</button>&nbsp;';
			}
			//else
			{
				//table_html += '<span class="label label-success">Installed</span>';
			}
			
			table_html += '</p></h4><p class="margin-top-10"></p></td><td class="text-center hidden-xs">' + plugin.version + '</td><td class="text-center hidden-xs"><a class="no-ajax" target="_blank" href="'+plugin.author_uri+'">'+plugin.author+'</a></td></tr>';
		});
		
		table_html += '</tbody></table>';
		$("#online-table").html(table_html);
		
		$(".action-button").on('click', confirmation_check);
	}
	
	function do_action(action, plugin_slug)
	{
		$(".action-button").addClass("disabled");
		console.log('ACTION', action, plugin_slug);
		
		$.ajax({
				type: "POST",
				url: "plugin/"+action+"/"+plugin_slug,
				dataType: 'json',
			}).done(function(response){
				
				console.log("do_action", response);
				
				$(".action-button").addClass("disabled");
				
				document.location.href="plugin";
				location.reload();
			});
	}
	
	function confirmation_check()
	{
		var action = $( this ).attr('data-action');
		var plugin_slug = $( this ).attr('data-title');
		
		
		if( action == 'remove' )
		{
			var plugin_name = $( this ).attr('data-name');
			
				$.SmartMessageBox({
					title: "Attention!",
					content: "Remove <b>" + plugin_name + " </b> plugin?",
					buttons: '[No][Yes]'
				}, function(ButtonPressed) {
				   
					if (ButtonPressed === "Yes")
					{
						do_action(action, plugin_slug);
					}
					if (ButtonPressed === "No")
					{
						/* do nothing */
					}
				});
		}
		else
		{
			if(action == 'update')
			{
				$(this).addClass('status-button');
				$(this).html('Connecting...');
			}
			do_action(action, plugin_slug);
		}
	
	}
	
	function do_upload()
	{
		openWait('<i class="fa fa-spinner fa-spin"></i> Uploading and installing plugin...');
		var pluginFile = $('#plugin-file').prop('files')[0];   
		var form_data = new FormData();                  
		form_data.append('plugin-file', pluginFile);
		console.log(form_data);                             
		$.ajax({
			url: '<?php echo site_url('plugin/doUpload') ?>',
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,                         
			type: 'post',
			success: function(response){
				console.log(response);
				if(response.installed == true){
					waitContent('Plugin installed successfully<br>Redirecting to plugins page...');
					setTimeout(function(){
						location.reload();
					}, 2000);
				}
				else
				{
					closeWait();
					$.smallBox({
						title : "Error",
						content : 'Uploaded zip file is not a plugin archive.',
						color : "#C46A69",
						timeout: 10000,
						icon : "fa fa-warning"
					});
				}
			}
		 });
	}

	if(typeof manageMonitor != 'function'){
		window.manageMonitor = function(data){
			handleTask(data);
		}
	}
	/**
	*
	**/
	function handleTask(data)
	{
		//handleUpdate(data.update);
		handleCurrent(data.update.current);
		//handleTaskStatus(data.task.status);
	}
	
	function handleCurrent(current)
	{
		if(current.status != ''){
			switch(current.status){
				case 'downloading' :
					$(".status-button").html('<i class="fa fa-download"></i> Downloading &nbsp;');
					break;
				case 'installing' :
					$(".status-button").html('<i class="fa fa-gear fa-spin"></i> Installing &nbsp;');
					break;
				case 'installed':
					$(".status-button").html('<i class="fa fa-check"></i> Installed &nbsp;');
					break;
			}
		}
	}

</script>
