/**
 * WP jQuery Lightbox
 * Version 1.4.8.2 - 2021-01-23
 * 
 * @author Ulf Benjaminsson (http://www.ulfbenjaminsson.com) 
 *
 * This is a modified version of Warren Krevenkis Lightbox-port (see notice below) for use in the WP jQuery Lightbox-
 * plugin (http://wordpress.org/extend/plugins/wp-jquery-lightbox/)
 *  Modifications include:
 *	. migrated to modern jQuery (Thanks: [Joseph Rézeau aka papijo](https://www.rezeau.org/)!) 
 *	. improved support of Gutenberg editor's image captions (Thanks: [Joseph Rézeau aka papijo](https://www.rezeau.org/)!) 
 *	. added slideshow
 *	. added swipe-support
 *	. added "support" for WordPress admin bar.
 *	. improved the resizing code to respect aspect ratio
 *	. improved scaling routines to maximize images while taking captions into account
 *  . added support for browser resizing and orientation changes
 *	. grabs image caption from WordPress [gallery] or media library output
 *	. using WordPress API to localize script (with safe fallbacks)
 *	. grabs image title if the link lacks one
 *	. using rel attribute instead of class
 *	. auto-lightboxing all links after page load
 *	. replaced explicit IMG-urls with divs, styled through the CSS.
 *	. honors empty captions / titles
 *	. use only title if captions is identical
 *  . added support for disabling all animations
 *	. using no-conflict mode (for good measure)
 **/
/**
 * jQuery Lightbox
 * Version 0.5 - 11/29/2007
 * @author Warren Krewenki
 *
 * This package is distributed under the BSD license.
 * For full license information, see LICENSE.TXT
 *
 * Based on Lightbox 2 by Lokesh Dhakar (http://www.huddletogether.com/projects/lightbox2/)
 * Originally written to make use of the Prototype framework, and Script.acalo.us, now altered to use jQuery.
 **/
