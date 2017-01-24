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

    var responsiveHelper_dt_basic = undefined;
	var filesTable; //table object
	var recentFilesTable; //table object
    var breakpointDefinition = { tablet : 1024, phone : 480};
    
	$(document).ready(function() {
		console.log('select_file_js: ready');
		initFilesTable();
		
		<?php if( isset($get_reacent_url) ): ?>
		initRecentFilesTable();
		<?php endif?>
	});
    
	function initFilesTable()
	{
		filesTable = $('#files_table').dataTable({
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"preDrawCallback" : function() {
				// Initialize the responsive datatables helper once.
				if (!responsiveHelper_dt_basic) {
					responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#files_table'), breakpointDefinition);
				}
			},
			"aaSorting": [],
			"rowCallback" : function(nRow) {
				responsiveHelper_dt_basic.createExpandIcon(nRow);
			},
			"drawCallback" : function(oSettings) {
				responsiveHelper_dt_basic.respond();
				initFilesTableEvents();
				if(idFile)
				{
					$(':radio[value='+idFile+']').attr('checked', 'checked');
				}
			},
			"sAjaxSource": "<?php echo site_url($get_files_url) ?>",
			"fnRowCallback": function (row, data, index ){
				$('td', row).eq(0).addClass('text-center');
				$('td', row).eq(2).addClass('hidden-xs');
				$('td', row).eq(3).addClass('hidden');
				$('td', row).eq(4).addClass('hidden');
			}
		});

	}
	
	<?php if( isset($get_reacent_url) ): ?>
	function initRecentFilesTable()
	{
		recentFilesTable = $('#recent_files_table').dataTable({
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
				"t"+
				"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"preDrawCallback" : function() {
				// Initialize the responsive datatables helper once.
				if (!responsiveHelper_dt_basic) {
					responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#recent_files_table'), breakpointDefinition);
				}
			},
			"rowCallback" : function(nRow) {
				responsiveHelper_dt_basic.createExpandIcon(nRow);
			},
			"drawCallback" : function(oSettings) {
				responsiveHelper_dt_basic.respond();
				initRecentFilesTableEvents();
			},
			"sAjaxSource": "<?php echo site_url($get_reacent_url) ?>",
			"fnRowCallback": function (row, data, index ){
				$('td', row).eq(0).addClass('text-center');
				$('td', row).eq(2).addClass('hidden-xs');
				$('td', row).eq(3).addClass('hidden');
				$('td', row).eq(4).addClass('hidden');
			}
		});
	}
	
	// recent files table event
	function initRecentFilesTableEvents()
	{
		$("#recent_files_table tbody > tr").on("click", function(){
			selectFile(this, 'recent_files_table');
		});
	}
	<?php endif?>
	
	//all files table event
	function initFilesTableEvents()
	{	
		$("#files_table tbody > tr").on("click", function(){
			selectFile(this, 'files_table');
		});
	}
	//select file by clicking on the row
	function selectFile(tr, tableID)
	{
		$("table input[type='radio']").removeAttr('checked');
		$("table tbody > tr").removeClass('bold-text txt-color-blueDark uppercase');
		$(tr).find("input[type='radio']").prop('checked', true);
		$(tr).addClass('bold-text txt-color-blueDark uppercase');
		idFile = $(tr).find("input[type='radio']").val();
		//im in firs step
		enableButton('.btn-next');
	}

</script>

