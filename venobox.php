<?php
/**
 * Plugin Name: VenoBox
 * Plugin URI: https://wordpress.org/plugins/venobox/
 * Description: The modern, lightweight lightbox for images, videos, and galleries. No jQuery dependency. Features touch-swipe navigation & WooCommerce product gallery support.
 * Author: Nicola Franchini
 * Author URI: https://veno.es
 * Version: 1.1.0
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: venobox
 * Domain Path: /languages
 *
 * @package Venobox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VBOX_VENOBOX_PLUGIN_VERSION', '1.1.0' );
if ( ! class_exists( 'VenoBox_Plugin', false ) ) {
	require_once __DIR__ . '/include/class-venobox-plugin.php';
}