(function($){
	//http://stackoverflow.com/a/16187766/347764	
	function versionCompare(a, b){ 	
		var i, l, d;
		a = a.split('.');
		b = b.split('.');
		l = Math.min(a.length, b.length);
		for (i=0; i<l; i++) {
			d = parseInt(a[i], 10) - parseInt(b[i], 10);
			if (d !== 0) {
				return d;
			}
		}
		return a.length - b.length;
	}
    $.fn.lightbox = function(options) {			
        var opts = $.extend({}, $.fn.lightbox.defaults, options);
		if($("#overlay").is(':visible')){//to resize the overlay whenever doLightbox is invoked
			$(window).trigger('resize'); //we need this to deal with InfiniteScroll and similar.
		}
		function onClick() {
            initialize();
            start(this);
            return false;
        }	
		if(versionCompare($.fn.jquery, '1.7') > 0){
			return $(this).on("click", onClick);
        }else{
			return $(this).live("click", onClick); //deprecated since 1.7
		}				
		function initialize() {
            $(window).on('orientationchange', resizeListener);
            $(window).on('resize', resizeListener);
            $('#overlay').remove();
            $('#lightbox').remove();         
            opts.inprogress = false;
			opts.auto = -1;
			var txt = opts.strings;
			var arrowLeft = '<svg id="prevArrow" width="60px" height="60px" viewBox="-5.5 0 26 26" xmlns="http://www.w3.org/2000/svg"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-423.000000, -1196.000000)" fill="currentColor"><path d="M428.115,1209 L437.371,1200.6 C438.202,1199.77 438.202,1198.43 437.371,1197.6 C436.541,1196.76 435.194,1196.76 434.363,1197.6 L423.596,1207.36 C423.146,1207.81 422.948,1208.41 422.985,1209 C422.948,1209.59 423.146,1210.19 423.596,1210.64 L434.363,1220.4 C435.194,1221.24 436.541,1221.24 437.371,1220.4 C438.202,1219.57 438.202,1218.23 437.371,1217.4 L428.115,1209" id="chevron-left"></path></g></g></svg>';
			var arrowRight = '<svg id="nextArrow" width="60px" height="60px" viewBox="-5.5 0 26 26" xmlns="http://www.w3.org/2000/svg"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(-474.000000, -1196.000000)" fill="currentColor"><path d="M488.404,1207.36 L477.637,1197.6 C476.806,1196.76 475.459,1196.76 474.629,1197.6 C473.798,1198.43 473.798,1199.77 474.629,1200.6 L483.885,1209 L474.629,1217.4 C473.798,1218.23 473.798,1219.57 474.629,1220.4 C475.459,1221.24 476.806,1221.24 477.637,1220.4 L488.404,1210.64 C488.854,1210.19 489.052,1209.59 489.015,1209 C489.052,1208.41 488.854,1207.81 488.404,1207.36"></path></g></g></svg>';

			var navigation = opts.newNavStyle
				? '<div id="newHoverNav">' + arrowLeft + arrowRight + '</div>'
				: '<div id="hoverNav"><a href="javascript://" title="' + txt.prevLinkTitle + '" id="prevLink"></a><a href="javascript://" id="nextLink" title="' + txt.nextLinkTitle + '"></a></div>';

			var swipeNavigation = opts.newNavStyle
				? arrowLeft + arrowRight
				: '<a href="javascript://" title="' + txt.prevLinkTitle + '" id="prevLink" class="touch-device"></a><a href="javascript://" id="nextLink" class="touch-device" title="' + txt.nextLinkTitle + '"></a>';

            var outerImage = '<div id="outerImageContainer"><div id="imageContainer"><img id="lightboxImage">' + navigation + '<div id="jqlb_loading"><a href="javascript://" id="loadingLink"><div id="jqlb_spinner"></div></a></div></div></div>';
			if ( jqlbIsTouchDevice() ) {
				outerImage = '<div id="outerImageContainer"><div id="imageContainer"><img id="lightboxImage">' + swipeNavigation + '<div id="jqlb_loading"><a href="javascript://" id="loadingLink"><div id="jqlb_spinner"></div></a></div></div></div>';
			}
            var imageData = '<div id="imageDataContainer" class="clearfix"><div id="imageData"><div id="imageDetails"><span id="titleAndCaption"></span><div id="controls"><span id="numberDisplay"></span> <a id="playPause" href="#"></a> <span id="downloadLink"></span></div></div><div id="bottomNav">';
            imageData += '<a href="javascript://" id="bottomNavClose" title="' + txt.closeTitle + '"><div id="jqlb_closelabel"></div></a></div></div></div>';
            var string;
            if ( opts.navbarOnTop ) {
                string = '<div id="overlay"></div><div id="lightbox">' + imageData + outerImage + '</div>';
                $("body").append(string);
                $("#imageDataContainer").addClass('ontop');
			} else {
                string = '<div id="overlay"></div><div id="lightbox">' + outerImage + imageData + '</div>';
                $("body").append(string);
            }
            $("#overlay").on("click", function () { end(); }).hide();
            $("#lightbox").on("click", function () { end(); }).hide();
            $("#loadingLink").on("click", function () { end(); return false; });
            $("#bottomNavClose").on("click", function () { end(); return false; });
            $('#outerImageContainer').width(opts.widthCurrent).height(opts.heightCurrent);
            $('#imageDataContainer').width(opts.widthCurrent);
            if (!opts.imageClickClose) {
                $("#lightboxImage").on("click", function () { return false; });
                $("#hoverNav").on("click", function () { return false; });
            }

			// Apply color options
			$("#outerImageContainer").css({ "background-color": opts.borderColor });
			$("#overlay").css({
				"background-color": opts.overlayColor ?? '#000',
				"opacity": opts.overlayOpacity ?? '.6',
				"font-style": "italic"
			});

			// Hide/show infobar
			if ( ! opts.showInfoBar ) {
				$('#imageDataContainer').addClass('hide');
			}

			// Fixed nav postion infobar
			if ( opts.fixedNav ) {
				$('#prevArrow').addClass('fixed');
				$('#nextArrow').addClass('fixed');
			}
        };	
        //allow image to reposition & scale if orientation change or resize occurs.
        function resizeListener(e) {
            if (opts.resizeTimeout) {
                clearTimeout(opts.resizeTimeout);
                opts.resizeTimeout = false;
            }
            opts.resizeTimeout = setTimeout(function () { doScale(); }, 50); //a delay to avoid duplicate event calls.		
        }
        function getPageSize(){           						
			return { 
				pageWidth:$(document).width(), //returns width of HTML document
				pageHeight:$(document).height(), 
				viewportWidth:$(window).width(),  //width of browser viewport, minus scrollbars
				viewportHeight:$(window).height()-opts.adminBarHeight,				
			};			
        };       
        function getPageScroll() {
            var yScroll = $(document).scrollTop();            
			if(opts.adminBarHeight && parseInt($('#wpadminbar').css('top'), 10) === 0){				
				yScroll += opts.adminBarHeight;
			}
			return {x:$(document).scrollLeft(), y:yScroll};            
        };
		function start(imageLink) {
			var pageSize = getPageSize();            
			var newTop = 0;
			$("#overlay").hide().css({width: pageSize.pageWidth + 'px', height: pageSize.pageHeight + 'px', opacity: opts.overlayOpacity*100 + '%'}).fadeIn(400);
			var imageNum = 0;  			
			var images = [];
			opts.downloads = {}; //to keep track of any custom download links		
			$("a").each(function(){
				if(!this.href || (this.rel != imageLink.rel)) {
					return;
				}
				var jqThis = $(this);
				var title = opts.showTitle ? getTitle(jqThis) : '';
				var caption = {html:'',text:''};
				if (opts.showCaption) {
					// Case if Gutenberg blocks : the caption now resides inside the <figcaption> tag.
					var figureCaption = jqThis.parent().children('figcaption')[0];
							if (figureCaption !== undefined) { // Case Gutenberg blocks
						caption.html = figureCaption.innerHTML;
					}	else { // Case Classic blocks
						var caption = getCaption(jqThis);
					}
				}
							
				if (opts.showTitle && title.toLowerCase() == caption.text.toLowerCase()) {
					title = caption.html; //to keep linked captions
					caption.html = ''; //but not duplicate the text								
				}	
        			
				var s = (title != '') ? '<span id="titleText">' + title + '</span>' : '';					
				s += (title != '' && caption.html)? '<br />' : '';
				s += (caption.html != '') ? '<span id="captionText">' + caption.html +'</span>' : '';	
				if(opts.showDownload || jqThis.attr("data-download")){							
					opts.downloads[images.length] = jqThis.attr("data-download"); //use length as an index. convenient since it will always be unique							
				}						
				images.push(new Array(this.href, s, images.length));
			});			            
			if (images.length > 1) {
				for (i = 0; i < images.length; i++) {
					for (j = images.length - 1; j > i; j--) {
						if (images[i][0] == images[j][0]) {
							images.splice(j, 1);
						}
					}
				}
				while (images[imageNum][0] != imageLink.href) { imageNum++; }
			}
      		opts.imageArray = images;
      		var scroll = getPageScroll();			
      		setLightBoxPos(scroll.y, scroll.x).show();// calculate top and left offset for the lightbox
      		changeImage(imageNum);

			// Set panzoom on initial image
			if ( jqlbIsZoomEnabled() ) {
				var lightboxImage = document.getElementById( 'lightboxImage' );
				panzoom = Panzoom( lightboxImage, { maxScale: 5, minScale: 1, panOnlyWhenZoomed: true } );
			}

			setNav();
    	};
		function getTitle(jqLink){
			var title = jqLink.attr("title") || '';
			var jqImg = jqLink.children('img:first-child');
			if ( opts.useAltForTitle && ( jqLink.attr('alt') || jqImg.attr('alt') ) ) {
				title = jqLink.attr('alt') ? jqLink.attr('alt') : jqImg.attr('alt');
			}
			if(!title){
				var jqImg = jqLink.children('img:first-child');
				if (jqImg.attr('title')) {
					title = jqImg.attr('title'); //grab the title from the image if the link lacks one
				} else if(jqImg.attr('alt')){
					title = jqImg.attr('alt'); //if neither link nor image have a title attribute
				}
			}
			return DOMPurify.sanitize(title);
		}
		function getCaption(jqLink){
			var caption = {html:'',text:''};
			if (jqLink.parent().next('.gallery-caption').html()) {
				var jq = jqLink.parent().next('.gallery-caption');
				caption.html = jq.html(); //holds html, links etc
				caption.text = jq.text(); //plain-text
			} else if (jqLink.next('.wp-caption-text').html()) {
				caption.html = jqLink.next('.wp-caption-text').html();
				caption.text = jqLink.next('.wp-caption-text').text();
			}
			caption.text = caption.text.trim().replace('&#8217;', '&#039;').replace('’', '\'').replace('…', '...');//http://nickjohnson.com/b/wordpress-apostrophe-vs-right-single-quote
			return caption;			
		}
		function setLightBoxPos(newTop, newLeft) {        
            if (opts.resizeSpeed > 0) {
                return $('#lightbox').animate({ top: newTop+ 'px', left: newLeft+ 'px' }, opts.resizeSpeed, 'linear');                
            }
            return $('#lightbox').css({ top: newTop + 'px', left: newLeft + 'px' });
        };
        function changeImage(imageNum) {
            if (opts.inprogress != false) {
				return;
			}
			opts.inprogress = true;
			opts.activeImage = imageNum;
			// hide elements during transition
			$('#jqlb_loading').show();
			$('#lightboxImage').hide();
			$('#hoverNav').hide();
			$('#prevLink').hide();
			$('#nextLink').hide();
			$('#prevArrow').hide();
			$('#nextArrow').hide();
			doChangeImage();            
        };
        function doChangeImage() {
            opts.imgPreloader = new Image();
            opts.imgPreloader.onload = function () {
                $('#lightboxImage').attr('src', opts.imageArray[opts.activeImage][0]);
                updateDetails(); 
				doScale();  // once image is preloaded, resize image container
                preloadNeighborImages();				
            };
            opts.imgPreloader.src = opts.imageArray[opts.activeImage][0];
        };
        function doScale() {
            if (!opts.imgPreloader) {
                return;
            }
            var newWidth = opts.imgPreloader.width;
            var newHeight = opts.imgPreloader.height;
            var pageSize = getPageSize();  
			var noScrollWidth = (pageSize.viewportWidth < pageSize.pageWidth) ? pageSize.pageWidth : pageSize.viewportWidth; //if viewport is smaller than page, use page width.
			$("#overlay").css({ width: noScrollWidth + 'px', height: pageSize.pageHeight + 'px' });  
            var maxHeight = (pageSize.viewportHeight) - ($("#imageDataContainer").outerHeight(true) + (2 * opts.borderSize));
            var maxWidth = (pageSize.viewportWidth) - (2*opts.borderSize);			
			if(opts.fitToScreen){
				var displayHeight = maxHeight-opts.marginSize;	
				var displayWidth = maxWidth-opts.marginSize;									
				var ratio = 1;
                if (newHeight > displayHeight) {
                    ratio = displayHeight / newHeight; //ex. 600/1024 = 0.58					
                }
                newWidth = newWidth * ratio;
                newHeight = newHeight * ratio;
                ratio = 1;
                if (newWidth > displayWidth) {
                    ratio = displayWidth / newWidth; //ex. 800/1280 == 0.62					
                }
                newWidth = Math.round(newWidth * ratio);
                newHeight = Math.round(newHeight * ratio);
            }        
			var scroll = getPageScroll();
			var centerY = scroll.y + (maxHeight * 0.5);
			var newTop = centerY - (newHeight * 0.5);
			var newLeft = scroll.x;
			$('#lightboxImage').width(newWidth).height(newHeight);
			resizeImageContainer(newWidth, newHeight, newTop, newLeft);           
        }
        function resizeImageContainer(imgWidth, imgHeight, lightboxTop, lightboxLeft) {
            opts.widthCurrent = $("#outerImageContainer").outerWidth();
            opts.heightCurrent = $("#outerImageContainer").outerHeight();
            var widthNew = Math.max(300, imgWidth + (opts.borderSize * 2)); //300 = iphone. http://wordpress.org/support/topic/image-not-resized-correctly-with-wptouch?replies=6#post-4205735
            var heightNew = (imgHeight + (opts.borderSize * 2));                      
			setLightBoxPos(lightboxTop, lightboxLeft);                    
			$('#imageDataContainer').animate({width:widthNew}, opts.resizeSpeed, 'linear');
			$('#outerImageContainer').animate({width:widthNew,height:heightNew}, opts.resizeSpeed, 'linear', function () {				
					showImage();				
			});
			if (opts.imageArray.length > 1) {
                $('#hoverNav').show();      
				$('#prevLink').show();
				$('#nextLink').show();
				$('#prevArrow').show();
				$('#nextArrow').show();
            }
            $('#prevLink,#nextLink').height(imgHeight);            
        };
        function showImage(){                
            $('#jqlb_loading').hide();
            showDetails();
			if(opts.resizeSpeed > 0){
                $('#lightboxImage').fadeIn("fast", function(){
					onImageVisible();
				});
            }else{
				$('#lightboxImage').show();
				onImageVisible();
            }
            opts.inprogress = false;
        };
		function showDetails(){			
			$('#titleAndCaption').css("opacity", '100%');			
			if(opts.showDownload){
				$('#downloadLink').css('opacity', '100%');
			}					
            if(opts.showNumbers){
				$('#numberDisplay').css('opacity', '100%');
			}
			if(opts.slidehowSpeed){
				$('#playPause').css('opacity', '100%');
			}
			if(opts.resizeSpeed > 0){
				$("#imageDetails").animate({opacity: '100%'}, 'fast');
			}else{
				$("#imageDetails").css('opacity', '100%');
			}
		}
		function onImageVisible(){
			if(opts.auto != -1){				
				clearTimeout(opts.auto); //in case we came here after mouse/keyboard interaction
				opts.auto = setTimeout(function(){changeImage((opts.activeImage == (opts.imageArray.length - 1)) ? 0 : opts.activeImage + 1);}, opts.slidehowSpeed);
			}			
			enableKeyboardNav();

			// Re-attach panzoom to current image
			if ( jqlbIsZoomEnabled() ) {
				panzoom?.destroy();
				var lightboxImage = document.getElementById( 'lightboxImage' );
				panzoom = Panzoom( lightboxImage, { maxScale: 5, minScale: 1, panOnlyWhenZoomed: true } );
			}
		}		
		function preloadNeighborImages() {
            if (opts.imageArray.length > 1) {
                preloadNextImage = new Image();
                preloadNextImage.src = opts.imageArray[(opts.activeImage == (opts.imageArray.length - 1)) ? 0 : opts.activeImage + 1][0]
                preloadPrevImage = new Image();
                preloadPrevImage.src = opts.imageArray[(opts.activeImage == 0) ? (opts.imageArray.length - 1) : opts.activeImage - 1][0]
            } else {
                if ((opts.imageArray.length - 1) > opts.activeImage) {
                    preloadNextImage = new Image();
                    preloadNextImage.src = opts.imageArray[opts.activeImage + 1][0];
                }
                if (opts.activeImage > 0) {
                    preloadPrevImage = new Image();
                    preloadPrevImage.src = opts.imageArray[opts.activeImage - 1][0];
                }
            }
        };
        function updateDetails(){ //update caption, title and control, and makes them invisible while we animate into position.                        
			var images = opts.imageArray;
			var txt = opts.strings;
			var i = opts.activeImage;
			var downloadIndex = images[i][2];            		
            var pos = (opts.showNumbers && images.length > 1) ? txt.image + (i + 1) + txt.of + images.length : '';            
			$("#imageDetails").css("opacity", '100%');					
			if(images[i][1] != ''){
                $('#titleAndCaption').css('opacity', '100%').html(images[i][1]);
            }else{	
				$('#titleAndCaption').empty();
			}
			if(opts.showNumbers){
                $('#numberDisplay').css('opacity', '100%').html(pos);
            }else{
				$('#numberDisplay').empty();
			}
			if(opts.slidehowSpeed && images.length > 1){	
				var pp = (opts.auto === -1) ? txt.play : txt.pause;
				$('#playPause').css('opacity', '100%').attr('href', '#').text(pp);				
			}else{
				$('#playPause').empty();
			}				
			if(opts.showDownload || opts.downloads[downloadIndex]){
				var url = opts.downloads[downloadIndex] ? opts.downloads[downloadIndex] : images[i][0]; 				
				$('#downloadLink').css('opacity', '100%').html($('<a>').attr('href', url).attr('target', '_blank').attr('download', '').text(txt.download));
			}else{
				$('#downloadLink').empty();
			}
			var position = $('#downloadLink').position();			
        };	
        function setNav() {
            if (opts.imageArray.length > 1) {                
				$('#prevLink').on("click", function () {
					changeImage((opts.activeImage == 0) ? (opts.imageArray.length - 1) : opts.activeImage - 1);
					return false;
				});
				$('#nextLink').on("click", function () {
					changeImage((opts.activeImage == (opts.imageArray.length - 1)) ? 0 : opts.activeImage + 1);
					return false;
				});
				$('#prevArrow').on("click", function () {
					changeImage((opts.activeImage == 0) ? (opts.imageArray.length - 1) : opts.activeImage - 1);
					return false;
				});
				$('#nextArrow').on("click", function () {
					changeImage((opts.activeImage == (opts.imageArray.length - 1)) ? 0 : opts.activeImage + 1);
					return false;
				});

				if($.fn.touchwipe){
					$('#imageContainer').touchwipe({
						wipeLeft: function() {
							if ( ! isImageZoomed() ) {
								changeImage((opts.activeImage == (opts.imageArray.length - 1)) ? 0 : opts.activeImage + 1);
							}
						},
						wipeRight: function() {
							if ( ! isImageZoomed() ) {
								changeImage((opts.activeImage == 0) ? (opts.imageArray.length - 1) : opts.activeImage - 1);
							}
						},
						min_move_x: 20,				
						preventDefaultEvents: true
					});
				}
				if(opts.slidehowSpeed){				
					$('#playPause').off('click').on("click", function() {										
						if(opts.auto != -1){
							$(this).text(opts.strings.play);							
							clearTimeout(opts.auto);							
							opts.auto = -1;
						}else{											
							$(this).text(opts.strings.pause);						
							opts.auto = setTimeout(function(){changeImage((opts.activeImage == (opts.imageArray.length - 1)) ? 0 : opts.activeImage + 1);}, opts.slidehowSpeed);
						}
						return false;
					});							
				}
                enableKeyboardNav();
            }
        };
        function end() {
            disableKeyboardNav();
			clearTimeout(opts.auto);
			opts.auto = -1;
            $('#lightbox').hide();
            $('#overlay').fadeOut();         
        };
        function keyboardAction(e) {
            var o = e.data.opts;
            var keycode = e.keyCode;
            var escapeKey = 27;
            var key = String.fromCharCode(keycode).toLowerCase();
            if ((key == 'x') || (key == 'o') || (key == 'c') || (keycode == escapeKey)) { // close lightbox
                end();
            } else if ((key == 'p') || (keycode == 37)) { // display previous image
				disableKeyboardNav();
                changeImage((o.activeImage == 0) ? (o.imageArray.length - 1) : o.activeImage - 1);
            } else if ((key == 'n') || (keycode == 39)) { // display next image
                disableKeyboardNav();
                changeImage((o.activeImage == (o.imageArray.length - 1)) ? 0 : o.activeImage + 1);
            }          
            return false;
        };
						
        function enableKeyboardNav() {			
			$(document).off('keydown').on('keydown', {opts: opts}, keyboardAction);
        };
        function disableKeyboardNav() {
            $(document).off('keydown');
        };
		function isImageZoomed() {
			if ( jqlbIsZoomEnabled() && panzoom?.getScale() > 1 ) {
				return true;
			}
			return false;
		}
    };    
    $.fn.lightbox.defaults = {
		showCaption:false,
		showNumbers:true,
		adminBarHeight:0, //28
        overlayOpacity: '80%',
        borderSize: 10,
        imageArray: new Array,
        activeImage: null,
        inprogress: false, //this is an internal state variable. don't touch.
        widthCurrent: 300,
        heightCurrent: 300,
        showTitle: true,       
        imageClickClose: true
    };	
	$(document).ready(doLightBox);	
})(jQuery);

