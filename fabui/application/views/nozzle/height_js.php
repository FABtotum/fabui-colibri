<script type="text/javascript">

    var choice = '';
    var probe_length = 0;
    var interval;

    var z_max_offset = 0;
    var z_probe_offset = 0;
    var z_nozzle_offset = 0;

    $(function () {
       
        
        $(".jog").addClass("disabled");
        
        $("#probe-calibration-prepare").on('click', prepare);
        $("#probe-calibration-calibrate").on('click',  calibrate);
        $(".calibrate-again").on('click', do_again);
        
        $(".z-action").on('click', move_z);
        
        $("#z-value").spinner({
                step : 0.01,
                numberFormat : "n",
                max: 1,
                min: 0
        });
        
        $('.change-over').on({
            
          mousedown : function () {
            
            
            var over = parseFloat($("#over").val()).toFixed(2);
            
            if(over >= -2 && over <=  2){
                
                var action = $(this).attr("data-action");
            
                over = eval(parseFloat(over) + action + '0.01');
            
                $("#over").val(over.toFixed(2));
                
                interval = window.setInterval(function(){
                
                     if(over >= -2 && over <=  2){
                        over = eval(parseFloat(over) + action + '0.01');
                        $("#over").val(over.toFixed(2));
                    }
                    
                }, 100);
                
            }
            
          },
          mouseup : function () {
            window.clearInterval(interval);
          }
        });
        
        $(".choice-button").on('click', function (){
                
            choice = $(this).attr('data-action');
            
            if(choice == 'normal'){
                $( ".choice" ).slideUp( "slow", function() {});
                $("#row-" + choice + "-1").slideDown('slow');
                $(".re-choice").slideDown('slow');
                $(".start").slideDown('slow');
            }
            
            if(choice == 'fast'){
                get_probe_length();
            }
   
        });
        
        $(".re-choice-button").on('click', function(){
            
            $("#row-" + choice + "-1").slideUp('slow');
            $( ".choice" ).slideDown( "slow", function() {});
            $(".re-choice").slideUp('slow');
            $(".start").slideUp('slow');
            
        });
        
        $("#probe-calibration-save").on('click', override_nozzle_offset);

    });
    
    function get_probe_length(){
        if(probe_length <= 0){
        
            openWait('<i class="fa fa-circle-o-notch fa-spin"></i> '+_("Please wait") + '...');
            
            $.ajax({
                type: "POST",
                url: "<?php echo site_url("nozzle/getLength") ?>",
                dataType: "json"
            }).done(function( data ) {
               
                
                
                $( ".choice" ).slideUp( "slow", function() {});
                $("#row-fast-1").slideDown('slow');
                $(".re-choice").slideDown('slow');
                $(".start").slideDown('slow');
                closeWait();
                
                probe_length = data.probe_length;
                $("#probe-lenght").html(Math.abs(data.probe_length));
               
            });
        
        }else{
            $( ".choice" ).slideUp( "slow", function() {});
            $("#row-fast-1").slideDown('slow');
            $(".re-choice").slideDown('slow');
            $(".start").slideDown('slow');
        }
    }
    
    function prepare()
    {
        $(".re-choice").slideUp('slow');
        
        //var content = 'Heating extruder and bed<br>This operation will take a while';
        index = 1;
        
        openWait('<i class="fa fa-circle-o-notch fa-spin"></i> <?php echo _("Please wait"); ?>...');
        $.ajax({
              type: "POST",
              url: "<?php echo site_url("nozzle/prepare") ?>",
              dataType: 'json',
        }).done(function( response ) {
        	 closeWait();
            if(response.response == 'success'){
                z_max_offset = response.reply.z_max_offset;
                z_probe_offset = response.reply.z_probe_offset;
	            $("#row-normal-" + index).slideUp('slow', function(){
	                $("#row-normal-" + (index+1)).slideDown('slow');
	                
	            });
            }else{
            	showErrorAlert('<?php echo _("Error") ?>', response.message);
            }
           
        });
    }
    
    function calibrate()
    {
        $(".re-choice").slideUp('slow');
        
        index = 2;
        
        openWait('<i class="fa fa-circle-o-notch fa-spin"></i> ' + _("Please wait") + '...');
        $.ajax({
              type: "POST",
              url: "<?php echo site_url("nozzle/calibrateHeight") ?>",
              dataType: 'json',
        }).done(function( data ) {
                          
            $("#row-normal-" + index).slideUp('slow', function(){
                $("#row-normal-" + (index+1)).slideDown('slow');
                
                z_nozzle_offset = data.nozzle_z_offset;
                
                closeWait();
                
                var html = _("Nozzle offset calibration")+'\n';
                html += '====================================\n';
                html += _("New Probe Length: {0}").format(Math.abs(z_probe_offset)) + '\n';
                html += _("New Z Max offset: {0}").format(z_max_offset) + '\n';
                html += _("New nozzle offset: {0}").format(z_nozzle_offset);
                
                $("#calibrate-trace").html(html);
            });
        });
    }
    
    function do_again()
    {
        $("#calibrate-trace").html('');
        
        $(".calibration").slideUp('fast', function(){
            $(".choice").slideDown('fast');
        });
    }
        
    function move_z()
    {
        var sign = $(this).attr('data-action');
        var value = $("#z-value").val();
        var gcode = 'G91\nG0 Z' + sign + value;
        fabApp.jogMdi(gcode);
    }
    
    function override_nozzle_offset()
    {
        $(".re-choice").slideUp('slow');
        openWait('<i class="fa fa-circle-o-notch fa-spin"></i> ' + _("Please wait") + '...');
        
        $.ajax({
                type: "POST",
                url: "<?php echo site_url("nozzle/overrideOffset") ?>/"
                + $("#over").val(),
                dataType: "json",
            }).done(function( data ) {
               
               
               var html = _("Calibrating nozzle offset") + '\n';
               html += '====================================\n';
               html += _("Old nozzle offset: {0}").format( Math.abs(data.old_nozzle_offset) ) + '\n';
               html += _("Override value: {0}").format(data.over) + '\n';
               html += '====================================\n';
               html += _("New nozzle offset: {0}").format(data.nozzle_offset);
               
               $("#over-calibrate-trace").html(html);
               $("#row-fast-1").slideUp('fast', function(){
                     $("#row-fast-2").slideDown();
               });
                
               closeWait();
            });
    }

</script>
