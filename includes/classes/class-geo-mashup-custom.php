<?php
/**
 * Geo Mashup Custom class.
 *
 * Extends Geo Mashup functionality.
 *
 * @package WPCV_EO_Maps
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The Geo Mashup Custom class.
 *
 * @since 1.0
 */
class WPCV_EO_Maps_Geo_Mashup_Custom {

	/**
	 * The array of data for this class.
	 *
	 * This is a replacement for the "dir_path" and "url_path" properties which
	 * are accessed directly by Geo Mashup. Removing them triggers "__get()" so
	 * that their values can be filtered.
	 *
	 * Precedence is still given to template files in theme directories, but if
	 * a plugin wants to supply template files, the filters in this class allows
	 * it to do so.
	 *
	 * @since 1.0
	 * @access public
	 * @var array $data
	 */
	public $data = [];

	/**
	 * The array of available files.
	 *
	 * @since 1.0
	 * @access public
	 * @var array $files
	 */
	public $files = [];

	/**
	 * Class constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// Path and URL to the directory holding the templates.
		$this->data['dir_path'] = WPCV_EO_GEO_PATH . 'assets/templates/geo-mashup/';
		$this->data['url_path'] = WPCV_EO_GEO_URL . 'assets/templates/geo-mashup/';

	}

	/**
	 * Magic getter.
	 *
	 * Since Geo Mashup accesses the properties of this object directly, we need
	 * this in order to make those properties filterable.
	 *
	 * @since 1.0
	 *
	 * @param string $key The property name.
	 * @return string|null
	 */
	public function __get( $key ) {

		// Return filtered values.
		if ( 'dir_path' === $key ) {
			return $this->get_dir_path();
		}
		if ( 'url_path' === $key ) {
			return $this->get_url_path();
		}

		// Return null for anything else.
		return null;

	}

	/**
	 * Gets the path to the directory holding the templates.
	 *
	 * @since 1.0
	 *
	 * @return string $dir_path The path to the directory holding the templates.
	 */
	private function get_dir_path() {

		/**
		 * Filter the path to the directory holding the templates.
		 *
		 * @since 1.0
		 *
		 * @param string $dir_path The path to the directory holding the templates.
		 */
		return apply_filters( 'wpcv_eo_maps/geo_mashup_custom/dir_path', $this->data['dir_path'] );

	}

	/**
	 * Gets the URL to the directory holding the templates.
	 *
	 * @since 1.0
	 *
	 * @return string $url_path The URL to the directory holding the templates.
	 */
	private function get_url_path() {

		/**
		 * Filter the URL to the directory holding the templates.
		 *
		 * @since 1.0
		 *
		 * @param string $url_path The URL to the directory holding the templates.
		 */
		return apply_filters( 'wpcv_eo_maps/geo_mashup_custom/url_path', $this->data['url_path'] );

	}

	/**
	 * Gets the URL of a custom file if it exists.
	 *
	 * @since 1.0
	 *
	 * @param string $file The custom file to check for.
	 * @return string $url The URL or false if the file is not found.
	 */
	public function file_url( $file ) {

		// Init return.
		$url = false;

		// Build inventory of custom files.
		$this->build_inventory();

		// Assign if found.
		if ( ! empty( $this->files[ $file ] ) ) {
			$url = $this->files[ $file ];
		}

		/**
		 * Filter the requested URL.
		 *
		 * @since 1.0
		 *
		 * @param string $url The URL of the custom file.
		 * @param string $file The custom file being checked.
		 * @param array $files The full array of custom files.
		 */
		return apply_filters( 'wpcv_eo_maps/geo_mashup_custom/file_url', $url, $file, $this->files );

	}

	/**
	 * Build inventory of custom files.
	 *
	 * Built on demand when a File URL is requested.
	 *
	 * @since 1.0
	 */
	private function build_inventory() {

		// Only do this once.
		if ( ! empty( $this->files ) ) {
			return;
		}

		// Grab handle for this plugin's directory.
		$dir_handle = opendir( $this->get_dir_path() );
		if ( ! $dir_handle ) {
			return;
		}

		// Files to exclude.
		$excludes = [
			'.',
			'..',
			'.editorconfig',
			'.git',
			'.gitignore',
			'.DS_Store',
			basename( __FILE__ ),
			'phpcs.xml',
			'languages',
		];

		// Build inventory of custom files.
		// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
		while ( ( $custom_file = readdir( $dir_handle ) ) !== false ) {
			if ( ! in_array( $custom_file, $excludes, true ) && ! strpos( $custom_file, '-sample' ) && ! is_dir( $custom_file ) ) {
				$this->files[ $custom_file ] = trailingslashit( $this->url_path ) . $custom_file;
			}
		}

		// Close directory handle.
		closedir( $dir_handle );

	}

}
