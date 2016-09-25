<script type="text/javascript">

    var choice = '';
    
    var interval_trace;
    var interval_response;
    var trace_file;
    var response_file;
    
    var finished = false;
    var editor;

    $(function () {
         
        $(".choice-button").on('click', function (){
                
            choice = $(this).attr('data-action');
            
            var name_procedure = '';
            
            $( ".choice" ).slideUp( "slow", function() {});
            $("." + choice + "-choice").slideDown('slow');
            $(".re-choice").slideDown('slow');
            $(".start").slideDown('slow');
            
            if(choice == 'unload' || choice == 'pre_unload'){
            	name_procedure = 'Unload Filament';
            }else{
            	name_procedure = 'Load Filament';
            }
            
            $(".procedure-name").html('>  <strong>' + name_procedure +' </strong>');
                
        });
        
        
        $(".re-choice-button").on('click', function(){
            
            $("." + choice + "-choice").slideUp('slow');
            $( ".choice" ).slideDown( "slow", function() {});
            $(".re-choice").slideUp('slow');
            $(".start").slideUp('slow');
            
            $(".procedure-name").html("");
            
        });
        
        
        $(".start-button").on('click', do_macro);
        
        
    });
    
    function do_macro()
    {
        
        if(choice == 'pre_unload'){
            pre_unload();
            return;
        }
        
        $(".trace").slideDown('slow');
        $(".new-spool").remove();
        $("." + choice + "-choice").slideUp('slow');
        
        openWait("<i class='fa fa-circle-o-notch fa-spin'></i> Please wait");
        
        $.ajax({
              type: "POST",
              url: "<?php echo site_url("spool") ?>/" + choice,
              dataType: 'json'
        }).done(function( response ) { 
            
            //response_file = response.uri_response;
            //trace_file    = response.uri_trace;
            
            //interval_response = setInterval(do_monitor, 1000);
            //interval_trace    = setInterval(do_trace, 1000);
            
            closeWait();
            
            $(".start").slideUp('slow');
            $(".start-button").addClass('disabled');
            $(".re-choice").slideUp('slow');
            $(".title").find("h2").html(choice.charAt(0).toUpperCase() + choice.slice(1) + 'ing filament');
            $(".title").slideDown('slow', function () {});
            $(".console").slideDown('slow', function () {});
            
            end();
            
        });
    }
    
    function pre_unload()
    {
        openWait("<i class='fa fa-circle-o-notch fa-spin'></i> Please wait");
        
        $.ajax({
            type:"POST",
            url: "<?php echo site_url("spool/preUnload") ?>",
            dataType: "json"
            
        }).done(function(response){
            choice = 'unload';
            
            closeWait();

            $(".pre_unload-choice").slideUp( "slow", function() {});
            $( ".choice" ).slideUp( "slow", function() {});
            $("." + choice + "-choice").slideDown('slow');
            $(".re-choice").slideDown('slow');
            
        });
    }
    
    function end()
    {        
        $(".title").find("h2").html('Spool ' +  choice.charAt(0).toUpperCase() + choice.slice(1) + ' completed <i class="fa fa-check text-success"></i>');
        
        var act = choice == 'unload' ? 'load' : 'unload';
            
        $(".title").find('h2').append('<h5 class="new-spool">Do you want to ' + act + ' spool? <a id="again-link" href="javascript:void(0);"> YES </a> </h5>');
            
        /** VUOI CARICARCARE FILO ? */
        
        $("#again-link").on('click', function() {
            again(act);
        });
        
        $(".trace").slideUp('slow');
        
        $(".console").html('');
    }
    
    
    function again(action)
    {
        choice = action;
        finished = false;
        
        $(".title").slideUp('fast');
        $(".start-button").removeClass('disabled');
        $(".re-choice").slideDown('fast');
        
        $("." + action + "-choice").slideDown('fast', function() {
            
            $(".start").slideDown('fast');
            
        });
    }
    

</script>