var panzoom = {};

function jqlbIsTouchDevice() {
	return (('ontouchstart' in window) ||
		(navigator.maxTouchPoints > 0) ||
		(navigator.msMaxTouchPoints > 0));
}

function jqlbIsZoomEnabled() {
	return JQLBSettings.allowPinchZoom  === '1'
}

//you can call doLightBox() manually at any time to activate the lightboxing. Useful for AJAX-loaded content.
function doLightBox(){
	var haveConf = (typeof JQLBSettings == 'object');	
	var ss, rs, ms = 0;
	if(haveConf && JQLBSettings.slideshowSpeed) {
		 ss = parseInt(JQLBSettings.slideshowSpeed);
	}
	if(haveConf && JQLBSettings.resizeSpeed) {
		rs = parseInt(JQLBSettings.resizeSpeed);
	}
	if(haveConf && JQLBSettings.marginSize){
		ms = parseInt(JQLBSettings.marginSize);
	}
	if(haveConf && jqlbIsTouchDevice() && JQLBSettings.mobileMarginSize){
		ms = parseInt(JQLBSettings.mobileMarginSize)
	}
	var default_strings = {		
		prevLinkTitle: 'previous image',
		nextLinkTitle: 'next image',		
		closeTitle: 'close image gallery',
		image: 'Image ',
		of: ' of ',
		download: 'Download',
		pause: '(pause slideshow)',
		play: '(play slideshow)'
	};
	jQuery('a[rel^="lightbox"]').lightbox({
		adminBarHeight: jQuery('#wpadminbar').height() || 0,
		showNumbers: (haveConf && JQLBSettings.showNumbers == '1') ? true : false,
		showCaption: (haveConf && JQLBSettings.showCaption == '1') ? true : false,
		showTitle: (haveConf && JQLBSettings.showTitle == '1') ? true : false,
		useAltForTitle: (haveConf && JQLBSettings.useAltForTitle == '1') ? true : false,
		marginSize: (haveConf && ms) ? ms : 0,
		fitToScreen: (haveConf && JQLBSettings.fitToScreen == '1') ? true : false,
		resizeSpeed: (haveConf && rs >= 0) ? rs : 400,
		slidehowSpeed: (haveConf && ss >= 0) ? ss : 4000,
		showDownload: (haveConf && JQLBSettings.showDownload == '1') ? true : false,
		navbarOnTop: (haveConf && JQLBSettings.navbarOnTop == '1') ? true : false,		
		strings: (haveConf && typeof JQLBSettings.prevLinkTitle == 'string') ? JQLBSettings : default_strings,
		borderSize: (haveConf && JQLBSettings?.borderSize ? JQLBSettings?.borderSize : 10),
		borderColor: (haveConf && JQLBSettings?.borderColor ),
		overlayColor: (haveConf && JQLBSettings?.overlayColor ),
		overlayOpacity: (haveConf && JQLBSettings?.overlayOpacity ),
		newNavStyle: (haveConf && JQLBSettings?.newNavStyle == '1' ? true : false ),
		fixedNav: (haveConf && JQLBSettings?.fixedNav == '1' ? true : false ),
		navArrowColor: (haveConf && JQLBSettings?.navArrowColor ),
		navBackgroundColor: (haveConf && JQLBSettings?.navBackgroundColor ),
		showInfoBar: (haveConf && JQLBSettings?.showInfoBar == '1' ? true : false )
	});
}

