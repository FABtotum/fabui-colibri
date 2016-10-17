<script type="text/javascript">
	
	$(function() {
		
		$(".action-button").on('click', do_action);
		
	});
	
	function do_action()
	{
		$(".action-button").addClass("disabled");
		
		var action = $( this ).attr('data-action');
		var plugin_slug = $( this ).attr('data-title');

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

</script>
