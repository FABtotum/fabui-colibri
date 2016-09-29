<script type="text/javascript">

    $(function () {
    $(".send-command").on('click', sendCommand);
    $(".exec-macro").on('click', execMacro);
    });

    function sendCommand()
    {

        $.ajax({
              type: "POST",
              data: {'code' : $('#command').val() },
              url: "<?php echo site_url("xmlrpc/doGCode") ?>",
              dataType: 'json'
        }).done(function( data ) { 
            $(".response-container").html( data.reply );
        });
        
    }
    
    function execMacro()
    {
        var macro = $('#macro').val()
        
        if( macro == 'probe_down' )
        {
            $('#macro').val('probe_up');
        }
        else if( macro == 'probe_up' )
        {
            $('#macro').val('probe_down');
        }
        
        $.ajax({
              type: "POST",
              data: {'macro' : macro, 'args' : null },
              url: "<?php echo site_url("xmlrpc/doMacro") ?>",
              dataType: 'json'
        }).done(function( data ) { 
            $(".response-container").html( JSON.stringify(data.reply) );
        });

    }
     
</script>
