<?php
/**
 * Geo Base class.
 *
 * Base class for implementing Geo Location functionality.
 *
 * It is also a fallback class for when no Geo Location plugin is present.
 *
 * @package WPCV_EO_Maps
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Geo Base class.
 *
 * @since 1.0
 */
class WPCV_EO_Maps_Geo_Base {

	/**
	 * Geo plugin installed flag.
	 *
	 * @since 1.0
	 * @access public
	 * @var bool $is_installed True if Geo plugin is present, false otherwise.
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
	 * Checks if Geo plugin is installed.
	 *
	 * @since 1.0
	 */
	public function is_installed() {
		return false;
	}

	/**
	 * Initialise this object.
	 *
	 * @since 1.0
	 */
	public function initialise() {

		// Bail if Geo plugin not installed.
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
	 * Creates a Geo Location with a given set of data.
	 *
	 * @since 1.0
	 *
	 * @param array $location The Location data.
	 * @param bool  $do_lookups Pass true to refresh Location geodata.
	 * @return int|bool $result The new Location ID, or false on failure.
	 */
	public function create( $location, $do_lookups = false ) {

		// Bail if Geo plugin not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Returns false.
		$result = false;

		// --<
		return $result;

	}

	/**
	 * Updates a Geo Location with a given set of data.
	 *
	 * @since 1.0
	 *
	 * @param array $location The Location data.
	 * @param bool  $do_lookups Pass true to refresh Location geodata.
	 * @return int|bool $location_id The Location ID, or false on failure.
	 */
	public function update( $location, $do_lookups = false ) {

		// Bail if Geo plugin not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Pass through.
		return $this->create( $location, $do_lookups );

	}

	/**
	 * Deletes a Geo Location for a given Location or Location ID.
	 *
	 * @since 1.0
	 *
	 * @param int|array $location The numeric ID of the Location, or a Location array.
	 * @return int|bool $result The number of rows affected, or false on failure.
	 */
	public function delete( $location ) {

		// Bail if Geo plugin not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Returns false.
		$result = false;

		// --<
		return $result;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Geo Location for a given Location ID.
	 *
	 * @since 1.0
	 *
	 * @param int $location_id The numeric ID of the Location.
	 * @return object|bool $location The Location data object, or false if not found.
	 */
	public function location_get_by_id( $location_id ) {

		// Bail if Geo plugin not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Returns false.
		$location = false;

		// --<
		return $location;

	}

	/**
	 * Gets the Geo Location for a given Event ID.
	 *
	 * @since 1.0
	 *
	 * @param int $event_id The numeric ID of the Event.
	 * @return object|bool $location The Location data object, or false if not found.
	 */
	public function location_get_by_event_id( $event_id ) {

		// Bail if Geo plugin not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Returns false.
		$location = false;

		// --<
		return $location;

	}

	/**
	 * Prepares the Geo Location for a given Venue.
	 *
	 * @since 1.0
	 *
	 * @param object $venue The Venue object.
	 * @return array $location The array of Location data.
	 */
	public function location_prepare( $venue ) {

		// Init return.
		$location = [];

		// Bail if Geo plugin not installed.
		if ( ! $this->is_installed() ) {
			return $location;
		}

		// --<
		return $location;

	}

	/**
	 * Assigns the Geo Location to a given Event ID.
	 *
	 * @since 1.0
	 *
	 * @param int       $event_id The numeric ID of the Event.
	 * @param int|array $location The numeric ID of the Location or an array of Location data.
	 * @return int|bool $result The Location ID associated with the Event, false otherwise.
	 */
	public function location_save( $event_id, $location ) {

		// Bail if Geo plugin not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// Returns false.
		$result = false;

		// --<
		return $result;

	}

	/**
	 * Clears the Geo Location for a given Event ID.
	 *
	 * @since 1.0
	 *
	 * @param int $event_id The numeric ID of the Event.
	 * @return bool $result True on success, false otherwise.
	 */
	public function location_clear( $event_id ) {

		// Bail if Geo plugin not installed.
		if ( ! $this->is_installed() ) {
			return false;
		}

		// --<
		return true;

	}

}
