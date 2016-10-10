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
	var objectsTable;
	
	$(document).ready(function() {
		initTable();
		$("#selectAll").on('click', function(){
			var that = this;
			$(this).closest("table").find("tr > td input:checkbox").each(function() {
				this.checked = that.checked;
			});
		});
		
		$(".bulk-button").on('click', bulk_actions);
	});
	
	/**
	 * init dataTable
	 */
	function initTable()
	{
		objectsTable = $('#objects-table').dataTable({
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"preDrawCallback" : function() {
				// Initialize the responsive datatables helper once.
				if (!responsiveHelper_dt_basic) {
					responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#objects-table'), breakpointDefinition);
				}
			},
			"aaSorting": [],
			"rowCallback" : function(nRow) {
				responsiveHelper_dt_basic.createExpandIcon(nRow);
			},
			"drawCallback" : function(oSettings) {
				transformLinks($('#objects-table'));
			},
			"sAjaxSource": "<?php echo site_url('filemanager/getUserObjects/') ?>",
			"fnRowCallback": function (row, data, index ){
				$('td', row).eq(0).addClass('center table-checkbox').attr('width', '20px');
				$('td', row).eq(2).addClass('hidden-xs');
				$('td', row).eq(3).addClass('center hidden-xs').attr('width', '20px');
			}
		});
	}
	
	function bulk_actions(){
		console.log('bulk_actions');
		
		var action = $( this ).attr('data-action');
		
		if(action == ""){
			show_message("Please select an action");
			return false;
		}
		
		switch(action){
			case 'delete':
				bulk_delete();
				break;
			case 'download':
				bulk_download();
				break;
		}	
	}
	
	function bulk_delete()
	{
		var ids = new Array();
		
		var boxes = $(".table tbody").find(":checkbox:checked");
		
		if(boxes.length > 0){
			boxes.each(function() {
				ids.push($(this).attr("id").replace("check_", ""));
			});
			bulk_ask_delete(ids);
		}
		else
		{
			show_message("Please select at least 1 object");
			return false;
		}

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

	function show_message(message){
		$.SmartMessageBox({
				title: "<i class='fa fa-info-circle'></i> Information",
				content: message,
				buttons: '[Ok]'
			}, function(ButtonPressed) {
				if (ButtonPressed === "OK") 
				{
				}
			});
	}

	function bulk_ask_delete(ids){
		$.SmartMessageBox({
				title: "<i class='fa fa-warning txt-color-orangeDark'></i> Warning!",
				content: "Do you really want to remove the selected objects",
				buttons: '[No][Yes]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "Yes") 
			{
				delete_objects(ids);
			}
			if (ButtonPressed === "No")
			{

			}
		});
	}

	function delete_objects(list){
		
		$(".bulk-button").addClass("disabled");
		$(".bulk-button[data-action='delete']").html("<i class='fa fa-spinner'></i> Deleting...");
		
		$.ajax({
				type: "POST",
				url: "<?php echo site_url('filemanager/deleteObjects') ?>",
				dataType: 'json',
				data: {ids: list}
			}).done(function(response) {
				
				if (response.success == true)
				{
					objectsTable._fnAjaxUpdate();
				}
				else
				{
					showErrorAlert('Error deleting object', response.message);
				}
				
				$(".bulk-button[data-action='delete']").html("<i class='fa fa-trash'></i> Delete");
				$(".bulk-button").removeClass("disabled");
				
			});

    }

	function bulk_ask_download(ids){
		$.SmartMessageBox({
				title: "<i class='fa fa-warning txt-color-orangeDark'></i> Warning!",
				content: "Do you really want download the selected objects",
				buttons: '[No][Yes]'
		}, function(ButtonPressed) {
			if (ButtonPressed === "Yes") {
				download_objects(ids);
			}
			if (ButtonPressed === "No") {

			}
		});
	}
	
	function download_objects(list){  	
		document.location.href = '<?php echo site_url('filemanager/download/object/') ?>/' + list.join('-');
	}
    

</script>
