<?php
/**
 * Geo Mashup class.
 *
 * Manages Geo Mashup functionality.
 *
 * @package WPCV_EO_Maps
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Geo Mashup class.
 *
 * @since 1.0
 */
class WPCV_EO_Maps_Geo_Mashup {

	/**
	 * Geo Mashup installed flag.
	 *
	 * @since 1.0
	 * @access public
	 * @var bool $is_installed True if Geo Mashup is present, false otherwise.
	 */
	public $is_installed;

	/**
	 * Class constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// Init when this plugin is fully loaded.
		add_action( 'wpcv_eo_maps/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Checks if Geo Mashup is installed.
	 *
	 * @since 1.0
	 */
	public function is_installed() {

		// Only check once.
		if ( isset( $this->is_installed ) ) {
			return $this->is_installed;
		}

		// Set flag based on Geo Mashup presence.
		if ( ! class_exists( 'GeoMashup' ) ) {
			$this->is_installed = false;
		} else {
			$this->is_installed = true;
		}

		// --<
		return $this->is_installed;

	}

	/**
	 * Initialise this object.
	 *
	 * @since 1.0
	 */
	public function initialise() {

		// Bail if Geo Mashup not installed.
		if ( ! $this->is_installed() ) {
			return;
		}

		// Register all hooks.
		$this->register_hooks();
		$this->register_mapper_hooks();

	}

