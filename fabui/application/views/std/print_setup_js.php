<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript">
	
	<?php if($this->session->settings['feeder']['show'] == true): ?>
	
	$(document).ready(function() {
		$(".engage-feeder").on('click', engageFeeder);
	});
	
	function engageFeeder(){
		openWait("<?php echo _("Preparing engage procedure");?>");
		$.ajax({
			  type: "POST",
			  url: "<?php echo site_url("feeder/engage") ?>",
			  dataType: 'json'
		}).done(function( data ) { 
			if(!data.response){
				$.smallBox({
					title : "<?php echo _("Warning");?>",
					content: data.trace,
					color : "#C46A69",
					icon : "fa fa-warning",
					timeout: 15000
				});
			}
			
			closeWait();

		});
		
		return false;
	} 
	
	<?php endif; ?>
	
</script>
