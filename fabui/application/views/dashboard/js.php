<script type="text/javascript">

	$(document).ready(function() {

		loadFeed('/fabui/social/load/blog',      buildBlogFeeds);
		loadFeed('/fabui/social/load/twitter',   buildTwitterFeeds);
		loadFeed('/fabui/social/load/instagram', buildInstagramFeeds);
	});

	function loadFeed(url, callback)
	{
		$.get(url, function(data, status){
			callback(data);
			$(".check-again").on('click', checkAgain);
		});
	}
	
	/**
	*
	**/
	function buildBlogFeeds(data)
	{
		//showLatestPost(data[0]);
		var html = '';
		if(data && data.length > 0){
			$.each(data, function(i, item) {
				html += buildBlogPost(item);
			});
		}else{
			html = noFeedAvailable("<i class='fa fa-rss'></i> <?php echo _("Latest blog posts"); ?>", 'blog');
		}
		$("#blog-container").html(html);
		$("#blog-carousel-container").html(html);
		//initCarousel("#blog-carousel-container", false, false);
	}
	/**
	*
	**/
	function buildTwitterFeeds(data)
	{
		var html = '';
		if(data && data.length > 0){
			$.each(data, function(i, item) {
				
				html += buildTwitterPost(item);
			});
		}else{
			html = noFeedAvailable("<i class='fab fa-twitter'></i> <?php echo _("Latest tweets"); ?>", 'twitter');
		}
		$("#twitter-container").html(html);

		$("#twitter-carousel-container").html(html);
		//initCarousel("#twitter-carousel-container", false, false);
		setTimeout(function(){
			initCarousel(".twitter-carousel", true, true);
		},500);
		
	}
	/**
	*
	**/
	function buildInstagramFeeds(data)
	{
		var html = '';
		var html_carousel = '';
		if(data && (data.feeds_a)  && (data.feeds_b)){
			html += '<div class="row"><div class="col-sm-6 col-xs-6 col-b">';
			$.each(data.feeds_a, function(i, item) {
				html += instagramPost(item);
				html_carousel += instagramPost(item);
			});
			html += '</div><div class="col-sm-6 col-xs-6 col-a">';
	
			$.each(data.feeds_b, function(i, item) {

				var post = instagramPost(item);
				html += post;
				html_carousel += post;
				
			});
		}else{
			html = noFeedAvailable("<i class='fab fa-instagram'></i> <?php echo _("Latest instragm posts"); ?>", 'instagram');
		}
		html += '</div>';
		$("#instagram-container").html(html);
		$("#instagram-carousel-container").html(html_carousel);
		$(".instagram-comments").on('click', function(){
			var container = $(this).parent().parent();
			
			 if(container.find('.comments').length){
				 var comments = container.find('.comments');
				 if(comments.is(':visible')){
					 comments.slideUp();
				 }else{
					 comments.slideDown();
				 }
			 }
		});
		
		//initCarousel("#instagram-carousel-container", false, false);
		
		setTimeout(function(){
			initCarousel(".instagram-carousel", true, false);
		},500);
	}
	/**
	*
	**/
	function instagramPost(item)
	{
		var post_url = 'http://www.instagram.com/p/'+ item['code'];
		var date = item['taken_at'];
		var location = '';
		var image = '';
		var src_image = getInstagramImageSrc(item);
		var src_video = getInstagramVideoSrc(item);
		var carousel  = getInstagramCarousel(item);
		var video = '';
		var likes = '<li class="txt-color-red"><i class="fa fa-heart"></i> ('+item['like_count']+')</li>';
		var views_count = '';
		var ranked = '';
		var comments_list = '';
		var class_comments = item.preview_comments.length > 0 ? 'cursor-pointer' : '';
		var comments_title = item.preview_comments.length > 0 ? "<?php echo _("Click to view comments"); ?>" : "";
		var comments_color = item.preview_comments.length > 0 ? "txt-color-blue" : "";
		var comments = '<li title="'+comments_title+'" class="'+comments_color+' instagram-comments '+class_comments+'"><i class="fa fa-comments"></i> ('+item['comment_count']+') </li>';

		if(src_video != ''){
			video = '<div class="image padding-10"><video class="img-responsive" controls><source src="'+src_video+'" type="video/mp4"><img src="'+src_image+'" /></video></div>';
			views_count = '<li class="txt-color-green"><i class="fa fa-play"></i> ('+item['view_count']+')</li>';
		}else if(carousel != ''){
			
		}else{
			image = '<div class="image padding-10"><img title="'+item.caption.original_text+'" src="'+src_image+'" /></div>';
		}

		if(item.location) location += '<br><i class="fa fa-map-marker"></i> '+item['location']['name'];
		if(item.is_ranked && item.is_ranked == true) ranked += '<li title="<?php echo _("Popular"); ?>" class="txt-color-yellow pull-right"><i class="fa fa-star"></i> </li>';

		if(item.preview_comments.length > 0){
			
			comments_list += '<ul class="comments" style="display:none;">';
			$.each(item.preview_comments, function(i, comment) {
				comments_list += '<li>'+
									'<img src="'+comment.user.profile_pic_url+'">'+
									'<a target="_blank" href="https://www.instagram.com/'+comment.user.username+'"><span class="name">' + comment.user.full_name + '</span></a>' + 
									comment.text				
			});
			comments_list += '</ul>';
		}
		
		var html = '<div class="panel panel-default">' +
							'<div class="panel-body status">' +
						'<div class="who clearfix">' +
							'<img class="hidden-xs" src="'+item['user']['profile_pic_url']+'" />' +
							'<span class="name"><b><a target="_blank" href="https://www.instagram.com/'+item['user']['username']+'">'+item['user']['username']+'</a></b>' +
							'<span class="pull-right">' +
								'<a href="'+post_url+'" target="_blank" title="<?php echo _("View on instagram");?>"><i class="fab fa-instagram"></i></a>' +
							'</span></span>' +
							'<span class="from"> ' + date + location + '</span>' +
						'</div>' + image + video + carousel +
						'<div class="text padding-top-0 hidden-xs"><p style="word-wrap: break-word">'+item['caption']['text']+'</p></div>' +
						'<ul class="links">' + likes + comments + views_count + ranked +
						'</ul>' +
						comments_list + 
					'</div>' +
				'</div>';

		return html;
	}
	/**
	*
	**/
	function getInstagramImageSrc(item)
	{
		if(item.image_versions2.candidates){
			var url = "";
			var maxWidth = 0;

			$.each(item.image_versions2.candidates, function(i, img) {
				if(img.width > maxWidth){
					url = img.url;
					maxWidth = img.maxWidth;
				}
			});
			return url;
		}
		return '';
	}
	/**
	*
	**/
	function getInstagramVideoSrc(item)
	{
		if(item.video_versions){
			return item.video_versions[0]['url'];
		}
		return "";
	}
	/**
	*
	**/
	function getInstagramCarousel(item)
	{
		if(item.carousel_media){
			var html = '<div id="carousel_'+item.id+'" class="owl-carousel instagram-carousel owl-theme">';
			$.each(item.carousel_media, function(i, car_item) {
				var img_src = getInstagramImageSrc(car_item);
				if(car_item.media_type == 1){ // PHOTO	
					html += '<div class="image padding-10"><img src="'+img_src+'" /></div>';
				}else if(car_item.media_type == 2){ // VIDEO
					var video_src = getInstagramVideoSrc(car_item);
					html += '<div class="image padding-10"><video class="img-responsive" controls><source src="'+video_src+'" type="video/mp4"><img src="'+img_src+'" /></video></div>';
				}
			});
			html += '';
			html += '</div>';			
			return html;
		}
		return '';
	}
	/**
	*
	**/
	function showLatestPost(post)
	{
		var html = '';

		html += '<div class="col-md-4">'+
					'<img class="img-responsive" src="'+post['img_src']+'" alt="'+post['title'][0]+'" title="'+post['title'][0]+'" />' +
					'<ul class="list-inline padding-10">'+
						'<li>'+
							'<i class="fa fa-calendar"></i>'+
							'<a href="javascript:void(0);"> '+post['date']+' </a>'+
						'</li>'+
					'</ul>'+
				'</div>';

		html += '<div class="col-md-8 padding-left-0">'+
					'<h3 class="margin-top-0"><a href="javascript:void(0);">'+post['title'][0]+'</a></h3>'+
					'<p>'+ post['text'] + '</p>'+
					'<a class="btn btn-primary" href="javascript:void(0);"> Read more </a>'+
					'<a class="btn btn-warning" href="javascript:void(0);"> Edit </a>'+
					'<a class="btn btn-success" href="javascript:void(0);"> Publish </a>'+
				'</div>';
		$("#last-post").html(html);
	}
	/**
	*
	**/
	function noFeedAvailable(message, type)
	{
		var html = '<div class="panel panel-default ">'+
						'<div class="panel-body status">'+
							'<div class="who clearfix"><h4>'+message+'</h4></div>'+
							'<div class="text text-center">'+
								'<h2><i class="fa fa-frown-o"></i></h2>' +
								'<p><?php echo _("Feeds are not available") ?></p>'+
								'<p><?php echo _("Check your connection and try again") ?></p>'+
							'</div>'+
							'<ul class="links text-center">' +
								'<li class="text-center">' +
									'<button data-type='+type+' class="btn btn-default check-again"><?php echo _("Check again");?></button>' +
								'</li>' +
							'</ul>' +
						'</div>'+
					'</div>';
		return html;
	}
	/**
	*
	**/
	function checkAgain()
	{
		var button = $(this);
		var type   = button.attr("data-type");

		button.html("<i class='fa fa-spin fa-spinner'></i> Downloading...");
			
		switch(type){
			case 'blog':
				loadFeed('/fabui/social/load/blog/1', buildBlogFeeds);
				break;
			case 'twitter':
				loadFeed('/fabui/social/load/twitter/1', buildTwitterFeeds);
				break;
			case 'instagram':
				loadFeed('/fabui/social/load/instagram/1', buildInstagramFeeds);
				break;
		}		
	}
	/**
	*
	**/
	function initCarousel(element, is_single_post, is_twitter)
	{

		var custom_prev_class = is_twitter == true ? 'instagram-owl-prev' : 'instagram-owl-prev';
		var custom_next_class = is_twitter == true ? 'instagram-owl-next' : 'instagram-owl-next';

		var prev_class = "owl-prev " + custom_prev_class;
		var next_class = "owl-next " + custom_next_class;

		var show_nav =  true;

		var  owl = $(element).owlCarousel({
        	loop: true,
         	margin: 10,
         	autoHeight:true,
         	navText : ["<i class='fa fw-lg fa-chevron-left font-lg'></i>","<i class='fa fw-lg fa-chevron-right font-lg'></i>"],
         	navClass: [prev_class, next_class],
         	dots: false,
         	onInitialize:  fixNavBars,
         	onInitialized: fixNavBars,
         	onChange :  fixNavBars,
         	onResized :  fixNavBars,
         	onRefreshed : fixNavBars,
         	onDragged: fixNavBars,
            responsiveClass: true,
            	responsive: {
                	0: {
                    	items: 1,
                    	nav: show_nav
                  	},
                  	600: {
                    	items: is_single_post ? 1 : 3,
                    	nav: show_nav
                  	},
                  	1000: {
                    	items: is_single_post ? 1 : 5,
                    	nav: show_nav,
                    	loop: false,
                    	margin: 10
                  	}
                }
		});
	}
	/**
	*
	**/
	function fixNavBars(event)
	{

		var element = this.$element;
		var id_element = element.attr("id");
		var mainContentHeight = $("#content").height();
		//center arrows
		var carouselHeight = $("#" + id_element).height();
		
		//var prevHeight = $(".owl-prev").height();
		//var nextHeight = $(".owl-next").height();
		var prevHeight = $("#"+id_element+" .owl-prev").height();
		var nextHeight = $("#"+id_element+" .owl-next").height();
		
		$("#"+id_element+" .owl-prev").css("top", (carouselHeight-prevHeight)/2);
		$("#"+id_element+" .owl-next").css("top", (carouselHeight-prevHeight)/2);

		if(carouselHeight > mainContentHeight) fixNavBars();

	}
		
		
	/**
	*
	**/
	function buildBlogPost(item)
	{
		return '<div class="panel panel-default">' +
					'<div class="panel-body status">' +
					'<div class="who clearfix">' +
						'<img class="hidden-xs" src="'+item['img_src']+'" alt="'+item['title'][0]+'" title="'+item['title'][0]+'" />' +
						'<span class="name font-sm">' +
							'<a target="_blank" href="'+item['link'][0]+'">'+item['title'][0]+'</a>' +
							'<br>' +
							'<span class="text-muted">'+item['date']+'</span>' +
						'</span>' +
					'</div>' +
					'<div class="image padding-top-0 padding-10">' +
						'<a target="_blank" href="'+item['link'][0]+'"><img title="'+item['title'][0]+'" alt="'+item['title'][0]+'" src="'+item['img_src']+'" /></a>' +
					'</div>' +
					'<div class="text hidden-xs">' +
						'<p>'+item['text']+'</p>' +
					'</div>' +
					'<ul class="links">' +
						'<li class="">' +
							'<a class="btn btn-default btn-circle btn-xs txt-color-blue" title="<?php echo _("Share on facebook"); ?>" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u='+item['link'][0]+'"><i class="fab fa-facebook-f"></i></a>' +
						'</li>' +
						'<li class="">' +
							'<a class="pull-right" title="<?php echo _("Read more"); ?>" target="_blank" href="'+item['link'][0]+'"> <?php echo _("Read more"); ?> <i class="fa fa-arrow-right"></i></a>' +
						'</li>' +
					'</ul>' +
				'</div>' +
			'</div>';
	}
	/**
	*
	**/
	function buildTwitterPost(item)
	{
		var post_url  = 'http://www.twitter.com/statuses/' + item['id_str'];
		var retweet = '';
		var favourite = '';
		var place = '';
		var date = item['created_at'];
		var media_html = '';
		var retweeted = '';
		var has_video = false;

		if(item.is_retweeted) retweeted += '<div class="retweeted"><span><i class="fa fa-retweet txt-color-green"></i> <?php echo _("FABtotum retweeted");?></span></div>';
		if(item.place) place += '<br><i class="fa fa-map-marker"></i> '+item['place']['full_name'];
		if(item.retweet_count>0) retweet += '<li class="txt-color-green"><i class="fa fa-retweet"></i> ('+ item['retweet_count']+')</li>';
		if(item.favorite_count>0) favourite += '<li class="txt-color-red"><i class="fa fa-heart"></i> ('+item['favorite_count']+')</li>';


		if(typeof item.quoted_status !== "undefined"){

			if(typeof item.quoted_status.extended_entities !== "undefined")
				item.extended_entities = item.quoted_status.extended_entities;
				
		}
		
		if(item.extended_entities){
			media_html += '<div id="carousel_'+item.id+'" class="owl-carousel twitter-carousel owl-theme" style="">';
			$.each(item.extended_entities.media, function(j, media){	
				if(media.type == 'photo'){
					media_html += '<div class="image padding-top-0 padding-10" style=""><img title="'+item.original_text+'" src="'+media.media_url+'" /></div>';
				}else if(media.type == 'video'){
					var video_src = media.video_info.variants[1].url;
					var src_image = media.media_url_https;
					media_html += '<div class="image padding-10"><video class="img-responsive" controls><source src="'+video_src+'" type="video/mp4"><img src="'+src_image+'" /></video></div>';
				}
			});
			media_html += '</div>';
		}else if(item.entities.media){
		}
		
		return '<div class="panel panel-default">'+
					'<div class="panel-body status">'+ retweeted + 
					'<div class="who clearfix">'+
						'<img alt="'+item['user']['description']+'"  title="'+item['user']['description']+'" class="hidden-xs" src="'+item['user']['profile_image_url']+'" />'+
						'<span class="name"><b><a target="_blank" href="https://twitter.com/'+item['user']['screen_name']+'">'+item['user']['screen_name']+'</a></b>'+
						'<span class="pull-right"><a href="'+post_url+'" target="_blank" title="View on Twitter"><i class="fab fa-twitter"></i></a></span></span>'+
						'<span class="from">'+ date + place + '</span>'+
						'</span>'+
					'</div>'+
					'<div class="text">'+
						'<p>'+item['text']+'</p>'+
					'</div>'+media_html+
					'<ul class="links">' + retweet + favourite +
					'</ul>'+
				'</div>'+
			'</div>';
	}
	/**
	*
	**/
	function myFabotumPrintersList(data)
	{
		if(data.status == true){
			if(data.printers && data.printers.length > 0){
				var html = '<div class="col-sm-12 animated fadeIn">\
								<div class="well well-light well-sm">\
									<div class="">\
										<span style="margin-right:30px;">'+_("My other printers")+'</span>';
				var ribbon = '<span class="pull-left">'+_("My other printers")+'</span>';
				$.each(data.printers, function(i, item) {
					html += '<div class="btn-group"><a href="http://'+item.iplan+'/fabui/#dashboard" target="_blank" class="btn btn-default no-ajax" style="margin-right:5px;"><i class="fa fa-lg fa-fw fabui-core" style="vertical-align: -30%;"></i> '+item.name+'</a></div>';
					ribbon += '<a href="http://'+item.iplan+'/fabui/#dashboard" target="_blank" class="btn btn-ribbon no-ajax"><i class="fa fa-lg fa-fw fabui-core"></i> '+item.name+'</a>';
				});
				ribbon += '';
				html += '</div></div></div>';
				$("#ribbon-right-buttons").html(ribbon);
				//$("#my-fabtotum-printers-list").html(html);
			}
		}
	}
</script>