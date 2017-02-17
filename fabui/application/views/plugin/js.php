<script type="text/javascript">
	
	var installed_plugins = [ <?php foreach($installed_plugins as $plugin => $plugin_info) { echo '"'.$plugin.'", '; } ?> ];
	
	$(function() {
		
		initFieldValidation();
		$(".action-button").on('click', confirmationCheck);
		$("#install-button").on('click', doUpload);
		$(".plugin-adaptive-meta").on('input', pluginMetaChange);
		
		$("#plugin-file").on('change', function(){
			
			//~ var allowed_types = new Array('zip', 'xz' ,'gz', 'bz2', 'tgz');
			var allowed_types = new Array('zip');
			
			$(".type-warning").remove();
			$("#install-button").removeClass("disabled");
			
			
			var files = !!this.files ? this.files : [];
			
			var explode = files[0].name.split(".");
			
			var extension = explode[explode.length-1];
			
			if($.inArray(extension.toLowerCase(), allowed_types) == -1){
				
				$(".well").after('<div class="alert alert-warning type-warning"><i class="fa-fw fa fa-warning"></i>'+"<?php echo _("<strong>Warning</strong>: Only .zip files are allowed");?>"+'</div>');
				$(this).val("");
				$("#install-button").addClass("disabled");
			}
			
		});
		
		loadOnlinePlugins();
		
	});
	
	function loadOnlinePlugins()
	{
		$.get("<?php echo site_url('plugin/online') ?>", function(data, status){
			populateOnlineTable(data);
		});
	}
	/**
	*
	**/
	function populateOnlineTable(plugins)
	{
		if(plugins == false){
			$("#online-table").html('<h2 class="text-center"><i class="fa fa-warning"></i> <?php echo _(" No internet connection found") ?></h2><h6 class="text-center"><?php echo _("Check your connection or try again") ?></h6>');

		}else{
			var table_html = '<table class="table table-striped table-forum"><thead><tr>\
						<th>'+"<?php echo _("Plugin"); ?>"+'</th><th class="text-center hidden-xs">Version</th>\
						<th class="text-center hidden-xs">'+"<?php echo ("Author");?>"+'</th>\
					</tr></thead><tbody>';
			
			$.each(plugins, function(i, plugin) {
				table_html += '<tr><td><h4>' + plugin.name + '<small>' + plugin.desc + ' | <a class="no-ajax" target="_blank" href="'+plugin.url+'"> '+"<?php echo _("visit plugin site");?>"+'</a></small><p class="margin-top-10">';
				
				if( installed_plugins.indexOf(plugin.slug) == -1 )
				{
					table_html += '<button class="btn btn-xs btn-primary action-button" data-action="update" data-title="'+plugin.slug+'" " title="Install">' + "<?php echo _("Install");?>" + '</button>&nbsp;';
				}
				else
				{
					table_html += '<span class="label label-success">' + "<?php echo _("Installed");?>" + '</span>';
				}
				
				table_html += '</p></h4><p class="margin-top-10"></p></td><td class="text-center hidden-xs">' + plugin.version + '</td><td class="text-center hidden-xs"><a class="no-ajax" target="_blank" href="'+plugin.author_uri+'">'+plugin.author+'</a></td></tr>';
			});
			
			table_html += '</tbody></table>';
			$("#online-table").html(table_html);
			$(".action-button").on('click', confirmationCheck);
		}
	}
	
	function doAction(action, plugin_slug)
	{
		$(".action-button").addClass("disabled");
		console.log('ACTION', action, plugin_slug);
		
		$.ajax({
				type: "POST",
				url: "plugin/"+action+"/"+plugin_slug,
				dataType: 'json',
			}).done(function(response){
				
				console.log("doAction", response);
				
				$(".action-button").addClass("disabled");
				
				document.location.href="plugin";
				location.reload();
			});
	}
	
	function confirmationCheck()
	{
		var action = $( this ).attr('data-action');
		var plugin_slug = $( this ).attr('data-title');
		
		if( action == 'remove' )
		{
			var plugin_name = $( this ).attr('data-name');
			
			$.SmartMessageBox({
				title: "<?php echo _("Attention");?>!",
				content: "<?php echo _("Remove <strong>{0}</strong> plugin?");?>".format(plugin_name),
				buttons: '[<?php echo _("No")?>][<?php echo _("Yes")?>]'
			}, function(ButtonPressed) {
			   
				if (ButtonPressed === "<?php echo _("Yes")?>")
				{
					doAction(action, plugin_slug);
				}
				if (ButtonPressed === "<?php echo _("No")?>")
				{
					
				}
			});
		}
		else
		{
			if(action == 'update')
			{
				$(this).addClass('status-button');
				$(this).html('<?php echo _("Connecting");?>...');
			}
			doAction(action, plugin_slug);
		}
	
	}
	
	function doUpload()
	{
		openWait('<i class="fa fa-spinner fa-spin"></i>' + "<?php echo _("Uploading and installing plugin");?>...");
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
					waitContent("<?php echo _("Plugin installed successfully");?><br><?php echo _("Redirecting to plugins page");?>...");
					setTimeout(function(){
						location.reload();
					}, 2000);
				}
				else
				{
					closeWait();
					$.smallBox({
						title : "Error",
						content : '<?php echo _("Uploaded zip file is not a plugin archive");?>',
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
					$(".status-button").html('<i class="fa fa-download"></i> <?php echo _("Downloading");?> &nbsp;');
					break;
				case 'installing' :
					$(".status-button").html('<i class="fa fa-gear fa-spin"></i> <?php echo _("Installing");?> &nbsp;');
					break;
				case 'installed':
					$(".status-button").html('<i class="fa fa-check"></i> <?php echo _("Installed");?> &nbsp;');
					break;
			}
		}
	}
	
	function initFieldValidation()
	{
		console.log('initFieldValidation');
		
		jQuery.validator.addMethod("slugChecker", function(value, element, param) {
			if(!param)
				return true;
			
			if(value)
			{
				return !value.startsWith("plugin");
			}
			
			return true;
		}, 'The "plugin_" prefix is not allowed');


		$("#plugin-meta-form").validate({
			rules:{
				plugin_slug:{
					slugChecker: true
				},
				author_name:{
					required:true
				},
				plugin_description:{
					required:true
				}
			},
			messages: {
				plugin_slug: {
					slugChecker: "<?php echo _('\'plugin_\' prefix is not allowed');?>"
				},
				author_name:{
					required: "<?php echo _('Please enter your name here');?>"
				},
				plugin_description:{
					required: "<?php echo _('Please write a short description');?>"
				}
			},
			  submitHandler: function(form) {
				createNewPlugin();
			},
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
		
		$("#plugin-slug").inputmask("Regex");

	}
	
	function pluginMetaChange()
	{
		var id = $(this).attr('id');
		var name = $(this).attr('name');
		var value = $(this).val();
		
		console.log(id, name, value);
		if(id == "plugin-name")
		{
			var slug="my_new_plugin";
			if(value)
				slug = value.replace(/ /g, "_").toLowerCase();
			$("#plugin-slug").val(slug);
			$("#plugin-menu-0-title").val(value);
			$("#plugin-menu-0-url").val('/'+slug);
		}
		else if(id == "plugin-slug")
		{
			$("#plugin-menu-0-url").val('/'+value);
		}
	}
	
	function createNewPlugin()
	{
		var meta = getNewPluginMeta();
		console.log('META', meta);
		console.log('Form Submit handler');
		
		var data = {meta: meta};
		
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('plugin/create'); ?>',
			data : data,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			/*$.smallBox({
				title : "<?php echo _('Settings')?>",
				content : '<?php echo _('Hardware settings saved')?>',
				color : "#5384AF",
				timeout: 3000,
				icon : "fa fa-check bounce animated"
			});*/
			
			document.location.href = '<?php echo site_url('plugin/download/') ?>/' + response.slug;
			
		});
	}
	
	function getNewPluginMeta()
	{
		var meta = {};
		$(".new-plugin-meta :input").each(function (index, value) {
			var name = $(this).attr('name');
			var json_id = $(this).attr('id');
			var placeholder = $(this).attr('placeholder');
			var value = $(this).val();
			var type = $(this).attr('type');
			
			if(name)
			{
				if(value != "")
					meta[json_id] = $(this).val();
				else
					meta[json_id] = placeholder;
			}
			
			meta["plugin-menu-0-icon"] = "fa-cube";
			meta["plugin-menu-0-url"] = "plugin/" + meta["plugin-slug"];
		});
		
		//~ preset['pwm-off_during_travel'] = $("#off-during-travel").is(":checked")?true:false;

		return meta;
	}

</script>
