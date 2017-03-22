<?php
/**
 * 
 * @author Daniel Kesler
 * @version 1.0
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
/* variable initialization */
$this->load->helper('std_helper');
?>

<script type="text/javascript">
	var selected_head = "<?php echo $head?>";
	
	$(function () {
		$("#heads").on('change', set_head_img);
		$("#heads").trigger('change');
	});

	function set_head_img(){
		selected_head = $(this).val();
		var head = heads[selected_head];
		
		if(heads.hasOwnProperty(selected_head))
		{
			$("#edit-button").show();
			$("#remove-button").show();
			//~ var head = heads[selected_head];
			if( head.fw_id < 100 )
				$("#remove-button").hide();
		}
		else
		{
			$("#edit-button").hide();
			$("#remove-button").hide();
		}
		
		$(".jumbotron").html('');
		
		$("#head_img").parent().attr('href', 'javascript:void(0);');
		$("#head_img").css('cursor', 'default');
		$("#set-head").prop("disabled",false);
		
		$("#head_img").attr('src', '/assets/img/head/' + $(this).val() + '.png');
		
		//~ if($("#" + $(this).val() + "_description").length > 0){
		var html = '<p class="margin-bottom-10">' + head.description + '</p>';
		
		if(head.link)
		{
			html += '<a style="padding: 6px 12px;" target="_blank" href="'+head.link+'" class="btn btn-default no-ajax">More details</a>';
		}
			
		$(".jumbotron").html(html);
		
		if($(this).val() == 'more_heads'){
			$("#head_img").parent().attr('href', 'https://store.fabtotum.com?from=fabui&module=maintenance&section=head');
			$("#head_img").css('cursor', 'pointer');
	 		$("#set-head").prop("disabled",true);
		}
		if($(this).val() == 'head_shape'){
			$("#set-head").prop("disabled",true);
		}
	 }
    
</script>
