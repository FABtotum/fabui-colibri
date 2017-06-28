<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

/* variable initialization */
if( !isset($safety_check) ) $safety_check = array( 'all_is_ok' => false, 'head_is_ok' => false, 'bed_is_ok' => false , 'bed_enabled' => true, 'url' => '' );
?>

<?php if(!$safety_check['all_is_ok']): ?>

<script type="text/javascript">
	
	window.safety_check_url = "<?php echo $safety_check['url']?>";
	
	$(document).ready(function() {
		setTimeout(checkSafety, 2000);
	});
	
	function checkSafety()
	{
		// safety measure to stop the periodic check if the view was changed
		// ensure that only one safety check can be active at a time
		if( safety_check_url != "<?php echo $safety_check['url']?>")
			return;
		
		$.ajax({
			  type: "POST",
			  url: "<?php echo site_url($safety_check['url']) ?>",
			  dataType: 'json'
		}).done(function( data ) {
			
			if($('#safety-check-bed-image').length === 0)
				return;
				
			if($('#safety-check-head-image').length === 0)
				return;
			
			// Bed
			$('#safety-check-bed-image').attr('src', '/assets/img/controllers/bed/hybrid_bed_' + (data.bed_in_place?"glass":"mill") + '.png');
			
			var message = '<h4><strong>'+("Bed inserted incorrectly")+'</strong> <i class="fa fa-times-circle text-danger fa-2x"></i></h4>';
			message += '<h3>'+  _("Please flip the bed to the other side.") + '</h3>';
			
			if(data.bed_is_ok)
			{
				message = '<h4><strong>'+_("Bed inserted correctly")+'</strong> <i class="fa fa-check-circle text-success fa-2x"></i></h4>';
			}
			
			$('#safety-check-bed-message').html(message);
			
			// Head
			$('#safety-check-head-image').attr('src', (data.head_in_place?data.head_info.image_src:"/assets/img/head/head_shape.png"));
			
			message = '<h4><strong>'+ (data.head_in_place?_("Wrong head installed"):_("No head installed")) +'</strong> <i class="fa fa-times-circle text-danger fa-2x"></i></h4>';
			message += '<h3>' + _("Please install a laser head.") + '</h3>'
			
			if(data.head_is_ok)
			{
				message = '<h4><strong>' + _("Correct head installed") + '</strong> <i class="fa fa-check-circle text-success fa-2x"></i></h4>';
			}
			
			if(data.all_is_ok)
			{
				$('#safety-check-content').hide();
				$('#task-wizard-content').show();
			}
			else
			{
				setTimeout(checkSafety, 1500);
			}
		});

		
	}

</script>

<?php endif;?>
