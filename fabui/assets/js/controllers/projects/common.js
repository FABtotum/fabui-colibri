/**
*
**/
function get_projects()
{
	$("#projects-container").html('<h4 class="ajax-loading-animation text-center"><i class="fa fa-cog fa-spin"></i> '+_("Loading projects")+'</h4>');
	$.get('/fabui/projects/get_all_projects', function(data, status){
		
		$(".ajax-loading-animation").remove();
		build_projects(data);
		
	});
}

/**
* build projects
**/
function build_projects(projects)
{	
	$.each(projects, function(i, item) {
		var html = '<div class="col-sm-2 animated fadeIn">\
						<div class="panel panel-default">\
							<div class="panel-body status">\
								<div class="image"><a href="#projects/edit/'+item.id+'"><image src="'+item.image_url+'"></a></div>\
								<div class="links">\
									<a href="#projects/edit/'+item.id+'">'+item.name+'</a>\
								</div>\
							</div>\
						</div>\
					</div>';
		$("#projects-container").append(html);
	});
}