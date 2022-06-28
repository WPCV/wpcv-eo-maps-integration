<?php
/**
 * Plugin Name: Integrate Event Organiser with Maps
 * Plugin URI: https://github.com/WPCV/wpcv-eo-maps-integration
 * GitHub Plugin URI: https://github.com/WPCV/wpcv-eo-maps-integration
 * Description: Provides mapping integration for Event Organiser.
 * Author: WPCV
 * Author URI: https://github.com/WPCV
 * Version: 1.0a
 * Requires at least: 5.7
 * Requires PHP: 7.1
 * Text Domain: wpcv-eo-maps-integration
 * Domain Path: /languages
 *
 * @package WPCV_EO_Maps
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set plugin version here.
define( 'WPCV_EO_GEO_VERSION', '1.0a' );

// Set our bulk operations flag here.
if ( ! defined( 'WPCV_EO_GEO_BULK' ) ) {
	define( 'WPCV_EO_GEO_BULK', false );
}

// Store reference to this file.
if ( ! defined( 'WPCV_EO_GEO_FILE' ) ) {
	define( 'WPCV_EO_GEO_FILE', __FILE__ );
}

// Store URL to this plugin's directory.
if ( ! defined( 'WPCV_EO_GEO_URL' ) ) {
	define( 'WPCV_EO_GEO_URL', plugin_dir_url( WPCV_EO_GEO_FILE ) );
}

// Store PATH to this plugin's directory.
if ( ! defined( 'WPCV_EO_GEO_PATH' ) ) {
	define( 'WPCV_EO_GEO_PATH', plugin_dir_path( WPCV_EO_GEO_FILE ) );
}

// Set debug flag.
define( 'WPCV_EO_GEO_DEBUG', false );

/**
 * Plugin bootstrap class.
 *
 * A class that encapsulates this plugin's functionality.
 *
 * @since 1.0
 */
class WPCV_EO_Maps {

	/**
	 * The class instance.
	 *
	 * @since 1.0
	 * @access private
	 * @var object $instance The class instance.
	 */
	private static $instance;

	/**
	 * Mapping object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $mapping The Mapping object.
	 */
	public $mapping;

	/**
	 * Event Organiser object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $eo The Event Organiser object.
	 */
	public $eo;

	/**
	 * Geo Mashup object.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $eo The Geo Mashup object.
	 */
	public $geo;

	/**
	 * Dependency check flag.
	 *
	 * @since 1.0
	 * @access public
	 * @var object $okay_to_load True if dependency check succeeds, false otherwise.
	 */
	public $okay_to_load = false;

	/**
	 * Dummy instance constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {}

	/**
	 * Returns a single instance of this object when called.
	 *
	 * @since 1.0
	 *
	 * @return object $instance The instance.
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) ) {

			// Instantiate.
			self::$instance = new WPCV_EO_Maps();

			// Enable translation first.
			add_action( 'plugins_loaded', [ self::$instance, 'enable_translation' ] );

			// Setup plugin when plugins have been loaded.
			add_action( 'plugins_loaded', [ self::$instance, 'initialise' ] );

		}

		// Always return instance.
		return self::$instance;

	}

	/**
	 * Initialise this plugin.
	 *
	 * @since 1.0
	 */
	public function initialise() {

		// Check dependencies.
		if ( ! $this->check_dependencies_on_load() ) {
			return;
		}

		// Bootstrap this plugin.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Broadcast that this plugin is loaded.
		 *
		 * Used internally by included classes in order to bootstrap.
		 *
		 * @since 1.0
		 */
		do_action( 'wpcv_eo_maps/loaded' );

	}

	/**
	 * Include plugin files.
	 *
	 * @since 1.0
	 */
	private function include_files() {

		// Include class files.
		include WPCV_EO_GEO_PATH . 'includes/classes/class-mapping.php';
		include WPCV_EO_GEO_PATH . 'includes/classes/class-eo-venue.php';
		include WPCV_EO_GEO_PATH . 'includes/classes/class-geo-base.php';

		// Include Geo Mashup class if plugin is present.
		if ( class_exists( 'GeoMashup' ) ) {
			include WPCV_EO_GEO_PATH . 'includes/classes/class-geo-mashup.php';
		}

	}

	/**
	 * Set up plugin objects.
	 *
	 * @since 1.0
	 */
	private function setup_objects() {

		// Initialise objects.
		$this->mapping = new WPCV_EO_Maps_Mapping();
		$this->eo      = new WPCV_EO_Maps_EO_Venue();

		// Initialise Geo Mashup object if plugin is present.
		if ( class_exists( 'GeoMashup' ) ) {
			$this->geo = new WPCV_EO_Maps_Geo_Mashup();
		}

		// If all else fails, use Base class.
		if ( ! isset( $this->geo ) ) {
			$this->geo = new WPCV_EO_Maps_Geo_Base();
		}

	}

	/**
	 * Register hooks.
	 *
	 * Add general hooks here.
	 *
	 * @since 1.0
	 */
	private function register_hooks() {

	}

