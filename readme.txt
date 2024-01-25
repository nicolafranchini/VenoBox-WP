=== VenoBox 2 ===

Author: Nicola Franchini
contributors: nicolafranchini
Version: 1.0.2
Stable tag: 1.0.2
Tested up to: 6.4
Requires at least: 4.0
Requires PHP: 5.3
Plugin Name: VenoBox
Plugin URI: https://wordpress.org/plugins/venobox/
Description: Responsive lightbox modal windows for images, videos, inline contents, iFrames, Ajax requests. Touch swipe galleries.
Tags: lightbox, modal, pop-up, gallery, zoom
Author URI: https://veno.es/
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Donate link: https://www.paypal.com/donate?hosted_button_id=2SUA56VZHYYMQ

Responsive lightbox modal windows for images, videos, inline contents, iFrames, Ajax requests. Touch swipe galleries.

== Description ==

VenoBox is a lightweight plugin that takes image and video showcasing to the next level. Built on the foundation of venobox.js, this plugin stands out by calculating the optimal display size for images, preserving their height, and ensuring a seamless viewing experience. Now, with even more options, VenoBox can also resizes the images to fit within the viewport height

### Features

* **Responsive Image Handling:** 
VenoBox sets itself apart by dynamically calculating the max width of displayed images and maintaining their original height, ensuring your visuals look stunning on any device. No more microscopic resized images on small screens – VenoBox guarantees an immersive user experience.

* **Optional Image Resizing:** 
Take control of your visuals with VenoBox's optional image resizing feature, allowing you to fit images perfectly within the viewport height. Tailor your content for an aesthetically pleasing and responsive display.

* **Multimedia Versatility:** 
From images and iframes to inline content, VenoBox supports it all. Seamlessly integrate Vimeo, YouTube, and HTML5 video formats into captivating modal lightbox popups for a truly engaging user experience.

* **Automatic Activation:** 
Let VenoBox do the heavy lifting by automatically enabling itself for all linked images and videos on your WordPress site. Enjoy hassle-free implementation and an instantly improved visual presentation.

* **Touch Swipe Gallery Navigation:** 
Enhance user interaction with touch swipe gallery navigation. Your audience can effortlessly explore galleries with a simple swipe, creating an intuitive and enjoyable browsing experience.

* **Keyboard Navigation:** 
Navigate through your content with ease using VenoBox's keyboard navigation feature. Provide users with multiple ways to interact, ensuring accessibility and user-friendliness.

* **Custom Preloaders:** 
Impress your visitors with custom preloaders that match your website's style. Choose from a variety of options to create a seamless transition as your images and videos load.

* **Custom Videos Aspect Ratio:** 
Fine-tune your video displays with custom aspect ratios, ensuring your multimedia content looks just the way you envision it.

* **Customizable Colors and Styles:** 
Tailor the appearance of VenoBox to match your brand with customizable colors and styles. Maintain a consistent visual identity across your website, creating a cohesive and polished look.

* **WooCommerce Compatibility:** 
VenoBox seamlessly integrates with WooCommerce product galleries, offering a streamlined solution for displaying your products with style and flair.


### 3rd party services

**YouTube** 
If you enable VenoBox for videos and link YouTube videos, the plugin embeds the videos inside of your website with the following url:
https://www.youtube-nocookie.com/embed/

Terms of servce:
https://developers.google.com/youtube/terms/api-services-terms-of-service

**Vimeo** 
If you enable VenoBox for videos and link Vimeo videos, the plugin embeds the videos inside of your website with the following url:
https://player.vimeo.com/video/

Terms of servce:
https://vimeo.com/terms


== Installation ==

1. Upload the plugin folder `venobox` to the `/wp-content/plugins/` directory
2. Activate the plugin through the Plugins menu in WordPress
3. Adjust the plugin settings in  WP Admin > Dashboard > Settings > Venobox


== Usage ==

Enable VenoBox for Images and/or Videos inside the plugin's settings section.
Adjust style and gallery options


== Screenshots ==

1. VenoBox interface

2. Plugin Options

3. UI Style

4. Integrations

5. Disable VenoBox on specific post or page

== Frequently Asked Questions == 

= How to open remote content inside iFrames? =
Add the class `venobox-iframe` to your links

= How to get results from ajax calls? =
Add the class `venobox-ajax` to your links

= How to open inline content inside iFrames? =
Add the class `venobox-inline` to your links

= Can the images be resized to fit within the viewport height? =
Yes, with the global option `Fit view`, or to individual links or group of links adding the class `venobox-fitview` to one of their containers

== Changelog ==

= 1.0.3 =
* New: initialize iframes, inline content and ajax calls with `venobox-`classes
* Update: venobox.js to 2.1.6
* Update: prefix settings fields
* Update: escape output
* Update: 3rd party services documentation

= 1.0.2 =
* Update VenoBox.js to 2.1.3
* Update: Tab navigation for settings page
* Update: FitView option as class venobox-fitview
* Update: prevent direct access to files

= 1.0.1 =
* Update: VenoBox.js to 2.0.9

= 1.0.0 =
* First release

 == Upgrade Notice ==

= 1.0.0 =
* First release
