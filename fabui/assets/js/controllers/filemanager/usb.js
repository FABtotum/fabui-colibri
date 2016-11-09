	function load_tree(obj)
	{
		
		var folder = obj.attr("data-folder");
		var loaded = obj.attr("data-loaded") == "true" ? true : false;
		
		if(!loaded){
			obj.next('ul').html('');
			$.ajax({
			   type: "POST",
			   url: "filemanager/getFileTree",
			   data: {folder: folder},
			   dataType: 'json'
			}).done(function(response) {
				var tree = response.tree;
				if(tree.length > 0)
				{
					
					$.each(tree, function(i, item) {
					
						var element = '';                  
						if(item.charAt((item.length - 1)) == '/')
						{
							element = folder_item(item, folder);
						}
						else
						{
							element = file_item(item, folder);
						}
						obj.next('ul').append(element);
					   
					});
					
					obj.attr("data-loaded","true");
					 
					init_sub_tree();
					
					
				}
				else
				{
					obj.find('i').removeClass();
					obj.attr("data-loaded","false");
				}

			});
		}
	}

	function file_item(item, parent)
	{
		var item_label = item.replace(parent, '');
		
		var html = '';
		
		html += '<li style="list-item;"><span>';
		html += '<label class="checkbox inline-block usb-file">';
		
		html += '<input type="checkbox" name="checkbox-inline" value="'+ parent + item +'" />';
		html += '<i></i> '+ item_label;
		
		html += '</label>';
		html += '</span></li>';
		
		return html;
		
	}


	function folder_item(item, parent)
	{
		
		var html = '';
		
		html += '<li class="parent_li" role="treeitem">';
		
		html += '<span class="subfolder" data-loaded="false" data-folder="' + parent + item +'">';
		
		item = item.replace(parent, '');
		item = item.slice(0,-1);
		
		html += '<i class="fa fa-lg fa-plus-circle"></i> ' + item;
		html += '</span>';
		
		html += '<ul></ul>';
		
		html += '</li>';
		
		return html;
		
	}
	
	function init_tree()
	{
		
		$('.tree > ul').attr('role', 'tree').find('ul').attr('role', 'group');
		
		$('.tree').find('li:has(ul)').addClass('parent_li').attr('role', 'treeitem').find(' > span').attr('title', 'Collapse this branch').on('click', function(e) {
			
			var children = $(this).parent('li.parent_li').find(' > ul > li');        
			
			load_tree($(this));
			
			if (children.is(':visible'))
			{
				children.hide('fast');
				$(this).attr("data-loaded","false");
				$(this).attr('title', 'Expand this branch').find(' > i').removeClass().addClass('fa fa-lg fa-plus-circle');
			}
			else
			{
				children.show('fast');
				$(this).attr('title', 'Collapse this branch').find(' > i').removeClass().addClass('fa fa-lg fa-minus-circle');
			}
			e.stopPropagation();         
		}); 
	}

	
	function init_sub_tree()
	{
		
		$(".subfolder").on('click', function (e) {
			var obj_temp = $(this);
			
			load_tree($(this));
			
			var children = $(this).parent('li.parent_li').find(' > ul > li');
			
			if (children.is(':visible'))
			{
				children.hide('fast');
				obj_temp.attr("data-loaded","false");
				
				$(this).attr('title', 'Expand this branch').find(' > i').removeClass().addClass('fa fa-lg fa-plus-circle');
			}
			else
			{
				children.show('fast');
				$(this).attr('title', 'Collapse this branch').find(' > i').removeClass().addClass('fa fa-lg fa-minus-circle');
				obj_temp.attr("data-loaded","true");
			}
			e.preventDefault();
			e.stopPropagation();  
		});
	}
	
	function check_usb()
	{
		console.log('check_usb');
		
		$.ajax({
			   type: "POST",
			   url: "filemanager/checkUSB",
			   dataType: 'json'
		}).done(function(response) {
			
			console.log(response);
			
			if(response.content)
			{
				$("#usb-tab").html(response.content);
			}
			else
			{
				fallback = '<div class="text-center">\
					<h1><span style="font-size: 50px;" class="icon-fab-usb"></span></h1>\
					<h1>Please insert USB disk</h1>\
					<a id="check-usb" class="btn btn-default" href="javascript:void(0);">Reload</a>\
					</div>';
				$("#usb-tab").html(fallback);
			}
			
			if(response.inserted == true){
				init_tree();
			}
			else
			{
				$("#check-usb").on('click', function() {
					check_usb();
				});
			}
			
			
		});
	}

	function add_usb_files()
	{
		if($('.tree').length > 0)
		{
			var usb_files = new Array();                        
			$( ".tree" ).find("input").each(function( index ) {
				
				
				var input = $(this);
				
				if(input.is(':checked'))
				{
					usb_files.push(input.val());
				}
				
			});
			$('#usb_files').val(usb_files.toString());
		}
	}
