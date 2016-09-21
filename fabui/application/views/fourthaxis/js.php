<script type="text/javascript">


    $(function () {
        $(".do-engage").on('click', do_engage);
    });
    
    function do_engage()
    {
        openWait('Engaging in process');
        IS_MACRO_ON = true;
        
        var now = jQuery.now();
        
        $.ajax({
            type: "POST",
            url : "fourthaxis/engage",
            url : "<?php echo site_url("fourthaxis/engage") ?>/"+ now,
            dataType: "json"
        }).done(function( data ) {
            
            closeWait();
            IS_MACRO_ON = false;
            
        });
    }
    
</script>
