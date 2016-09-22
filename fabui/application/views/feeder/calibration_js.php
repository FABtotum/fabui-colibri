<script type="text/javascript">
    
    $(function () {
        $(".extrude").on("click", extrudeFilament);
        $(".recalculate").on('click', calculateStep);
        $(".step-change-modal-open").on('click', openModal);
        $("#change-extruder-step-value-button").on('click', changeExtruderStepValue);
    });
    
    /* */
    function extrudeFilament()
    {
        var button = $(this);
        button.html('<i class="fa fa-spin fa-spinner"></i> Extruding...');
        disableButton('.extrude');
        disableButton('.step-change-modal-open');
        $(".response-container").html('');
        $(".calc-row").slideUp(function(){});
        
        $.ajax({
            type: "POST",
            url: "<?php echo site_url("feeder/extrude") ?>/"+ $("#filament-to-extrude").val(),
            dataType: 'json'
        }).done(function( data ) {
            enableButton('.extrude');
            enableButton('.step-change-modal-open');
            $(".calc-row").slideDown(function(){
                $('.extrude').html('<i class="fab-lg fab-fw icon-fab-e"></i> Start to extrude');
            });
        });
        
    }
    
    /* */
    function calculateStep()
    {
        var button = $(this);
        button.html('<i class="fa fa-spin fa-spinner"></i> Calculating...');
        disableButton('.recalculate');
        disableButton('.extrude');
        disableButton('.step-change-modal-open');
        $(".response-container").html('');
        
        $.ajax({
            type: "POST",
            url: "<?php echo site_url("feeder/calculateStep") ?>/"
                + $("#actual-step").val() + "/" 
                + $("#filament-to-extrude").val() + "/"
                + $("#filament-extruded").val(),
            dataType: 'json'
        }).done(function( data ) {
            button.html('<i class="fa fa-calculator"></i> Calculate');
            enableButton('.recalculate');
            enableButton('.extrude');
            enableButton('.step-change-modal-open');
            
            var html = '<div class="alert alert-info animated fadeIn"> <strong>Calibration completed</strong> New value: <strong>' + data.new_step + '</strong></div>';
            //~ $(".calc-row").slideUp(function(){
                $(".response-container").html(html);
            //~ });
            $("#actual-step").val(data.new_step);
        });
    }
    
    /* */
    function openModal()
    {
            
        $("#feeder-step-new-value").val($("#actual-step").val());
        $('#change-value-modal').modal({
            keyboard : false
        });
    }
    
    /* */
    function changeExtruderStepValue()
    {
        $(".calc-row").slideUp(function(){});
        var button = $("#change-extruder-step-value-button");
        button.html('<i class="fa fa-spin fa-spinner"></i> Storing...');
        disableButton('.step-change-modal-cancel');
        var data = {
            action : 'change',
            new_step : $("#feeder-step-new-value").val()
        };
        
        $.ajax({
            type: "POST",
            url: "<?php echo site_url("feeder/changeStep") ?>/"
                + $("#feeder-step-new-value").val(),
            dataType: 'json'
        }).done(function( data ) {
            var button = $("#change-extruder-step-value-button");
            button.html('<i class="fa fa-check"></i>Change');
            enableButton('.step-change-modal-cancel');
            $('#change-value-modal').modal('hide');
            $("#actual-step").val(data.new_step);
        });
    }
</script> 
