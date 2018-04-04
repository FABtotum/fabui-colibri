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
	var responsiveHelper_dt_basic = undefined;
	var breakpointDefinition = { tablet : 1024, phone : 480};
	var filesTable;
	
	$(document).ready(function() {
		initTable();
		$("#selectAll").on('click', function(){
			var that = this;
			$(this).closest("table").find("tr > td input:checkbox").each(function() {
				this.checked = that.checked;			
			});
		});
		
		$('#save-object').on('click', save_object);
		$(".bulk-button").on('click', bulk_actions);
	});
	
	/**
	 * init dataTable
	 */
	function initTable()
	{
		filesTable = $('#files-table').dataTable({
			"language": {
                "url": "assets/js/plugin/datatables/lang/<?php echo getCurrentLanguage();?>"
            },
            
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"preDrawCallback" : function() {
				// Initialize the responsive datatables helper once.
				if (!responsiveHelper_dt_basic) {
					responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#files-table'), breakpointDefinition);
				}
			},
			"aaSorting": [],
			"rowCallback" : function(nRow) {
				responsiveHelper_dt_basic.createExpandIcon(nRow);
			},
			"drawCallback" : function(oSettings) {
				transformLinks($('#files-table'));
				$(".file-action").on('click', do_file_action);
				pageSetUp();
			},
			"sAjaxSource": "<?php echo site_url("projectsmanager/getFiles/".$object['id']) ?>",
			"fnRowCallback": function (row, data, index ){

				
				$('td', row).eq(2).addClass('hidden-xs');
				$('td', row).eq(3).addClass('hidden-xs');
				$('td', row).eq(4).addClass('hidden-xs');
			}
		});
	}
	
	function save_object(){
		
		$("#save-object").addClass('disabled');
		$('#save-object').html('<i class="fa fa-save"></i> <?php echo _("Saving") ?>...');
		
		var object_data = {
			object_id : 	<?php echo $object['id'] ?>, 
			name: 			$("#obj_name").val(), 
			description: 	$("#obj_description").val(), 
			public: 		$('[name="public"]:checked').val()
			};
		
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('projectsmanager/updateObject') ?>",
			data: object_data,
			dataType: 'json'
		}).done(function(response) {
			$("#save-object").removeClass('disabled');
			$('#save-object').html('<i class="fa fa-save"></i> <?php echo _("Save") ?>');
			
			$.smallBox({
				title : "<?php echo _("Object saved with success") ?>",
				color : "#659265",
				iconSmall : "fa fa-check bounce animated",
				timeout : 4000
			});
			
			$("#label-obj-name").html($("#obj_name").val());
		});
		
	}
	
	function ask_delete(id_file, file_name) {
		
		$.SmartMessageBox({
			title: "<?php echo _("Warning") ?>!",
			content: "<?php echo _("Remove") ?> <b>" + file_name + "</b> ?",
			buttons: '[<?php echo _("No") ?>][<?php echo _("Yes") ?>]'
		}, function(ButtonPressed) {
		   
			if (ButtonPressed === "<?php echo _("Yes") ?>")
			{
				delete_file(id_file);
			}
			if (ButtonPressed === "<?php echo _("No") ?>")
			{
				
			}
		});

	}
	
	function delete_file(id_file) {
		
		openWait('<?php echo _("Deleting file") ?>..');
		
		var ids = new Array();
		ids.push(id_file);
		
		delete_files(ids);
	}
	
	function bulk_actions(){
			
		var action = $( this ).attr('data-action');
		
		if(action == "")
		{
			show_message("");
			return false;
		}
		
		switch(action)
		{
			case 'delete':
				bulk_delete();
				break;
			case 'download':
				bulk_download();
				break;
		}
	}
	
	function delete_files(list){
		
		$(".bulk-button").addClass("disabled");
		$(".bulk-button[data-action='delete']").html("<?php echo _("Deleting") ?>...");

		$.ajax({
				type: "POST",
				url: "<?php echo site_url('projectsmanager/deleteFiles') ?>",
				dataType: 'json',
				data: {ids: list}
			}).done(function(response) {
				if (response.success == true)
				{
					filesTable._fnAjaxUpdate();
				}
				else 
				{
					fabApp.showErrorAlert(response.message, "<?php echo _("Error deleting file(s)") ?>");
				}
				
				$(".bulk-button[data-action='delete']").html("<i class='fa fa-trash'></i> <?php echo _("Delete") ?>");
				$(".bulk-button").removeClass("disabled");
			});
	}
	
	function bulk_delete(){
			
		var ids = new Array();
		
		var boxes = $(".table tbody").find(":checkbox:checked");
		
		if(boxes.length > 0)
		{
					
			boxes.each(function() {
				ids.push($(this).attr("id").replace("check_", ""));
			});
			
			bulk_ask_delete(ids);
		}
		else
		{
			show_message("<?php echo _("Please select at least 1 file") ?>");
			return false;
		}
	}
	
	function show_message(message){
		
		$.SmartMessageBox({
				title: "<i class='fa fa-info-circle'></i> <?php echo _("Information") ?>",
				content: message,
				buttons: '[<?php echo _("Ok") ?>]'
			}, function(ButtonPressed) {
				if (ButtonPressed === "<?php echo _("Ok") ?>") {
				}
				
			});
		
	}
	
	function bulk_ask_delete(ids){
		$.SmartMessageBox({
				title: "<i class='fa fa-exclamation-triangle txt-color-orangeDark'></i> <?php echo _("Warning") ?>!",
				content: "<?php echo _("Do you really want to remove the selected files?") ?>",
				buttons: '[<?php echo _("No") ?>][<?php echo _("Yes") ?>]'
			}, function(ButtonPressed) {
				if (ButtonPressed === "<?php echo _("Yes") ?>")
				{
					delete_files(ids);
				}
				if (ButtonPressed === "<?php echo _("No") ?>")
				{
					
				}
			});
	}
	
	function bulk_download(){
		var ids = new Array();
		var boxes = $(".table tbody").find(":checkbox:checked");
		
		if(boxes.length > 0)
		{
			boxes.each(function() {
				ids.push($(this).attr("id").replace("check_", ""));
			});		
			bulk_ask_download(ids);
		}
		else
		{
			 show_message("Please select at least 1 object");
			 return false;
		}
	}
   	
	function bulk_ask_download(ids){
		$.SmartMessageBox({
				title: "<i class='fa fa-exclamation-triangle txt-color-orangeDark'></i> <?php echo _("Warning") ?>!",
				content: "<?php echo _("Do you really want download the selected files?") ?>",
				buttons: '[<?php echo _("No") ?>][<?php echo _("Yes") ?>]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "<?php echo _("Yes") ?>")
			{
				download_files(ids);
			}
			if (ButtonPressed === "<?php echo _("No") ?>")
			{
				
			}
		});
	}
	
	function download_files(list){
		document.location.href = '<?php echo site_url('projectsmanager/download/file/') ?>/' + list.join('-');
	}
	
	function do_file_action(){
		var url = $(this).attr("data-action");
		document.location.href = $(this).attr("data-action");
		//~ location.reload();
	}
	
</script>
