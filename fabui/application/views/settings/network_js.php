<script type="text/javascript">


    $(function () {
        
        $("#save").on('click', save);
        $(".ip").inputmask();
    });

    function save()
    {
        console.log('save');
        var tab = $("li.tab.active").attr('data-attribute');
        var net_type = $("li.tab.active").attr('data-net-type');
        
        var data = {};
        $(".tab-content :input").each(function (index, value) {
            if($(this).is('input:text') || $(this).is('select') || $(this).is(':input[type="number"]') || ($(this).is('input:radio') && $(this).is(':checked')) ){
                data[$(this).attr('id')] = $(this).val();
            }
        });
        
        if(tab == "dnssd")
        {
            net_type = "dnssd";
        }
        
        data['active'] = tab;
        data['active-type'] = net_type;
        
        switch(net_type)
        {
            case "eth":
                save_ethernet(tab, data);
                break;
            case "wlan":
                save_wifi(tab, data);
                break;
            default:
                save_dnssd(data)
                break;
        }
    }
    
    function save_ethernet(iface, data)
    {
        if( !validate_ip(iface) )
        {
            return;
        }
        
        console.log('save eth', iface);
        console.log(data);
        post_data(data);
    }
    
    function save_wifi(iface, data)
    {
        if( !validate_ip(iface) )
        {
            return;
        }
        
        console.log('save wifi', iface); 
        console.log(data);
        post_data(data);
    }
    
    function save_dnssd(data)
    {
        console.log('save dnssd');
        console.log(data);
        post_data(data);
    }
    
    function post_data(data)
    {
        var button = $("#save");
        button.addClass('disabled');
        button.html('<i class="fa fa-save"></i> Saving..');
        
        console.log('trying to post done');
        $.ajax({
            type: 'post',
            url: '<?php echo 'settings/saveNetworkSettings'; ?>',
            data : data,
            dataType: 'json'
        }).done(function(response) {
            button.html('<i class="fa fa-save"></i> Save');
            button.removeClass('disabled');
            console.log('post done!!!');
            
            $.smallBox({
                title : "Settings",
                content : 'Network settings saved',
                color : "#5384AF",
                timeout: 3000,
                icon : "fa fa-check bounce animated"
            });
            
        });
    }
    
    function validate_ip(iface)
    {
        var result = true;
        $(".tab-content :input[id^="+iface+"-].ip").each(function (index, value) {
            var id = $(this).attr('id');
            console.log(id);
            
            var ip = $(this).val();
            ip = ip.split('.');
            
            //$(this).removeClass('danger');
            
            for(i=0; i<4; i++)
            {
                if( !$.isNumeric(ip[i]) )
                {
                    result = false;

                        $.smallBox({
                            title : "Warning",
                            content : "Invalid IP address",
                            color : "#C46A69",
                            timeout: 10000,
                            icon : "fa fa-warning"
                        });
                        
                    return false;
                }
            }
        });
        
        return result;
    }
    
</script>
