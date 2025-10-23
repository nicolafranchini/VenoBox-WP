<?php
/**
 * VenoBox
 *
 * @since  1.0.0
 *
 * @category  WordPress_Plugin
 * @package   VenoBox
 * @author    Author: Nicola Franchini
 * @link      https://wordpress.org/plugins/venobox/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Main plugin class
 *
 * @since  1.0.0
 */
class VenoBox_Plugin {

	/**
	 * Holds an instance of the object
	 *
	 * @var VenoBox_Lightbox
	 */
	protected static $instance = null;

	/**
	 * Plugin options name
	 *
	 * @var string
	 */
	private $options_name = 'venobox_options';

	/**
	 * Venobox.js version
	 *
	 * @var string
	 */
	private $venobox_js_version = '2.1.8';

	/**
	 * Plugin name
	 *
	 * @var slug
	 */
	private $slug = 'venobox';

	/**
	 * Plugin's public display name.
	 *
	 * @var string
	 */
	private $plugin_name = 'VenoBox';

	/**
	 * Returns the running object
	 *
	 * @return VenoBox_Lightbox
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Initiate hooks
	 */
	public function hooks() {
		register_activation_hook( dirname( __DIR__ ) . '/' . $this->slug . '.php', array( $this, 'activate_plugin' ) );
		add_filter( 'plugin_action_links_' . dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/venobox.php', array( $this, 'action_links' ) );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'plugin_page' ) );
		add_action( 'admin_init', array( $this, 'plugin_settings' ) );
		add_action( 'after_setup_theme', array( $this, 'woocommerce_settings' ), 99 );

		// WP 3.0+.
		add_action( 'add_meta_boxes', array( $this, 'post_options_metabox' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );

		// Review notice.
		add_action( 'admin_init', array( $this, 'check_installation_date' ) );
		add_action( 'admin_notices', array( $this, 'display_review_notice' ) );
		add_action( 'wp_ajax_' . $this->slug . '_dismiss_review_notice', array( $this, 'dismiss_review_notice' ) );
	}

	/**
	 * Add the venobox option metabox to all post types
	 */
	public function post_options_metabox() {
		add_meta_box( 'post_options', __( 'VenoBox', 'venobox' ), array( $this, 'create_meta' ), get_post_types(), 'side', 'low' );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'venobox', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages' );
	}

	/**
	 * Setup JavaScript and CSS
	 */
	public function enqueue_scripts() {

		/* Get the current post ID. */
		$post_id = get_the_ID();
		$disable_venobox = get_post_meta( $post_id, 'venobox_disabled', true );

		$options = get_option( 'venobox_options' );
		$options_default = array(
			'numeratio' => '',
			'infinigall' => '',
			'max_width' => '100%',
			'share_style' => 'pill',
			'title_style' => 'bar',
			'nav_keyboard' => '',
			'nav_touch' => '',
			'nav_speed' => 300,
			'all_images' => false,
			'title_select' => 1,
			'title_position' => 'top',
			'all_videos' => false,
			'border_width' => '',
			'border_color' => 'rgba(255,255,255,1)',
			'preloader' => 'bounce',
			'nav_elements' => '#fff',
			'nav_elements_bg' => 'rgba(0,0,0,0.85)',
			'autoplay' => false,
			'overlay' => 'rgba(0,0,0,0.85)',
			'bb_lightbox' => false,
			'woocommerce' => false,
			'facetwp' => false,
			'searchfp' => false,
			'arrows' => '',
			'share' => false,
			'ratio' => '16x9',
			'fit_view' => false,
			'initial_scale' => 0.9,
			'transition_speed' => 200,
		);
		$options = wp_parse_args( $options, $options_default );

		$debug = ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) ? '' : '.min';

		// $enabled = ( ( $options['all_images'] || $options['all_videos'] ) && ! strlen( $disable_venobox ) ) || ( ( ! $options['all_images'] && ! $options['all_videos'] ) && strlen( $disable_venobox ) );
		// if ( $enabled ) {
		if ( ! strlen( $disable_venobox ) ) {
			wp_enqueue_style( 'venobox-wp', plugin_dir_url( __DIR__ ) . 'assets/venobox/dist/venobox' . $debug . '.css', array(), $this->venobox_js_version, 'all' );
			wp_enqueue_script( 'venobox-wp', plugin_dir_url( __DIR__ ) . 'assets/venobox/dist/venobox' . $debug . '.js', array(), $this->venobox_js_version, true );
			wp_register_script( 'venobox-start', plugin_dir_url( __DIR__ ) . 'js/venobox-start.js', array( 'venobox-wp' ), VBOX_VENOBOX_PLUGIN_VERSION, true );

			// Disable jQuery MagnificPopUp used on BeaverBuilder.
			if ( $options['bb_lightbox'] ) {
				add_action( 'wp_print_scripts', array( $this, 'remove_magnificpopup' ), 100 );
				add_filter( 'fl_builder_override_lightbox', '__return_true' );
			}

			// adjust number to px value.
			$border_width = isset( $options['border_width'] ) ? $options['border_width'] . 'px' : '';

			$data = array(
				'disabled' => $disable_venobox,
				'autoplay' => (bool) $options['autoplay'],
				'border_color' => $options['border_color'],
				'border_width' => $border_width,
				'infinigall' => (bool) $options['infinigall'],
				'max_width' => $options['max_width'],
				'arrows' => (bool) $options['arrows'], // navigation.
				'nav_keyboard' => (bool) $options['nav_keyboard'],
				'nav_touch' => (bool) $options['nav_touch'],
				'nav_speed' => $options['nav_speed'],
				'numeratio' => (bool) $options['numeratio'],
				'overlay' => $options['overlay'],
				'share' => (bool) $options['share'],
				'share_style' => $options['share_style'],
				'preloader' => $options['preloader'],
				'title_select' => (int) $options['title_select'],
				'title_position' => $options['title_position'],
				'title_style' => $options['title_style'],
				'all_images' => (bool) $options['all_images'],
				'all_videos' => (bool) $options['all_videos'],
				'nav_elements' => $options['nav_elements'],
				'nav_elements_bg' => $options['nav_elements_bg'],
				'woocommerce' => (bool) $options['woocommerce'],
				'bb_lightbox' => (bool) $options['bb_lightbox'],
				'facetwp' => (bool) $options['facetwp'],
				'searchfp' => (bool) $options['searchfp'],
				'ratio' => $options['ratio'],
				'fit_view' => $options['fit_view'],
				'initial_scale' => $options['initial_scale'],
				'transition_speed' => $options['transition_speed'],
			);

			// Access variables from venobox-init using venoboxVars.
			wp_localize_script( 'venobox-start', 'VENOBOX', $data );
			wp_enqueue_script( 'venobox-start' );
		}
	}

	/**
	 * Wrapper function around get_option
	 *
	 * @param str   $key     Options array key.
	 * @param mixed $default Optional default value.
	 * @return mixed         Option value
	 */
	public function get_option( $key = '', $default = false ) {
		$opts = get_option( $this->options_name, $default );
		$val = $default;
		if ( 'all' == $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}
		return $val;
	}

	/**
	 * Disable jQuery MagnificPopUp
	 */
	public function remove_magnificpopup() {
		wp_dequeue_script( 'jquery-magnificpopup' );
		wp_dequeue_style( 'jquery-magnificpopup' );
	}

	/**
	 * Add scripts in back-end for demo purpose.
	 *
	 * @param str $hook settings page.
	 */
	public function admin_scripts( $hook ) {
		// settings_page_venobox-options .
		// toplevel_page_venobox-options .

		// echo $hook; // debug.
		if ( 'settings_page_venobox-options' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker-alpha', plugin_dir_url( __DIR__ ) . 'js/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), '3.0.0', true );
		wp_enqueue_style( 'venobox-admin', plugin_dir_url( __DIR__ ) . 'css/admin.css', array(), VBOX_VENOBOX_PLUGIN_VERSION );
		wp_enqueue_script( 'venobox-admin', plugin_dir_url( __DIR__ ) . 'js/admin.js', array( 'jquery-ui-tabs', 'wp-color-picker-alpha' ), VBOX_VENOBOX_PLUGIN_VERSION, true );
	}

