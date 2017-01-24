<?php
/**
 * 
 * @author Daniel Kesler
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
/* variable initialization */

if(!isset($task_jump_new)) $task_jump_new = 1;
if(!isset($task_jump_restart)) $task_jump_restart = 1;

?>
<script type="text/javascript">
	
	$(document).ready(function() {
		$(".new-task").on('click', function(){$('.wizard').wizard('selectedItem', { step: <?php echo $task_jump_new; ?> });});
		$(".restart-task").on('click', function(){$('.wizard').wizard('selectedItem', { step: <?php echo $task_jump_restart; ?> });});
		$(".save-z-height").on('click', saveZHeight);
		$("input[name='quality']").on('click', qualityRating);
	});
	
	/**
	* save and override z height
	*/
	function saveZHeight()
	{
		disableButton('.save-z-height');
		$.ajax({
			type: 'post',
			url: '<?php echo site_url('probe/overrideLenght'); ?>/' + zOverride,
			dataType: 'json'
		}).done(function(response) {
			console.log(response);
			showActionAlert("Z's Height saved");
			enableButton('.save-z-height');
		});
	}
	
	function qualityRating(e)
	{
		var element = $(this);
		id = element.attr('id');
		var rating = id.split('-')[1];
		
		var data = {};
		$.ajax({
			type: 'post',
			data: data,
			url: '<?php echo site_url('create/saveQualityRating'); ?>/' + idTask + '/' + rating,
			dataType: 'json'
		}).done(function(response) {
			// do nothing
			console.log('save quality rating', response);
		});
	}
	
</script>
