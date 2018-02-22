/**
 * 
 */
const PROJECTS_INDEX_URL = '/fabui/#projects';
const ALL_PROJECTS_URL   = '/fabui/projects/get_all_projects';
const SAVE_PROJECT_URL   = '/fabui/projects/action/create-new-project';

/**
 * 
 */
var dropzones    = [];
var upload_queue = 0;
var total_files  = 0;
var main_form    = "";
/**
 * 
 */
var default_limit  = 10;
var default_offset = 0;
/**
 * 
 */
clearDropzones();
/**
*
**/
function get_projects(remote_sync, limit, offset)
{
	limit = limit || default_limit;
	offset = offset || default_offset;
	
	$(".sync").find('i').addClass("fa-spin");
	disableButton(".sync");
	disableButton("#load-more-button")
	if($(".project").length <= 0 || remote_sync == 1)
		$("#projects-container").html('<h4 class="ajax-loading-animation text-center"><i class="fa fa-cog fa-spin"></i> '+_("Loading projects")+'</h4>');
	
	$.get(ALL_PROJECTS_URL + '/' + remote_sync + '/' + limit + '/' + offset , function(data, status){
		$(".ajax-loading-animation").remove();
		build_projects(data);
		$(".sync").find('i').removeClass("fa-spin");
		enableButton(".sync");
	});
}

/**
* build projects
**/
function build_projects(data)
{	
	$.each(data.projects, function(i, item) {
		
		var image_url = item.image_url;
		if(image_url == '' || image_url == null){
			image_url = "http://via.placeholder.com/500x500?text=No%20preview";
		}
		var parts_icon = 'fa fas fa-cog';
		if(item.parts > 1){
			parts_icon = 'fa fas fa-cogs';
		}
		var cloud_icon = 'fas fa-cloud';
		if(item.deshape_id == '' || item.deshape_id == null){
			cloud_icon = '';
		}
		var html = '<div class="col-sm-2 animated fadeIn project">\
						<div class="panel panel-default">\
							<div class="panel-body status">\
								<div>\
									<a class="project-name"  href="#projects/edit/'+item.id+'">'+item.name+'</a>\
								</div>\
								<div class="image"><a href="#projects/edit/'+item.id+'"><image src="'+image_url+'"></a></div>\
								<ul class="links">\
									<li> </li>\
									<li title="' + item.parts + ' '+ _("part(s)") +'" class="pull-right"><i class="'+parts_icon+' text-info"></i></li>\
									<li  class="pull-right"><i class="'+cloud_icon+' text-info"></i></li>\
								</ul>\
							</div>\
						</div>\
					</div>';
		$("#projects-container").append(html);
		
	});
	
	if(data.projects.length < data.limit){
		disableButton("#load-more-button");
	}else{
		$("#load-more-button").attr('data-attribute-offset', data.next_offset);
		enableButton("#load-more-button");
	}
	
}

/**
 * init bootstrapValidator
 */
function initValidator(form)
{
	$(form).bootstrapValidator();
}

/**
 * manually validate bootstrapValidator form
 */
function validateForm(form)
{
	$(form).data('bootstrapValidator').validate();
}

/**
 * check if validate form is valid
 */
function isValidForm(form)
{
	validateForm(form);
	return $(form).data('bootstrapValidator').isValid()
}

/**
 * remove all dropzones
 */
function clearDropzones()
{
	$.each(dropzones, function(i, item){
		dropzones[i].destroy();
	});
	dropzones = [];
}

/**
 * craete dropzone instance
 */
