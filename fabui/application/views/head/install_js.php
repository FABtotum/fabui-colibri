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

	 $(function () {
	 	
	 	$("#heads").on('change', set_head_img);
	 	$("#set-head").on('click', set_head);
	 	
	 	
	 	<?php if(isset($_REQUEST['head_installed']) && $units['hardware']['head'] != 'mill_v2'): ?>
	 		
	 		$.SmartMessageBox({
				title : "<i class='fa fa-warning'></i> New head has been installed, it is recommended to repeat the Probe Calibration operation",
				buttons : '[<i class="fa fa-crosshairs"></i> Calibrate][Ignore]'
			}, function(ButtonPressed) {
				if(ButtonPressed === "Calibrate") {	
						document.location.href="<?php echo site_url('maintenance/probe-length-calibration'); ?>";
						location.reload();
				}
				if (ButtonPressed === "Ignore") {
					
				}
		
			});
	 	
	 	<?php endif; ?>
	 	
	 	
	 });

	 function set_head_img(){
	 	
	 	$(".jumbotron").html('');
	 	
	 	$("#head_img").parent().attr('href', 'javascript:void(0);');
	 	$("#head_img").css('cursor', 'default');
	 	$("#set-head").prop("disabled",false);
	 	
		$("#head_img").attr('src', '/assets/img/head/' + $(this).val() + '.png');
		
		if($("#" + $(this).val() + "_description").length > 0){
			$(".jumbotron").html($("#" + $(this).val() + "_description").html());
		}
		
		if($(this).val() == 'more_heads'){
			$("#head_img").parent().attr('href', 'https://store.fabtotum.com?from=fabui&module=maintenance&section=head');
	 		$("#head_img").css('cursor', 'pointer');
	 		$("#set-head").prop("disabled",true);
		}
		
		if($(this).val() == 'head_shape'){
			$("#set-head").prop("disabled",true);
		}
	 }

	function set_head(){
	 	if($("#heads").val() == 'head_shape'){
	 		alert('Please select a Head');
	 		return false;
	 	}
	 	
	 	openWait('<i class="fa fa-circle-o-notch fa-spin"></i> Installing head');
	 	
	 	$.ajax({
			type: "POST",
			url: "<?php echo site_url("head/setHead") ?>/"+ $("#heads").val(),
			dataType: 'json'
		}).done(function( data ) {
			
			$(".alerts-container").find('div:first-child').remove();
			$(".alerts-container").append('<div class="alert alert-success animated  fadeIn" role="alert"><i class="fa fa-check"></i> Well done! Now your <strong>FABtotum Personal Fabricator</strong> is set for the <strong>'+ data.name +'</strong>.</div>');
			
			waitContent('Well done! Now your <strong><i>FABtotum Personal Fabricator</i></strong> is configured to use <strong><i>'+ data.name+'</i></strong>.');
			
			setTimeout(function(){
					document.location.href =  '<?php echo site_url('head/index/install'); ?>?head_installed';
					location.reload();
				}, 2000
			);
			
		});
	}
	
</script>