	/**
	 * Register hooks that should not be unhooked.
	 *
	 * @since 1.0
	 */
	public function register_hooks() {

		// Do not show map on events screens.
		add_action( 'add_meta_boxes_event', [ $this, 'metabox_remove' ] );

	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0
	 */
	public function register_mapper_hooks() {

	}

	/**
	 * Unregister hooks.
	 *
	 * @since 1.0
	 */
	public function unregister_mapper_hooks() {

	}

	// -------------------------------------------------------------------------

	/**
	 * Removes the Geo Mashup metabox.
	 *
	 * @since 1.0
	 */
	public function metabox_remove() {

		// Do not show the Geo Mashup metabox on Event screens.
		remove_meta_box( 'geo_mashup_post_edit', 'event', 'advanced' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates a Geo Mashup Location with a given set of data.
	 *
	 * @since 1.0
	 *
	 * @param array $location The Location data.
	 * @param bool  $do_lookups Pass true to refresh Location geodata.
	 * @return int|bool $result The new Location ID, or false on failure.
	 */
	public function create( $location, $do_lookups = false ) {

		// Bail if Geo Mashup not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Cast lookups variable appropriately.
		if ( false === $do_lookups ) {
			$do_lookups = null;
		}

		// Returns Location ID or WP_Error.
		$result = GeoMashupDB::set_location( $location, $do_lookups );

		// Log and bail on error.
		if ( is_wp_error( $result ) ) {
			$e     = new Exception();
			$trace = $e->getTraceAsString();
			$data  = [
				'method'    => __METHOD__,
				'location'  => $location,
				'error'     => $result->get_error_message(),
				'backtrace' => $trace,
			];
			wpcv_eo_maps()->log_error( $data );
			return false;
		}

		// --<
		return $result;

	}

	/**
	 * Updates a Geo Mashup Location with a given set of data.
	 *
	 * @since 1.0
	 *
	 * @param array $location The Location data.
	 * @param bool  $do_lookups Pass true to refresh Location geodata.
	 * @return int|bool $location_id The Location ID, or false on failure.
	 */
	public function update( $location, $do_lookups = false ) {

		// Bail if Geo Mashup not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Log and bail if there's no Location ID.
		if ( empty( $location['id'] ) ) {
			$e     = new \Exception();
			$trace = $e->getTraceAsString();
			$data  = [
				'method'    => __METHOD__,
				'message'   => __( 'A numeric ID must be present to update a Location.', 'wpcv-eo-maps-integration' ),
				'location'  => $location,
				'backtrace' => $trace,
			];
			wpcv_eo_maps()->log_error( $data );
			return false;
		}

		// Cast lookups variable appropriately.
		if ( false === $do_lookups ) {
			$do_lookups = null;
		}

		// Pass through.
		return $this->create( $location, $do_lookups );

	}

	/**
	 * Deletes a Geo Mashup Location for a given Location or Location ID.
	 *
	 * @since 1.0
	 *
	 * @param int|array $location The numeric ID of the Location, or a Location array.
	 * @return int|bool $result The number of rows affected, or false on failure.
	 */
	public function delete( $location ) {

		// Bail if Geo Mashup not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Assign Location ID.
		if ( is_array( $location ) ) {
			$location_id = (int) $location['id'];
		} else {
			$location_id = (int) $location;
		}

		// Log and bail if there's no Location ID.
		if ( empty( $location_id ) ) {
			$e     = new \Exception();
			$trace = $e->getTraceAsString();
			$data  = [
				'method'    => __METHOD__,
				'message'   => __( 'A numeric ID must be present to delete a Location.', 'wpcv-eo-maps-integration' ),
				'location'  => $location,
				'backtrace' => $trace,
			];
			wpcv_eo_maps()->log_error( $data );
			return false;
		}

		// Wrap in an array.
		$location_ids = [ $location_id ];

		// Returns rows affected or WP_Error.
		$result = GeoMashupDB::delete_location( $location_ids );

		// Log and bail on error.
		if ( is_wp_error( $result ) ) {
			$e     = new Exception();
			$trace = $e->getTraceAsString();
			$data  = [
				'method'      => __METHOD__,
				'location_id' => $location_id,
				'location'    => $location,
				'error'       => $result->get_error_message(),
				'backtrace'   => $trace,
			];
			wpcv_eo_maps()->log_error( $data );
			return false;
		}

		// --<
		return $result;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Geo Mashup Location for a given Location ID.
	 *
	 * @since 1.0
	 *
	 * @param int $location_id The numeric ID of the Location.
	 * @return object|bool $location The Location data object, or false if not found.
	 */
	public function location_get_by_id( $location_id ) {

		// Bail if Geo Mashup not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Get Location from Geo Mashup.
		$location = GeoMashupDB::get_location( $location_id );

		// Bail if not found.
		if ( empty( $location ) ) {
			return false;
		}

		// --<
		return $location;

	}

	/**
	 * Gets the Geo Mashup Location for a given Event ID.
	 *
	 * @since 1.0
	 *
	 * @param int $event_id The numeric ID of the Event.
	 * @return object|bool $location The Location data object, or false if not found.
	 */
	public function location_get_by_event_id( $event_id ) {

		// Bail if Geo Mashup not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Get Location from Geo Mashup.
		$location = GeoMashupDB::get_post_location( $event_id );

		// Bail if not found.
		if ( empty( $location ) ) {
			return false;
		}

		// --<
		return $location;

	}

	/**
	 * Prepares the Geo Mashup Location for a given Venue.
	 *
	 * @since 1.0
	 *
	 * @param object $venue The Venue object.
	 * @return array $location The array of Location data.
	 */
	public function location_prepare( $venue ) {

		// Init return.
		$location = [];

		// Bail if Geo Mashup not installed.
		if ( ! $this->is_installed() ) {
			return $location;
		}

		// Convert whatever we can.
		$location['saved_name']  = isset( $venue->name ) ? $venue->name : '';
		$location['postal_code'] = isset( $venue->postcode ) ? $venue->postcode : '';

		// Fields to concatenate into "address" field.
		$concatenate = [ 'address', 'city', 'state', 'country', 'postcode' ];
		$address     = [];
		foreach ( $concatenate as $property ) {
			if ( ! empty( $venue->$property ) ) {
				$address[] = $venue->$property;
			}
		}

		// Build "address" field.
		if ( ! empty( $address ) ) {
			$location['address'] = implode( ', ', $address );
		}

		// We might have an address that's more than 255 chars.
		if ( strlen( $location['address'] ) > 254 ) {

			// Try without State.
			$concatenate = [ 'address', 'city', 'country', 'postcode' ];
			$address     = [];
			foreach ( $concatenate as $property ) {
				if ( ! empty( $venue->$property ) ) {
					$address[] = $venue->$property;
				}
			}

			// Build "address" field.
			if ( ! empty( $address ) ) {
				$location['address'] = implode( ', ', $address );
			}

		}

		// Apply latitude and longitude.
		$location['lat'] = isset( $venue->lat ) ? $venue->lat : '';
		$location['lng'] = isset( $venue->lng ) ? $venue->lng : '';

		// --<
		return $location;

	}

	/**
	 * Assigns the Geo Mashup Location to a given Event ID.
	 *
	 * @since 1.0
	 *
	 * @param int       $event_id The numeric ID of the Event.
	 * @param int|array $location The numeric ID of the Location or an array of Location data.
	 * @return int|bool $result The Location ID associated with the Event, false otherwise.
	 */
	public function location_save( $event_id, $location ) {

		// Bail if Geo Mashup not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Set geodate to now.
		$geo_date_obj = new DateTime( 'now', eo_get_blog_timezone() );
		$geo_date     = $geo_date_obj->format( 'Y-m-d H:i:s' );

		// Do we want to refresh the geodata?
		$do_lookups = null;

		// Store location for Event.
		$result = GeoMashupDB::set_object_location( 'post', $event_id, $location, $do_lookups, $geo_date );

		// Log and bail on error.
		if ( is_wp_error( $result ) ) {
			$e     = new Exception();
			$trace = $e->getTraceAsString();
			$data  = [
				'method'    => __METHOD__,
				'event_id'  => $event_id,
				'location'  => $location,
				'error'     => $result->get_error_message(),
				'backtrace' => $trace,
			];
			wpcv_eo_maps()->log_error( $data );
			return false;
		}

		// --<
		return $result;

	}

	/**
	 * Clears the Geo Mashup Location for a given Event ID.
	 *
	 * @since 1.0
	 *
	 * @param int $event_id The numeric ID of the Event.
	 * @return bool $result True on success, false otherwise.
	 */
	public function location_clear( $event_id ) {

		// Bail if Geo Mashup not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Delete location for post.
		$object_name = 'post';
		$object_ids  = [ $event_id ];

		// Returns int|WP_Error Rows affected or WordPress error.
		$result = GeoMashupDB::delete_object_location( $object_name, $object_ids );

		// Log and bail on error.
		if ( is_wp_error( $result ) ) {
			$e     = new Exception();
			$trace = $e->getTraceAsString();
			$data  = [
				'method'    => __METHOD__,
				'event_id'  => $event_id,
				'error'     => $result->get_error_message(),
				'backtrace' => $trace,
			];
			wpcv_eo_maps()->log_error( $data );
			return false;
		}

		// --<
		return true;

	}

}
