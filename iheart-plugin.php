<?php

/**
 * Plugin Name:     iHeart Plugin
 * Plugin URI:      https://iheartpublix.com
 * Description:     Category Labels and CTA's for iHeart sites.
 * Version:         0.2.0
 *
 * Author:          BizBudding
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main iHeart_Plugin Class.
 *
 * @since 0.1.0
 */
final class iHeart_Plugin {

	/**
	 * @var   iHeart_Plugin The one true iHeart_Plugin
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main iHeart_Plugin Instance.
	 *
	 * Insures that only one instance of iHeart_Plugin exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    iHeart_Plugin::setup_constants() Setup the constants needed.
	 * @uses    iHeart_Plugin::includes() Include the required files.
	 * @uses    iHeart_Plugin::hooks() Activate, deactivate, etc.
	 * @see     iHeart_Plugin()
	 * @return  object | iHeart_Plugin The one true iHeart_Plugin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup
			self::$instance = new iHeart_Plugin;
			// Methods
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'iheart-plugin' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'iheart-plugin' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'IHEARTPUBLIX_PLUGIN_VERSION' ) ) {
			define( 'IHEARTPUBLIX_PLUGIN_VERSION', '0.2.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'IHEARTPUBLIX_PLUGIN_DIR' ) ) {
			define( 'IHEARTPUBLIX_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Includes Path
		if ( ! defined( 'IHEARTPUBLIX_PLUGIN_INCLUDES_DIR' ) ) {
			define( 'IHEARTPUBLIX_PLUGIN_INCLUDES_DIR', IHEARTPUBLIX_PLUGIN_DIR . 'includes/' );
		}

		// Plugin Folder URL.
		if ( ! defined( 'IHEARTPUBLIX_PLUGIN_URL' ) ) {
			define( 'IHEARTPUBLIX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}


		// Plugin Root File.
		if ( ! defined( 'IHEARTPUBLIX_PLUGIN_FILE' ) ) {
			define( 'IHEARTPUBLIX_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Base Name
		if ( ! defined( 'IHEARTPUBLIX_PLUGIN_BASENAME' ) ) {
			define( 'IHEARTPUBLIX_PLUGIN_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
		}

	}

	/**
	 * Include required files.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function includes() {
		foreach ( glob( IHEARTPUBLIX_PLUGIN_INCLUDES_DIR . '*.php' ) as $file ) { include $file; }
	}

	/**
	 * Run the hooks.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @since   0.1.0
	 * @return  void
	 */
	public function hooks() {

		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';

		add_action( 'admin_init',         array( $this, 'updater' ) );
		add_action( 'init',               array( $this, 'register_content_types' ) );
		add_action( 'cmb2_admin_init',    array( $this, 'register_cmb2_box' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
	}

	/**
	 * Setup the updater.
	 *
	 * @uses    https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return  void
	 */
	public function updater() {

		// Bail if current user cannot manage plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'Puc_v4_Factory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = Puc_v4_Factory::buildUpdateChecker( 'https://github.com/bizbudding/iheart-plugin/', __FILE__, 'iheart-plugin' );
	}

	/**
	 * Register content types.
	 *
	 * @return  void
	 */
	public function register_content_types() {

		/***********************
		 *  Custom Taxonomies  *
		 ***********************/

		 // Coupon CTA.
		register_taxonomy( 'coupon_cta', array( 'post' ), array(
			'labels' => array(
				'name'                       => _x( 'CTAs', 'CTA General Name',   'iheartpublix' ),
				'singular_name'              => _x( 'CTA',  'CTA Singular Name',  'iheartpublix' ),
				'menu_name'                  => __( 'CTAs',                       'iheartpublix' ),
				'all_items'                  => __( 'All Items',                  'iheartpublix' ),
				'parent_item'                => __( 'Parent Item',                'iheartpublix' ),
				'parent_item_colon'          => __( 'Parent Item:',               'iheartpublix' ),
				'new_item_name'              => __( 'New Item Name',              'iheartpublix' ),
				'add_new_item'               => __( 'Add New Item',               'iheartpublix' ),
				'edit_item'                  => __( 'Edit Item',                  'iheartpublix' ),
				'update_item'                => __( 'Update Item',                'iheartpublix' ),
				'view_item'                  => __( 'View Item',                  'iheartpublix' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'iheartpublix' ),
				'add_or_remove_items'        => __( 'Add or remove items',        'iheartpublix' ),
				'choose_from_most_used'      => __( 'Choose from the most used',  'iheartpublix' ),
				'popular_items'              => __( 'Popular Items',              'iheartpublix' ),
				'search_items'               => __( 'Search Items',               'iheartpublix' ),
				'not_found'                  => __( 'Not Found',                  'iheartpublix' ),
			),
			'hierarchical'       => true,
			'public'             => false,
			'show_admin_column'  => true,
			'show_in_nav_menus'  => true,
			'show_in_quick_edit' => true,
			'show_tagcloud'      => true,
			'show_ui'            => true,
			'rewrite'            => false,
		) );

	}

	/**
	 * Create custom field to mark a category to be shown as a label on the post.
	 *
	 * @return  void
	 */
	public function register_cmb2_box() {

		$cmb_term = new_cmb2_box( array(
			'id'               => 'iheart_term_edit',
			'title'            => 'Coupon Category Settings',
			'object_types'     => array( 'term' ),
			'taxonomies'       => array( 'category' ),
			'new_term_section' => true,
		) );

		$cmb_term->add_field( array(
			'name' => 'Coupon Label',
			'desc' => 'Show this category as a label on posts.',
			'id'   => 'iheart_label',
			'type' => 'checkbox',
		) );

	}

	/**
	 * Plugin activation.
	 *
	 * @return  void
	 */
	public function activate() {
		// Maybe create Expired category.
		if ( ! term_exists( $term, $taxonomy, $parent ) ) {
			// Create the term.
			$term = wp_insert_term( 'Expired', 'category', array(
				'description' => '',
				'slug'        => 'expired',
			) );
			// If no error.
			if ( $term && ! is_wp_error( $term ) ) {
				// Set category to be a label by default.
				update_term_meta( $term['term_id'], 'iheart_label', 'on' );
			}
		}
		// Flush.
		$this->register_content_types();
		flush_rewrite_rules();
	}

	/**
	 * Enqueue files.
	 *
	 * @return  void
	 */
	function enqueue() {
		$debug  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		$suffix = $debug ? '' : '.min';
		wp_enqueue_style( 'iheart', IHEARTPUBLIX_PLUGIN_URL . "assets/css/iheart{$suffix}.css", array(), IHEARTPUBLIX_PLUGIN_VERSION );
	}

}

/**
 * The main function for that returns iHeart_Plugin
 *
 * The main function responsible for returning the one true iHeart_Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = iHeart_Plugin(); ?>
 *
 * @since 0.1.0
 *
 * @return object|iHeart_Plugin The one true iHeart_Plugin Instance.
 */
function iHeart_Plugin() {
	return iHeart_Plugin::instance();
}

// Get iHeart_Plugin Running.
iHeart_Plugin();
