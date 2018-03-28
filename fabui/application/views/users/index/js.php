<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
$is_admin = $this->session->user['role'] == 'administrator';
?>
<script type="text/javascript">
    var responsiveHelper_dt_basic = undefined;
    var breakpointDefinition = { tablet : 1024, phone : 480};
    var usersTable;
    var users = new Array();

    $(document).ready(function() {
    	initTable();
    	<?php if($is_admin): ?>
    	$("#trasnfer-data").on('click', handleTransferData);
    	$("#delete-user-button").on('click', askConfirmDeleteUser);
    	<?php endif; ?>
    });

    /**
    *
    */
    function initTable()
    {
    	usersTable = $('#users-table').dataTable({
			
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
					responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#users-table'), breakpointDefinition);
				}
			},
			"aaSorting": [],
			"rowCallback" : function(nRow) {
				responsiveHelper_dt_basic.createExpandIcon(nRow);
			},
			"drawCallback" : function(oSettings) {
				
				transformLinks($('#users-table'));
				<?php if($is_admin):?>
					$(".reset-password").on('click', askResetPassword);
					$(".delete-user").on('click', showDeleteUserModal);
				<?php endif; ?>
			},
			"sAjaxSource": "<?php echo site_url('users/getUsers') ?>",
			"fnRowCallback": function (row, data, index ){
			},
			"initComplete" : function( settings, json) {
				users = json.aaData;
				console.log(users);
			}
		});
    }

    <?php if($is_admin):?>
    /**
    *
    */
    function askResetPassword()
    {
        var button = $(this);
        var email  = button.attr('data-email');

        $.SmartMessageBox({
            title: "<i class='fa fa-key'></i> <span class='txt-color-orangeDark'><strong><?php echo _("Reset password");?></strong></span> ",
            content: "<?php echo _("An email with reset instructions will be sent.<br>Continue?");?>",
            buttons: "[<?php echo _("Yes");?>][<?php echo _("No");?>]"
        }, function(ButtonPressed) {

            if(ButtonPressed == '<?php echo _("Yes");?>'){
                resetPassword(email);
            }
       });
    }

    /**
    *
    **/
    function resetPassword(email)
    {
        openWait("<i class=\"fas fa-spin fa-cog\"></i> <?php echo _("Sending email");?>", "<?php echo _("Please wait..") ?>", false);
        $.ajax({
			type: "POST",
			url: "<?php echo site_url('login/sendResetEmail') ?>",
			dataType: 'json',
			data: {email: email}
		}).done(function(response) {

			closeWait();
			if(response.sent == true){

				$.smallBox({
					title : _("Success"),
					content : "<i class='fa fa-check'></i> <?php echo _("A message was sent to that address containing the instructions to reset the password ") ?>",
					color : "#659265",
					iconSmall : "fa fa-thumbs-up bounce animated",
					timeout : 4000
				});
			}
			
		});
    }

    /**
    *
    */
    function showDeleteUserModal()
    {
    	var button = $(this);
        var id  = button.attr('data-id');
        
        populateUserCombo(id);
        $("#delete-user-button").attr("data-user-id", id);
        
    	$('#deleteUserModal').modal({
			keyboard: false,
		});
    }

    /**
    *
    **/
    function populateUserCombo(exceptID){
        $("#users").html();
    	$.each( users, function( index, user ) {
        	if(user[7] != exceptID){
            	$("#users").append('<option value="'+user[7]+'">'+user[2]+'</option>');
        	}
    	});
    }

    /**
    *
    **/
    function handleTransferData()
    {
        var input = $(this);
        if(input.is(':checked')){
            $(".list-users-container").slideDown();
        }else{
        	 $(".list-users-container").slideUp();
        }
    }

    /**
    *
    **/
    function askConfirmDeleteUser()
    {
       	if($("#trasnfer-data").is(':checked')){
           	var content = "<?php echo  _("The user will be deleted and all its data will be transfered to:");?> <br> " + $("#users option:selected").text();
       	}else{
           	var content = "<?php echo _("The user and all its data will be permanently deleted"); ?>";
       	}
    	$.SmartMessageBox({
            title: "<i class='fa fa fa-exclamation-triangle'></i> <span class='txt-color-orangeDark'><strong><?php echo _("Warning");?></strong></span> ",
            content: "<p class='font-md'>" + content + "</p>",
            buttons: "[<?php echo _("Yes");?>][<?php echo _("No");?>]"
        }, function(ButtonPressed) {
            if(ButtonPressed == '<?php echo _("Yes");?>'){
            	deleteUser();
            }
       });    
    }

    /**
    *
    **/
    function deleteUser()
    {
    	openWait("<i class=\"fas fa-spin fa-cog\"></i> <?php echo _("Processing...");?>", "<?php echo _("Please wait..") ?>", false);
        var user_id      = $("#delete-user-button").attr("data-user-id");
        var new_owner_id = $("#trasnfer-data").is(':checked') ? $("#users").val() : '';
		
        $.ajax({
			url : "<?php echo site_url("users/deleteUser"); ?>/" + user_id + "/" + new_owner_id,
			type : 'POST',
			dataType : 'json'
		}).done(function(response) {
			openWait("<i class=\"fas fa-check\"></i> <?php echo _("Completed");?>", "<?php echo _("Reloading page") ?><br><?php echo _("Please wait..") ?>", false);
			setTimeout(function(){
				location.reload();
			}, 2000);
			
		});
        
    }
    <?php endif;?>
</script>
