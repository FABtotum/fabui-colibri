<script type="text/javascript">
    
    var num_probes = 1;
    var skip_homing = 0;
    
    $(function () {
        $(".do-calibration").on('click', do_calibration);
    });
    
    function do_calibration()
    {
        openWait('<i class="fa fa-circle-o-notch fa-spin"></i> Calibration in process');
        var now = jQuery.now();
        
        $.ajax({
            type: "POST",
            url: "<?php echo site_url("bed/calibrate") ?>/"
                + now + "/" 
                + num_probes + "/"
                + skip_homing,
            dataType: "json"
        }).done(function( data ) {
            
            num_probes++;
            skip_homing = 1;
            closeWait();
            
            if($(".step-1").is(":visible") ){
                $(".step-1").slideUp('fast', function(){
                    $(".step-2").slideDown('fast');
                });
            }
            
            $(".result-response").html(data.html);
            
        });
    }
    
</script>
