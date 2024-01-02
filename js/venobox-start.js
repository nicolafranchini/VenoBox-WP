var VenoboxWP = (function(){
    "use strict";

	// Convert values from 0/1 to false/true
	var numeratio = false, infinigall = false, autoplay = false, arrows = true, nav_keyboard = true, nav_touch = true, fit_view = false;

	if (VENOBOX.numeratio) {
		numeratio = true;
	}
	if (VENOBOX.infinigall) {
		infinigall = true;
	}
	if (VENOBOX.autoplay) {
		autoplay = true;
	}
	if (VENOBOX.arrows) {
		arrows = false;
	}
	if (VENOBOX.nav_keyboard) {
		nav_keyboard = false;
	}
	if (VENOBOX.nav_touch) {
		nav_touch = false;
	}
	if (VENOBOX.fit_view) {
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

	function isImage(url) {
		return(/\.(jpeg|jpg|gif|png|webp)$/i.test(url));
	}

	function isVideo(url) {
		var regYt = /(https?:\/\/)?((www\.)?(youtube(-nocookie)?|youtube.googleapis)\.com.*(v\/|v=|vi=|vi\/|e\/|embed\/|user\/.*\/u\/\d+\/)|youtu\.be\/)([_0-9a-z-]+)/i;
		var regVim = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
	    if (
	    	/\.(mp4|ogg|ogv|mov|webm)$/i.test(url) || 
	    	url.match(regYt) || url.match(regVim)
	    ) {
	    	return true;
	    }
	    return false;
	}

	// Images
	function initImages(boxlinks) {

		var linklist = [];
		var boxlinks = document.querySelectorAll('a[href]');

		for (var i=0,l=boxlinks.length; i<l; i++) {
			if (boxlinks[i].getAttribute('href')) {
				if (isImage(boxlinks[i].getAttribute('href'))) {
					linklist.push(boxlinks[i]);
				}
			}
		}

		linklist.forEach(function(el, i){

			if (el.href.indexOf('?') < 0) {
				el.classList.add('venobox');

				var imgSelector = el.querySelector("img");

				if (!el.hasAttribute('data-gall') ) {
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
						var gallItem = el.closest("figure");
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
	function initGalleries() {

		// Set galleries to have unique data-gall sets
		const galleries = document.querySelectorAll('div[id^="gallery"], .gallery-row, .wp-block-gallery');
		galleries.forEach(function(gallery, i){
			const allLinks = gallery.querySelectorAll('a');
			allLinks.forEach(function(link, index){
				link.dataset.gall = 'venoset-'+i;
			});
		})

		// Jetpacks caption as title
		if (VENOBOX.title_select == 3) {
			const tiledgalleries = document.querySelectorAll('.tiled-gallery-item a');
			tiledgalleries.forEach(function(tiledgall, i){
				const gallItem = tiledgall.closest('.tiled-gallery-item');
				if (gallItem) {
					const caption = gallItem.querySelector(".tiled-gallery-caption").innerText;
					if (caption) {
						tiledgall.setAttribute("title", caption);
					}
				}
			});
		}
	}

	// Videos
	function initVideos(vidlinks) {

		var vidlist = [];
		// var vidlinks = document.querySelectorAll('a[href]');

		vidlinks.forEach(function(vidlink){
			if (vidlink.getAttribute('href')) {
				if ( isVideo(vidlink.getAttribute('href')) ) {
					vidlist.push(vidlink);
				}
			}
		});

		vidlist.forEach(function(el){
			el.classList.add('venobox');
			el.dataset.vbtype = 'video';
			// Dont replace the data-gall if already set
			if ( ! el.hasAttribute('data-gall')) {
				el.dataset.gall = 'gallery';
			}
		});
	}

	function setDataAttributes() {
		const fit_refs = document.querySelectorAll('.venobox-fitview');
		fit_refs.forEach(function(fitwrap){
			const fitlinks = fitwrap.querySelectorAll('a[href]');
			fitlinks.forEach(function(fitlink){
				fitlink.dataset.fitview = 1
			});
		});
	}

	// Default settings
	function defaultSettings() {
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

	function enableVenoBox(allLinks){

		setDataAttributes();

		if (VENOBOX.all_images) {
			initImages(allLinks);
			initGalleries();
		}
		if (VENOBOX.all_videos) {
			initVideos(allLinks);
		}
		defaultSettings();
	}

	function init(){
		const allLinks = document.querySelectorAll('a[href]');
		enableVenoBox(allLinks);
	}

	return {
        init
    };
})();
	
if ( ! VENOBOX.disabled ) {
	VenoboxWP.init();
}
