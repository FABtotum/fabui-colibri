<script type="text/javascript">
	
	$(function() {
		
		$(".action-button").on('click', confirmation_check);
		
	});
	
	function do_action(action, plugin_slug)
	{
		$(".action-button").addClass("disabled");
		
		$.ajax({
				type: "POST",
				url: "plugin/"+action+"/"+plugin_slug,
				dataType: 'json',
			}).done(function(response){
				
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
			do_action(action, plugin_slug);
		}
	
	}

</script>