	/**
	 * Plugin activation.
	 *
	 * @since 1.0
	 */
	public function activate() {

		$this->check_dependencies();

		/**
		 * Broadcast that this plugin has been activated.
		 *
		 * @since 1.0
		 */
		do_action( 'wpcv_eo_maps/activated' );

	}

	/**
	 * Check if this plugin is network activated.
	 *
	 * @since 1.0
	 *
	 * @return bool $is_network_active True if network activated, false otherwise.
	 */
	public function is_network_activated() {

		// Only need to test once.
		static $is_network_active;

		// Have we done this already?
		if ( isset( $is_network_active ) ) {
			return $is_network_active;
		}

		// If not multisite, it cannot be.
		if ( ! is_multisite() ) {
			$is_network_active = false;
			return $is_network_active;
		}

		// Make sure plugin file is included when outside admin.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// Get path from 'plugins' directory to this plugin.
		$this_plugin = plugin_basename( WPCV_EO_GEO_FILE );

		// Test if network active.
		$is_network_active = is_plugin_active_for_network( $this_plugin );

		return $is_network_active;

	}

	/**
	 * Load translation files.
	 *
	 * Reference on how to implement translation in WordPress:
	 * http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/
	 *
	 * @since 1.0
	 */
	public function enable_translation() {

		// Load translations if present.
		// phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
		load_plugin_textdomain(
			// Unique name.
			'wpcv-eo-maps-integration',
			// Deprecated argument.
			'',
			// Relative path to translation files.
			dirname( plugin_basename( WPCV_EO_GEO_FILE ) ) . '/languages/'
		);

	}

	/**
	 * Check plugin dependencies when already installed.
	 *
	 * If any of these checks fail, this plugin will skip its load procedures.
	 *
	 * @since 1.0
	 *
	 * @return bool True if dependency check passes, false otherwise.
	 */
	public function check_dependencies_on_load() {

		// Bail if Event Organiser is not available.
		if ( ! defined( 'EVENT_ORGANISER_VER' ) ) {
			return false;
		}

		// We're good to go.
		return true;

	}

	/**
	 * Check plugin dependencies on plugin activation.
	 *
	 * If any of these checks fail, this plugin will self-deactivate and exit.
	 *
	 * @since 1.0
	 */
	public function check_dependencies() {

		// Bail if Event Organiser is not available.
		if ( ! defined( 'EVENT_ORGANISER_VER' ) ) {
			$this->display_eo_required_notice();
		}

	}

	/**
	 * Display Event Organiser required notice.
	 *
	 * @since 1.0
	 */
	public function display_eo_required_notice() {

		$heading = __( 'Activation failed', 'wpcv-eo-maps-integration' );

		$plugin = '<strong>' . __( 'Integrate Event Organiser with Maps', 'wpcv-eo-maps-integration' ) . '</strong>';
		$eo     = '<strong>' . __( 'Event Organiser', 'wpcv-eo-maps-integration' ) . '</strong>';

		$requires = sprintf(
			/* translators: 1: The plugin name, 2: Event Organiser */
			__( '%1$s requires %2$s to be installed and activated.', 'wpcv-eo-maps-integration' ),
			$plugin,
			$eo
		);
		$deactivated = sprintf(
			/* translators: %s: Event Organiser */
			__( 'This plugin has been deactivated! Please activate %s and try again.', 'wpcv-eo-maps-integration' ),
			$eo
		);
		$back = sprintf(
			/* translators: 1: The opening anchor tag, 2: The closing anchor tag */
			__( 'Back to the WordPress %1$splugins page%2$s.', 'wpcv-eo-maps-integration' ),
			'<a href="' . esc_url( get_admin_url( null, 'plugins.php' ) ) . '">',
			'</a>'
		);

		$message  = '<h1>' . $heading . '</h1>';
		$message .= '<p>' . $requires . '</p>';
		$message .= '<p>' . $deactivated . '</p>';
		$message .= '<p>' . $back . '</p>';

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		deactivate_plugins( plugin_basename( WPCV_EO_GEO_FILE ) );

		// phpcs:ignore: WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die( $message );

	}

	/**
	 * Write to the error log.
	 *
	 * @since 1.0
	 *
	 * @param array $data The data to write to the log file.
	 */
	public function log_error( $data = [] ) {

		// Skip if not debugging.
		if ( WPCV_EO_GEO_DEBUG === false ) {
			return;
		}

		// Skip if empty.
		if ( empty( $data ) ) {
			return;
		}

		// Format data.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$error = print_r( $data, true );

		// Write to log file.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $error );

	}

}

/**
 * Instantiate plugin.
 *
 * @since 1.0
 *
 * @return WPCV_EO_Maps The plugin instance.
 */
function wpcv_eo_maps() {
	return WPCV_EO_Maps::instance();
}

wpcv_eo_maps();

register_activation_hook( __FILE__, [ wpcv_eo_maps(), 'activate' ] );
