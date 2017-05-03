<script type="text/javascript">

     $(function () {
        $(".prepare-engage").on('click', prepare);
     });
     
     function prepare(){

        openWait("<?php echo _("Preparing procedure");?>");
        IS_MACRO_ON = true;
        $.ajax({
              type: "POST",
              url: "<?php echo site_url("feeder/engage") ?>",
              dataType: 'json'
        }).done(function( data ) { 
            closeWait();
            
            if(data.response == 'success'){
                $(".step-1").hide();
                $(".step-2").show();
            }
            else
            {
                showErrorAlert('<?php echo _("Error") ?>', data.message);
            }
            
            

        });
        
     } 

</script>
