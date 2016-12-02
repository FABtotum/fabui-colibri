<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">

	var bandleStatus;
	
	$(document).ready(function() {
		checkBundleStatus();
		$("#details-button").on('click', showHideDetails);
		$("#check-again").on('click', checkBundleStatus);
	});	
	function checkBundleStatus()
	{
		$(".fabtotum-badge").removeClass('bg-color-green').removeClass('bg-color-orange').addClass('bg-color-blue');
		$("#badge-icon").html('<i class="fa  fa-spin fa-spinner txt-color-black"></i>');
		$("#status").html("Check for updates");
		$(".details").slideUp(function(){
			$("#details-button").html('show details <i class="fa fa-angle-double-down"></i>');
		});
		
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('updates/bundleStatus') ?>",
			dataType: 'json'
		}).done(function(response) {
			bandleStatus = response;
			var needUpdate = response.update;
			var badgeBgColor = 'green';
			var icon = 'check';
			var message = 'Great! Your FABtotum Personal Fabricator is up to date';
			
			if(needUpdate){
				badgeBgColor = 'orange';
				icon = 'warning';
				message = 'New important software updates are now available';
			}

			
			$("#status").html("Check complete!");
			$(".fabtotum-badge").removeClass('bg-color-blue').addClass('bg-color-' + badgeBgColor);
			$("#badge-icon").html('<i class="fa  fa fa-'+ icon +' txt-color-black"></i>');

			$("#response").html(message);

			var html = '<table class="table table-striped table-forum">';
			$.each(response.bundles, function(i, item) {
				
				var sign = item.update == true ? 'fa-times' : 'fa-check';
				var text_color = item.update == true ? 'text-danger' : 'text-success';
				html += '<tr>';
				html += '<td width="20"><i class="fa '+ sign +' '+ text_color +'"></i></td>';
				html += '<td><h4><a href="javascript:void(0)">' + i + '</a>' ;
				if(item.update == true){
					html += ' <small>You have version <b>'+ item.local +'</b> installed. Update to <b>' + item.latest + '</b>. <a class="changelog" data-attribute="' + i + '" href="javascript:void(0)">View details</a> </small>';
				}
				html += ' </td>';
				
				html += '</tr>';
				
			    console.log(i);
			});
			html += '</table>';

			$(".details").html(html);
			$(".changelog").click(showChangeLog);


		});
	}
	/**
	*
	*/
	function showHideDetails()
	{
		if($('.details').is(":visible")){
			$(".details").slideUp(function(){
				$("#details-button").html('show details <i class="fa fa-angle-double-down"></i>');
			});
			
		}else{
			$(".details").slideDown(function(){
				$("#details-button").html('hide details <i class="fa fa-angle-double-up"></i>');
			});
			
		}
	}
	/**
	*
	*/
	function showChangeLog()
	{
		var button = $(this);
		var bundle = button.attr('data-attribute');
		console.log(bandleStatus['bundles'][bundle]['changelog']);

		$("#changelog-modal-title").html(bundle + ' ' + bandleStatus['bundles'][bundle]['latest']);
		$('#changelog-modal-body').html(bandleStatus['bundles'][bundle]['changelog']);
		$('#changelog-modal').modal('show');
	}
</script>
