<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */

/* variable initialization */
if( !isset($safety_check) ) $safety_check = array( 'all_is_ok' => false, 'head_is_ok' => false, 'bed_is_ok' => false );
?>

<?php if(!$safety_check['all_is_ok']): ?>

<script type="text/javascript">
	
	$(document).ready(function() {
		setTimeout(checkSafety, 2000);
	});
	
	function checkSafety()
	{
		console.log("update safety");
		
		$.ajax({
			  type: "POST",
			  url: "<?php echo site_url($safety_check['url']) ?>",
			  dataType: 'json'
		}).done(function( data ) {
			// safety measure to stop the periodic check if the view was changed
			
			console.log(data);
			
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
		
		/*fabApp.jogMdi("M744\nM745", function(e){
			data.bed_in_place =  e[0].reply[0] == 'TRIGGERED' );
			console.log('HEAD', e[1].reply[0] == 'TRIGGERED' );
		});
		
		setTimeout(checkSafety, 1500);*/
		
	}
	
	function updateSafety()
	{
		
	}

</script>

<?php endif;?>
