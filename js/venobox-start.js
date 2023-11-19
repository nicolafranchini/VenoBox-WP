var VenoboxWP = (function(){
    "use strict";
	// Convert values from 0/1 to false/true
	var numeratio = false, infinigall = false, autoplay = false, arrows = true, nav_keyboard = true, nav_touch = true, fit_view = false;
	if (VENOBOX.numeratio ) {
		numeratio = true;
	}
	if (VENOBOX.infinigall ) {
		infinigall = true;
	}
	if (VENOBOX.autoplay ) {
		autoplay = true;
	}
	if (VENOBOX.arrows ) {
		arrows = false;
	}
	if (VENOBOX.nav_keyboard ) {
		nav_keyboard = false;
	}
	if (VENOBOX.nav_touch ) {
		nav_touch = false;
	}
	if (VENOBOX.fit_view ) {
		fit_view = true;
	}

	// Detects the end of an ajax request being made for Search & Filter Pro
	if(VENOBOX.searchfp) {
		document.addEventListener('sf:ajaxfinish', enableVenoBox);
		document.addEventListener('.searchandfilter', enableVenoBox);
	}

	// Detects the end of an ajax request being made for Facet WP
	if(VENOBOX.facetwp) {
		document.addEventListener('facetwp-loaded', enableVenoBox);
	}

	function checkURL(url) {
	    return(url.match(/\.(jpeg|jpg|gif|png|webp)$/) != null);
	}

	// Images
	function imagesVeno() {

		var linklist = [];
		var boxlinks = document.querySelectorAll('a[href]');

		for (var i=0,l=boxlinks.length; i<l; i++) {
			if (boxlinks[i].getAttribute('href')) {
				if ( checkURL(boxlinks[i].getAttribute('href')) ) {
					linklist.push(boxlinks[i]);
				}
			}
		}

		Array.prototype.forEach.call(linklist, function(el, i){

			if (el.href.indexOf('?') < 0) {
				el.classList.add('venobox');

				var imgSelector = el.querySelector("img");

				if ( ! el.hasAttribute('data-gall') ) {
					el.dataset.gall = 'gallery';
				}

				// Set Title from one of three options
				switch (VENOBOX.title_select) {
					case '1':
				    	el.setAttribute("title", imgSelector.getAttribute("alt"));
				    	break;
					case '2':
						el.setAttribute("title", imgSelector.getAttribute("title"));
				  		break;
					case '3':
						var gallItem = el.closest('figure');
						if (gallItem) {
							var caption = gallItem.querySelector("figcaption");
							if (caption) {
								el.setAttribute("title", caption.innerText);
							}
						}
				    break;
						default:
				    return;
				}
			}
		});
	}

	// Galleries
	function galleryVeno() {

		// Set galleries to have unique data-gall sets
		var galleries = document.querySelectorAll('div[id^="gallery"], .gallery-row');
		
		Array.prototype.forEach.call(galleries, function(gall, i){
			var links = gall.querySelectorAll('a');
			Array.prototype.forEach.call(links, function(link, i){
				link.dataset.gall = 'venoset-'+i;
			});
		});

		// Jetpacks caption as title
		if (VENOBOX.title_select == 3) {
			var tiledgalleries = document.querySelectorAll('.tiled-gallery-item a');
			Array.prototype.forEach.call(tiledgalleries, function(tiledgall, i){
				var gallItem = tiledgall.closest('.tiled-gallery-item');
				if (gallItem) {
					var caption = gallItem.querySelector(".tiled-gallery-caption").innerText;
					if (caption) {
						tiledgall.setAttribute("title", caption);
					}
				}
			});
		}
	}

	function checkURLvid(url) {
		var regYt = /(https?:\/\/)?((www\.)?(youtube(-nocookie)?|youtube.googleapis)\.com.*(v\/|v=|vi=|vi\/|e\/|embed\/|user\/.*\/u\/\d+\/)|youtu\.be\/)([_0-9a-z-]+)/i;
		var regVim = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
	    if (
	    	url.search(/.+\.mp4|og[gv]|webm/) !== -1 || 
	    	url.match(regYt) || url.match(regVim)
	    ) {
	    	return true;
	    }
	    return false;
	}

	// Videos
	function videoVeno() {

		var vidlist = [];
		var vidlinks = document.querySelectorAll('a[href]');

		for (var i=0,l=vidlinks.length; i<l; i++) {
		
			if (vidlinks[i].getAttribute('href')) {
				if ( checkURLvid(vidlinks[i].getAttribute('href')) ) {
					vidlist.push(vidlinks[i]);
				}
			}
		}
		Array.prototype.forEach.call(vidlist, function(el, i){
			el.classList.add('venobox');
			el.dataset.vbtype = 'video';
			// Dont replace the data-gall if already set
			if ( ! el.hasAttribute('data-gall')) {
				el.dataset.gall = 'gallery';
			}
		});
	}

	// Default settings
	function defaultVeno() {
		new VenoBox({
			maxWidth: VENOBOX.max_width,
			navigation: arrows, // default: false
			navKeyboard: nav_keyboard,
			navTouch: nav_touch,
			navSpeed: VENOBOX.nav_speed,
			titleStyle: VENOBOX.title_style,
			shareStyle: VENOBOX.share_style,
			toolsBackground: VENOBOX.nav_elements_bg, // 'transparent'
			toolsColor: VENOBOX.nav_elements,
			bgcolor: VENOBOX.border_color,
			border: VENOBOX.border_width,
			numeration: numeratio, // default: false
			infinigall: infinigall, // default: false
			autoplay: autoplay, // default: false
			overlayColor: VENOBOX.overlay,
			spinner: VENOBOX.preloader,
			titlePosition: VENOBOX.title_position,
			spinColor: VENOBOX.nav_elements,
			share: VENOBOX.share,
			ratio: VENOBOX.ratio,
			fitView: fit_view
		});
	}

	function enableVenoBox(){
		if (VENOBOX.all_images) {
			imagesVeno();
			galleryVeno();
		}
		if (VENOBOX.all_videos) {
			videoVeno();
		}
		defaultVeno();
	}

	function init(){
		enableVenoBox();
		// Init main Venobox
	}

	return {
        init
    };
})();
	
if ( ! VENOBOX.disabled ) {
	VenoboxWP.init();
}
