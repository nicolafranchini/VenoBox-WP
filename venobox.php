<?php
/**
 * Plugin Name: VenoBox
 * Plugin URI: https://wordpress.org/plugins/venobox/
 * Description: Modal windows for images, videos, inline contents, iFrames, Ajax requests. Touch swipe galleries.
 * Author: Nicola Franchini
 * Version: 1.0.0
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: venobox
 * Domain Path: /languages
 *
 * @package Venobox
 */

define( 'VENOBOX_PLUGIN_VERSION', '1.0.0' );
if ( ! class_exists( 'VenoBox_Plugin', false ) ) {
	require_once __DIR__ . '/include/class-venobox-plugin.php';
}