	/**
	 * Add links to settings page
	 *
	 * @param array $links default plugin links.
	 *
	 * @return additional $links in plugins page
	 */
	public function action_links( $links ) {
		$newlink = '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=venobox-options' ) ) . '">' . __( 'Settings', 'venobox' ) . '</a>';
		array_unshift( $links, $newlink );
		return $links;
	}

	/**
	 * Create the plugin option page.
	 */
	public function plugin_page() {
		$my_menu_page = add_options_page(
			__( 'VenoBox Settings', 'venobox' ), // $page_title.
			__( 'VenoBox', 'venobox' ), // $menu_title.
			'manage_options', // $capability.
			'venobox-options', // $menu-slug.
			array( $this, 'plugin_options_page' ),
		);
		add_action( 'load-' . $my_menu_page, array( $this, 'help_tab' ) );
	}

	/**
	 * Help tab.
	 */
	public function help_tab() {
		$screen = get_current_screen();
		$screen->add_help_tab(
			array(
				'id' => 'venobox_help',
				'title' => __( 'More options' ),
				'content' => '<p><b>Inline contents</b>: add the class <code>venobox-inline</code> to your links.</p>
<pre>
&lt;a class="venobox-inline" href="#inline"&gt;Open inline content&lt;/a&gt;
</pre>
				<p><b>Ajax requests</b>: add the class <code>venobox-ajax</code> to your links.</p>
<pre>
&lt;a class="venobox-ajax" href="ajax-call.php">Retrieve data via Ajax&lt;/a&gt;
</pre>
				<p><b>iFrames</b>: add the class <code>venobox-iframe</code> to your links.</p>
<pre>
&lt;a class="venobox-iframe" href="https://www.example.ext"&gt;Open Iframe&lt;/a&gt;
</pre>',
			)
		);
		$screen->set_help_sidebar( '<p>Further reference:</p><p><b>Venobox JS</b><br><a href="http://veno.es/venobox/" target="_blank">Plugin Home</a><br><a href="https://github.com/nicolafranchini/VenoBox/" target="_blank">Github</a></p>' );
	}
	/**
	 * Include the plugin option page.
	 */
	public function plugin_options_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed' );
		} ?>
		<div class="wrap venobox-options">
			<h1><?php esc_html_e( 'VenoBox Options', 'venobox' ); ?></h1>
			<form method="post" action="options.php">
				<div id="tabs">
					<ul class="nav-tab-wrapper">
						<li><a href="#tab-general" class="nav-tab"><?php esc_attr_e( 'General settings', 'venobox' ); ?></a></li>
						<li><a href="#tab-style" class="nav-tab"><?php esc_attr_e( 'Style', 'venobox' ); ?></a></li>
						<li><a href="#tab-integration" class="nav-tab"><?php esc_attr_e( 'Integrations', 'venobox' ); ?></a></li>
					</ul>
			<?php
			// settings_errors(); // only on single option page.
			settings_fields( $this->options_name . '_group' );
			do_settings_sections( 'venobox-options' );
			submit_button();
			?>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Register our option fields
	 */
	public function plugin_settings() {

		$section = 'venobox_section';
		$page = 'venobox-options';
		$prefix = 'vbox_';

		register_setting(
			$this->options_name . '_group', // option group.
			$this->options_name // option name.
		);
		add_settings_section(
			$section,
			__( 'General Settings', 'venobox' ),
			array( $this, 'venobox_section_callback' ),
			$page,
			array(
				'before_section' => '<div class="tabs-content" id="tab-general">', // Open general settings TAB
				// 'after_section' => '</div>', // Close it later, after videos.
			)
		);

		$args = array(
			'name' => 'all_images',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Add VenoBox for all linked images & galleries', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'all_images',
			__( 'Link Images', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section,
			$args
		);

		$args = array(
			'name' => 'all_videos',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Add VenoBox for all the links to YouTube, Vimeo, and .mp4, .webm, .ogg videos', 'venobox' ),
			'default' => '',
		);

		add_settings_field(
			$prefix . 'all_videos',
			__( 'Link videos', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section,
			$args
		);

		add_settings_section(
			$section . '_images',
			__( 'Images', 'venobox' ),
			array( $this, 'venobox_section_callback' ),
			$page,
			array(
				// 'before_section' => '<div class="tabs-content" id="tab-images">',
				// 'after_section' => '</div>',
			)
		);
		$args = array(
			'name' => 'fit_view',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Resize the images to fit within the viewport height', 'venobox' ),
			'default' => '',
			'help' => __( 'To set FitView only to specific elements add the class venobox-fitview to one of their containers', 'venobox' ),
		);
		add_settings_field(
			$prefix . 'fit_view',
			__( 'Fit view', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_images',
			$args
		);

		$args = array(
			'name' => 'title_select',
			'type' => 'input',
			'subtype' => 'radio',
			'options' => array(
				array(
					'value' => 1,
					'label' => __( 'ALT text', 'venobox' ),
				),
				array(
					'value' => 2,
					'label' => __( 'Title', 'venobox' ),
				),
				array(
					'value' => 3,
					'label' => __( 'Caption', 'venobox' ),
				),
				array(
					'value' => 4,
					'label' => __( 'None', 'venobox' ),
				),
			),
			'default' => 4,
			'help' => __( 'Media attribute to be used as title', 'venobox' ),
		);
		add_settings_field(
			$prefix . 'title_select',
			__( 'Item Title', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_images',
			$args
		);

		add_settings_section(
			$section . '_videos',
			__( 'Videos', 'venobox' ),
			array( $this, 'venobox_section_callback' ),
			$page,
			array(
				// 'before_section' => '<div class="tabs-content" id="tab-videos">',
				// 'after_section' => '</div>', // Close general settings tab
			)
		);

		$args = array(
			'name' => 'autoplay',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Autoplay videos', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'autoplay',
			__( 'Autoplay', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_videos',
			$args
		);

		$args = array(
			'name' => 'ratio',
			'type' => 'select',
			'options' => array(
				array(
					'value' => '1x1',
					'label' => '1 : 1',
				),
				array(
					'value' => '4x3',
					'label' => '4 : 3',
				),
				array(
					'value' => '16x9',
					'label' => '16 : 9',
				),
				array(
					'value' => '21x9',
					'label' => '21 : 9',
				),
			),
			'default' => '16x9',
			'help' => __( 'Aspect ratio for video and iFrame', 'venobox' ),
		);
		add_settings_field(
			$prefix . 'ratio',
			__( 'Aspect ratio', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_videos',
			$args
		);

		add_settings_section(
			$section . '_galleries',
			__( 'Galleries', 'venobox' ),
			array( $this, 'venobox_section_callback' ),
			$page,
			array(
				// 'before_section' => '<div class="tabs-content" id="tab-galleries">',
				'after_section' => '</div>',
			)
		);

		$args = array(
			'name' => 'nav_speed',
			'type' => 'input',
			'subtype' => 'number',
			'default' => 300,
			'attributes' => array(
				'min' => 10,
				'max' => 5000,
				'step' => 10,
			),
		);
		add_settings_field(
			$prefix . 'nav_speed',
			__( 'Transition speed (ms)', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_galleries',
			$args
		);

		$args = array(
			'name' => 'numeratio',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Show Pagination for Multiple Items', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'numeratio',
			__( 'Gallery Numeration', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_galleries',
			$args
		);

		$args = array(
			'name' => 'infinigall',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Allow continous navigation, jumping from last to first item and vice versa', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'infinigall',
			__( 'Infinite Gallery', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_galleries',
			$args
		);

		$args = array(
			'name' => 'arrows',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Disable Previous & Next Arrows', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'arrows',
			__( 'Disable Navigation', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_galleries',
			$args
		);

		$args = array(
			'name' => 'nav_keyboard',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Disable keyboard navigation', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'nav_keyboard',
			__( 'Disable keyboard', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_galleries',
			$args
		);

		$args = array(
			'name' => 'nav_touch',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Disable touch swipe navigation', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'nav_touch',
			__( 'Disable swipe', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_galleries',
			$args
		);

		add_settings_section(
			$section . '_style',
			__( 'Style', 'venobox' ),
			array( $this, 'venobox_section_callback' ),
			$page,
			array(
				'before_section' => '<div class="tabs-content" id="tab-style">',
				'after_section' => '</div>',
			)
		);

		$args = array(
			'name' => 'overlay',
			'type' => 'input',
			'subtype' => 'colorpicker',
			'default' => 'rgba(0,0,0,0.85)',
		);
		add_settings_field(
			$prefix . 'overlay',
			__( 'Overlay Color', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'nav_elements',
			'type' => 'input',
			'subtype' => 'colorpicker',
			'default' => 'rgba(255,255,255,1)',
		);
		add_settings_field(
			$prefix . 'nav_elements',
			__( 'Navigation & Title Color', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'nav_elements_bg',
			'type' => 'input',
			'subtype' => 'colorpicker',
			'default' => 'rgba(0,0,0,0.85)',
		);
		add_settings_field(
			$prefix . 'nav_elements_bg',
			__( 'Title and share bar background', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'max_width',
			'type' => 'input',
			'subtype' => 'text',
			'default' => '100%',
		);
		add_settings_field(
			$prefix . 'max_width',
			__( 'Max item width', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'border_width',
			'type' => 'input',
			'subtype' => 'number',
			'default' => 0,
		);
		add_settings_field(
			$prefix . 'border_width',
			__( 'Frame Border Thickness', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'border_color',
			'type' => 'input',
			'subtype' => 'colorpicker',
			'default' => 'rgba(255,255,255,1)',
		);
		add_settings_field(
			$prefix . 'border_color',
			__( 'Frame Border Color', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'preloader',
			'type' => 'input',
			'subtype' => 'radio-image',
			'options' => array(
				array(
					'value' => 'bounce',
					'label' => '<div class="sk-bounce sk-center"><div class="sk-bounce-dot"></div><div class="sk-bounce-dot"></div></div>',
				),
				array(
					'value' => 'chase',
					'label' => '<div class="sk-chase"><div class="sk-chase-dot"></div><div class="sk-chase-dot"></div><div class="sk-chase-dot"></div><div class="sk-chase-dot"></div><div class="sk-chase-dot"></div><div class="sk-chase-dot"></div></div>',
				),
				array(
					'value' => 'circle',
					'label' => '<div class="sk-circle sk-center"><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div><div class="sk-circle-dot"></div></div>',
				),
				array(
					'value' => 'circle-fade',
					'label' => '<div class="sk-circle-fade sk-center"><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div><div class="sk-circle-fade-dot"></div></div>',
				),
				array(
					'value' => 'flow',
					'label' => '<div class="sk-flow sk-center"><div class="sk-flow-dot"></div><div class="sk-flow-dot"></div><div class="sk-flow-dot"></div></div>',
				),
				array(
					'value' => 'fold',
					'label' => '<div class="sk-fold sk-center"><div class="sk-fold-cube"></div><div class="sk-fold-cube"></div><div class="sk-fold-cube"></div><div class="sk-fold-cube"></div></div>',
				),
				array(
					'value' => 'grid',
					'label' => '<div class="sk-grid sk-center"><div class="sk-grid-cube"></div><div class="sk-grid-cube"></div><div class="sk-grid-cube"></div><div class="sk-grid-cube"></div><div class="sk-grid-cube"></div><div class="sk-grid-cube"></div><div class="sk-grid-cube"></div><div class="sk-grid-cube"></div><div class="sk-grid-cube"></div></div>',
				),
				array(
					'value' => 'plane',
					'label' => '<div class="sk-plane sk-center"></div>',
				),
				array(
					'value' => 'pulse',
					'label' => '<div class="sk-pulse sk-center"></div>',
				),
				array(
					'value' => 'swing',
					'label' => '<div class="sk-swing sk-center"><div class="sk-swing-dot"></div><div class="sk-swing-dot"></div></div>',
				),
				array(
					'value' => 'wander',
					'label' => '<div class="sk-wander sk-center"><div class="sk-wander-cube"></div><div class="sk-wander-cube"></div><div class="sk-wander-cube"></div></div>',
				),
				array(
					'value' => 'wave',
					'label' => '<div class="sk-wave sk-center"><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div></div>',
				),
			),
			'default' => 'bounce',
		);
		add_settings_field(
			$prefix . 'preloader',
			__( 'Preloader Type', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'title_position',
			'type' => 'select',
			'options' => array(
				array(
					'value' => 'top',
					'label' => __( 'Top', 'venobox' ),
				),
				array(
					'value' => 'bottom',
					'label' => __( 'Bottom', 'venobox' ),
				),
			),
			'default' => 'top',
		);
		add_settings_field(
			$prefix . 'title_position',
			__( 'Title Position', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'title_style',
			'type' => 'select',
			'options' => array(
				array(
					'value' => 'bar',
					'label' => __( 'Bar', 'venobox' ),
				),
				array(
					'value' => 'block',
					'label' => __( 'Block', 'venobox' ),
				),
				array(
					'value' => 'pill',
					'label' => __( 'Pill', 'venobox' ),
				),
				array(
					'value' => 'transparent',
					'label' => __( 'Transparent', 'venobox' ),
				),
			),
			'default' => 'bar',
		);
		add_settings_field(
			$prefix . 'title_style',
			__( 'Title style', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'share',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Enable share buttons', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'share',
			__( 'Sharing', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'share_style',
			'type' => 'select',
			'options' => array(
				array(
					'value' => 'bar',
					'label' => __( 'Bar', 'venobox' ),
				),
				array(
					'value' => 'block',
					'label' => __( 'Block', 'venobox' ),
				),
				array(
					'value' => 'pill',
					'label' => __( 'Pill', 'venobox' ),
				),
				array(
					'value' => 'transparent',
					'label' => __( 'Transparent', 'venobox' ),
				),
			),
			'default' => 'pill',
		);
		add_settings_field(
			$prefix . 'share_style',
			__( 'Share style', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'initial_scale',
			'type' => 'input',
			'subtype' => 'number',
			'default' => 0.9,
			'attributes' => array(
				'min' => 0.1,
				'max' => 1.5,
				'step' => 0.1,
			),
			'help' => __( 'Initial items transform scale', 'venobox' ),
		);

		add_settings_field(
			$prefix . 'initial_scale',
			__( 'Initial scale', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		$args = array(
			'name' => 'transition_speed',
			'type' => 'input',
			'subtype' => 'number',
			'default' => 200,
			'attributes' => array(
				'min' => 10,
				'max' => 5000,
				'step' => 10,
			),
			'help' => __( 'Transition speed for incoming items (ms)', 'venobox' ),
		);

		add_settings_field(
			$prefix . 'transition_speed',
			__( 'Transition speed', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_style',
			$args
		);

		add_settings_section(
			$section . '_integration',
			__( 'Integration', 'venobox' ),
			array( $this, 'venobox_section_callback' ),
			$page,
			array(
				'before_section' => '<div class="tabs-content" id="tab-integration">',
				'after_section' => '</div>',
			)
		);

		$args = array(
			'name' => 'woocommerce',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Support for WooCommerce', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'woocommerce',
			__( 'WooCommerce', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_integration',
			$args
		);

		$args = array(
			'name' => 'facetwp',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Support for FacetWP', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'facetwp',
			__( 'FacetWP', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_integration',
			$args
		);

		$args = array(
			'name' => 'searchfp',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Support for Search & Filter Pro', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'searchfp',
			__( 'Search & Filter Pro', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_integration',
			$args
		);

		$args = array(
			'name' => 'bb_lightbox',
			'type' => 'input',
			'subtype' => 'checkbox',
			'label' => __( 'Disable Beaver Builder Lightbox', 'venobox' ),
			'default' => '',
		);
		add_settings_field(
			$prefix . 'bb_lightbox',
			__( 'Beaver Builder', 'venobox' ),
			array( $this, 'render_settings_field' ),
			$page,
			$section . '_integration',
			$args
		);
	}

	/**
	 *  Render setting field
	 *
	 * @param array $args input options.
	 */
	public function render_settings_field( $args ) {

		$option_name = $args['name'];
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$this_option = $this->get_option( $option_name, $default );

		switch ( $args['type'] ) {
			case 'input':
				if ( 'text' == $args['subtype'] || 'number' == $args['subtype'] ) {
					?>
					<input type="<?php echo esc_attr( $args['subtype'] ); ?>" class="regular-text" name="<?php echo esc_attr( $this->options_name ) . '[' . esc_attr( $option_name ) . ']'; ?>" value="<?php echo esc_attr( $this_option ); ?>" 
						<?php
						if ( isset( $args['attributes'] ) ) {

							// print_r($args['attributes'] ); // debug.
							foreach ( $args['attributes'] as $key => $attribute ) {
								echo ' ' . esc_attr( $key ) . '="' . esc_attr( $attribute ) . '"';
							}
						}
						?>
						/>
					<?php
				}
				if ( 'checkbox' == $args['subtype'] ) {
					?>
<fieldset><label>
	<input name="<?php echo esc_attr( $this->options_name ); ?>[<?php echo esc_attr( $option_name ); ?>]" type="checkbox" value="1" <?php echo checked( 1, $this_option, false ); ?>/>
					<?php
					if ( isset( $args['label'] ) ) {
						?>
	<span class="d-inline-block"><?php echo esc_html( $args['label'] ); ?></span>
						<?php
					}
					?>
</label></fieldset>
					<?php
				}
				if ( 'radio' == $args['subtype'] || 'radio-image' == $args['subtype'] ) {
					$radioclass = 'radio' == $args['subtype'] ? 'inline-radio' : 'image-radio';
					?>
<fieldset>
					<?php
					if ( 'radio-image' == $args['subtype'] ) {
						?>
	<div class="flex-radio">
						<?php
					}
					foreach ( $args['options'] as $option ) {
						?>
	<input type="radio" class="<?php echo esc_attr( $radioclass ); ?>" name="<?php echo esc_attr( $this->options_name ) . '[' . esc_attr( $option_name ) . ']'; ?>" id="radio-<?php echo esc_attr( $option_name ) . '-' . esc_attr( $option['value'] ); ?>" value="<?php echo esc_attr( $option['value'] ); ?>" <?php echo checked( $option['value'], $this_option, false ); ?>/>
						<?php
						if ( isset( $option['label'] ) ) {
							?>
	<label for="radio-<?php echo esc_attr( $option_name ) . '-' . esc_attr( $option['value'] ); ?>"><?php echo wp_kses_post( $option['label'] ); ?></label>
							<?php
						}
						if ( 'radio' == $args['subtype'] ) {
							?>
	<br>
							<?php
						}
					}

					if ( 'radio-image' == $args['subtype'] ) {
						?>
	</div>
						<?php
					}
					?>
</fieldset>
					<?php
				}
				if ( 'colorpicker' == $args['subtype'] ) {
					?>
<input type="text" class="color-picker" data-alpha-enabled="true" data-default-color="<?php echo esc_attr( $default ); ?>" name="<?php echo esc_attr( $this->options_name ) . '[' . esc_attr( $option_name ) . ']'; ?>" value="<?php echo esc_attr( $this_option ); ?>"/>
					<?php
				}
				break;
			case 'select':
				?>
<select name="<?php echo esc_attr( $this->options_name ) . '[' . esc_attr( $option_name ) . ']'; ?>">
				<?php
				foreach ( $args['options'] as $option ) {
					?>
	<option name="<?php echo esc_attr( $this->options_name ) . '[' . esc_attr( $option_name ) . ']'; ?>" id="<?php echo esc_attr( $this_option ); ?>" type="radio" value="<?php echo esc_attr( $option['value'] ); ?>"<?php echo selected( $option['value'], $this_option, false ); ?>/><?php echo esc_html( $option['label'] ); ?></option>
					<?php
				}
				?>
</select>
				<?php
				break;
			default:
				// code...
				break;
		}
		if ( isset( $args['help'] ) ) {
			?>
			<p><?php echo esc_html( $args['help'] ); ?></p>
			<?php
		}
	}


	/**
	 * Register section callback
	 * (not much happening here)
	 */
	public function venobox_section_callback() {
		// var_dump($this->get_option('all')); // debug.
	}

	/**
	 * Include WooCommerce Settings
	 * Remove Supports for zoom/slider/gallery
	 *
	 * @since 2.0.7
	 */
	public function woocommerce_settings() {
		$woocommerce = $this->get_option( 'woocommerce', '' );
		if ( class_exists( 'WooCommerce' ) && '1' == $woocommerce ) {
			remove_theme_support( 'wc-product-gallery-zoom' );
			remove_theme_support( 'wc-product-gallery-lightbox' );
		}
	}

	/**
	 * Create VenoBox Meta
	 *
	 * @link https://gist.github.com/emilysnothere/943ea6274dc160cec271
	 */
	public function create_meta() {
		$post_id = get_the_ID();
		$value = get_post_meta( $post_id, 'venobox_disabled', true );
		wp_nonce_field( 'venobox_nonce_' . $post_id, 'venobox_nonce' );
		?>
		<div class="misc-pub-section misc-pub-section-last">
			<label><input type="checkbox" value="1" <?php checked( $value, true, true ); ?> name="venobox_disabled" /><?php esc_attr_e( 'Disable VenoBox', 'venobox' ); ?></label>
		</div>
		<?php
	}

	/**
	 * Save VenoBox Meta
	 *
	 * @param int $post_id post ID.
	 */
	public function save_meta( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$venobox_nonce = filter_input( INPUT_POST, 'venobox_nonce', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! wp_verify_nonce( $venobox_nonce, 'venobox_nonce_' . $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$venobox_check = filter_input( INPUT_POST, 'venobox_disabled', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( $venobox_check ) {
			update_post_meta( $post_id, 'venobox_disabled', $venobox_check );
		} else {
			delete_post_meta( $post_id, 'venobox_disabled' );
		}
	}


	/**
	 * Rewrite permalinks on activation, after cpt registration
	 */
	public function activate_plugin() {
		$this->register_cpt();
		flush_rewrite_rules();

		// Set activation date for new installations.
		$option_name = $this->slug . '_activation_date';
		if ( false === get_option( $option_name ) ) {
			add_option( $option_name, time() );
		}
	}

	/**
	 * Check and set the installation date if it doesn't exist.
	 * This ensures that the notice timer starts for existing users who update the plugin.
	 *
	 * @return void
	 */
	public function check_installation_date() {
		$option_name = $this->slug . '_activation_date';
		if ( false === get_option( $option_name ) ) {
			add_option( $option_name, time() );
		}
	}

	/**
	 * Display the review notice in the admin dashboard.
	 *
	 * @return void
	 */
	public function display_review_notice() {
		// Only show notice to users who can manage options.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$dismissed_option = $this->slug . '_review_notice_dismissed';
		$activation_option = $this->slug . '_activation_date';

		// Check if the notice has been dismissed.
		if ( get_option( $dismissed_option ) ) {
			return;
		}

		$activation_date = get_option( $activation_option );

		// Show notice only after 14 days of usage.
		if ( ! $activation_date || ( time() - $activation_date < 14 * DAY_IN_SECONDS ) ) {
			return;
		}

		// Enqueue the script for the notice.
		$this->enqueue_review_notice_script();

		// Dynamic CSS IDs and classes.
		$notice_id     = $this->slug . '-review-notice';
		$dismiss_class = $this->slug . '-dismiss-notice';
		$review_url    = 'https://wordpress.org/support/plugin/' . $this->slug . '/reviews/?filter=5';
		?>
		<div id="<?php echo esc_attr( $notice_id ); ?>" class="notice notice-info is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %s is the plugin name */
					esc_html__( 'Enjoying %s? Please consider leaving a 5-star review ⭐⭐⭐⭐⭐. It helps us grow and support the plugin!', 'venomaps' ),
					'<strong>' . esc_html( $this->plugin_name ) . '</strong>'
				);
				?>
			</p>
			<p>
				<a href="<?php echo esc_url( $review_url ); ?>" class="button button-primary" target="_blank">
					<?php esc_html_e( 'Sure, I’d love to!', 'venomaps' ); ?>
				</a>
				<a href="#" class="button button-secondary <?php echo esc_attr( $dismiss_class ); ?>">
					<?php esc_html_e( 'Maybe Later', 'venomaps' ); ?>
				</a>
				<a href="#" class="button button-secondary <?php echo esc_attr( $dismiss_class ); ?>">
					<?php esc_html_e( 'I Already Rated It', 'venomaps' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Enqueue the JavaScript for the review notice dismissal.
	 *
	 * @return void
	 */
	private function enqueue_review_notice_script() {
		// Dynamic script handle.
		$handle     = $this->slug . '-review-notice';
		$plugin_url = plugin_dir_url( __DIR__ );

		wp_enqueue_script(
			$handle,
			$plugin_url . 'js/admin-review-notice.js', // Adjust path if necessary.
			array(),
			VBOX_VENOBOX_PLUGIN_VERSION,
			true
		);

		// Create a unique, JS-friendly object name from the slug.
		$camel_case_slug = lcfirst( str_replace( ' ', '', ucwords( str_replace( '-', ' ', $this->slug ) ) ) );
		$object_name     = $camel_case_slug . 'ReviewNoticeData';

		wp_localize_script(
			$handle,
			$object_name, // Use the unique object name here.
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( $this->slug . '_dismiss_review_notice_nonce' ),
				'action'      => $this->slug . '_dismiss_review_notice',
				'notice_id'   => $this->slug . '-review-notice',
				'dismiss_class' => $this->slug . '-dismiss-notice',
			)
		);
	}

	/**
	 * Handles the AJAX request to dismiss the review notice.
	 *
	 * @return void
	 */
	public function dismiss_review_notice() {
		// Dynamic nonce check and option update.
		check_ajax_referer( $this->slug . '_dismiss_review_notice_nonce', 'nonce' );
		update_option( $this->slug . '_review_notice_dismissed', true );
		wp_send_json_success();
	}
}

VenoBox_Plugin::get_instance();
