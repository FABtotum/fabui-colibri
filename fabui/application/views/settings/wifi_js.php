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
	$(document).ready(function() {
		scan();
	});
	
	/**
	 * scan wifi networks
	 */
	function scan()
	{
		$.ajax({
			type: 'get',
			url: '<?php echo site_url('settings/scanWifi'); ?>',
			dataType: 'json'
		}).done(function(response) {
			buildTable(response);
		});
	}
	
	/**
	 * 
	 */
	function buildTable(nets)
	{
		var table = '<table class="table table-striped table-forum"><tbody>';
		$.each(nets, function( index, net ) {
  			
  			var protected = net.encryption_key == 'on' ? 'Protected <i class="fa fa-lock"></i>' : '';
  			var channel = net.channel != '' ? '( Channel ' + net.channel + ')' : '';
  			
  			table += '<tr>';
  			table += '<td class="text-center" style="width: 40px;"><i class="icon-communication-035 fa-2x text-muted"></i></td>';
  			table += '<td>';
  			table += '<p>'+net.essid+'<span class="hidden-xs pull-right">Signal level: '+net.signal_level +'/100</span></p>';
  			table += '<div class="hidden-xs progress progress-sm progress-striped active"><div class="progress-bar  bg-color-blue" aria-valuetransitiongoal="'+net.signal_level +'"></div></div>';
  			table += '<small class="hidden-xs note">'+protected+' ' + net.protocol + ' / ' + net.mode +' / ' + net.frequency + ' ' + channel + ' </small>';
  			table += '</td>';
  			table += '<td style="width: 100px" class="text-right va-middle"><button class="btn btn-default btn-sm btn-block">Connect</button></td></td>';
  			table += '</tr>';
		});
		table += '</tbody></table>';
		$(".table-container").html(table);
		$('.progress-bar').progressbar();
	}
</script>