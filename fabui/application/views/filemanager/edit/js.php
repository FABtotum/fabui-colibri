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
	});
	
	/**
	 * init dataTable
	 */
	function initTable()
	{
		filesTable = $('#files-table').dataTable({
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
			},
			"sAjaxSource": "<?php echo site_url("filemanager/getFiles/".$object['id']) ?>",
			"fnRowCallback": function (row, data, index ){
				
			}
		});
	}
	
</script>