function initDropzone(element, url, acceptedFiles, title)
{
	return new Dropzone("div"+element, {
		url: url,
		acceptedFiles: acceptedFiles,
		parallelUploads: 1,
		dictDefaultMessage: title,
		maxFiles: 1,
		addRemoveLinks : true, 
		autoProcessQueue: false,
		dictRemoveFile: _("Remove file"),
		dictMaxFilesExceeded: _("You can upload just {{maxFiles}} file at time"), 
		init: function(){
			
			/**
			 * 
			 */
			this.on("addedfile", function(file){
				upload_queue++;
			});
			/**
			*
			**/
			this.on("removedfile", function(file){
				upload_queue--;
			});
			
			/**
			 * 
			 */
			this.on("uploadprogress", function(file, progress) {
				
				var file_name = file.name.replace(/[^a-z0-9\-_:]|^[^a-z]+/gi, "");
				if($("#progress_" + file_name).length <= 0){
					
					var html = '<div class="row">\
							<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12">\
								<span class="text"><i class="far fa-file"></i> <strong>'+file.name+' <small>(' + humanFileSize(file.size) + ')</small> </strong> <span id="progress_'+file_name+'_percent" class="pull-right">'+parseInt(progress)+'</span></span>\
								<div class="progress">\
									<div class="progress-bar bg-color-blueDark" id="progress_'+file_name+'"></div>\
								</div>\
							</div>\
						</div>';
				}
				$(".show-stats").append(html);
				$("#progress_" + file_name).attr("style", "width:"+parseInt(progress)+"%");
				$("#progress_" + file_name+"_percent").html(parseInt(progress) + " %");
			
			});
			/**
			 * 
			 */
			this.on("complete", function(file){
				var file_name = file.name.replace(/[^a-z0-9\-_:]|^[^a-z]+/gi, "");
				$("#progress_" + file_name+"_percent").html("<i class='fa fa-check'></i>");
			});
			/**
			 * 
			 */
			this.on("success", function(file) {

				if(file.hasOwnProperty("xhr")){
					var response = jQuery.parseJSON(file.xhr.response);
					if(response.upload == true){
						
						var splitted_id = this.element.id.split('-');
						var counter = splitted_id[2];
						var type    = splitted_id[3];
						$('[name="part-'+counter+'-'+type+'_file"').val(response.file_id);
						upload_queue--;
						if(upload_queue == 0){
							$(".dropzone-modal-title").html('<i class="fa fa-check"></i> ' + _("Files uploaded"));
							setTimeout(function(){
								$('#progressModal').modal('hide');
								saveProject(main_form);
							}, 1500)
							
						}	
					}
				}
			});
		}
	});
}

/**
 * 
 */
function doUpload(dropzone)
{
	if(dropzone.getQueuedFiles().length > 0){
		//start upload
		dropzone.processQueue();
	}
}

/**
 *  start upload all files
 */
function startUpload(form)
{	
	
	if(isValidForm(form)){
		if(upload_queue > 0) {
			
			total_files = upload_queue;
			
			if($("#progressModal").length <= 0){
				createProgressModal();
			}
			$('#progressModal').modal({
				keyboard: false,
				backdrop: 'static'
			});
			
			$.each(dropzones, function(i, item){
				doUpload(item.source);
				doUpload(item.machine);
			});
		}else{
			fabApp.showErrorAlert(_("Add at least 1 file"));
		}
	}
}

/**
*
**/
function saveProject(form)
{
	if(isValidForm(form)){
		var data = getDataFromForm(form);
		openWait('<i class="fas fa-spinner fa-pulse"></i> ' + _("Saving project"), _("Please wait.."), false);
		$.ajax({
			type: "POST",
			url: SAVE_PROJECT_URL,
			data: data,
			dataType: "json",
		}).done(function( response ) {
			openWait('<i class="fas fa-check"></i> ' + _("Project saved"), '<i class="fas fa-sync fa-spin"></i> ' + _("Reloading page"), false);
			setTimeout(function() {
				document.location.href = PROJECTS_INDEX_URL;
			}, 2000);
		});
	}
}

/**
 * 
 */
function createProgressModal()
{
	var modal_html = '<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">\
					<div class="modal-dialog">\
						<div class="modal-content">\
							<div class="modal-header">\
								<h4 class="modal-title" id="myModalLabel"> <span class="dropzone-modal-title"> <i class="fa fa-upload"></i> ' + _("Uploading files") + '</span></h4>\
							</div>\
							<div class="modal-body">\
								<div class="row">\
									<div class="col-sm-12 show-stats">\
									</div>\
								</div>\
							</div>	\
						</div>\
					</div>\
				</div>';
	$("#content").append(modal_html);
}
