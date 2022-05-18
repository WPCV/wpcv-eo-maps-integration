<?php
/**
 * Mapping class.
 *
 * Manages mapping between Event Organiser and Mapping plugins.
 *
 * Event Organiser provides metadata functionality for Venues, so we use that to
 * store the ID of a Location. This allows us to maintain sync between Venues
 * and Locations.
 *
 * @package WPCV_EO_Maps
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Mapping class.
 *
 * @since 1.0
 */
class WPCV_EO_Maps_Mapping {

	/**
	 * Event Organiser Venue meta key whose value is the Location ID.
	 *
	 * @since 1.0
	 * @access public
	 * @var string $meta_key The Venue meta key.
	 */
	public $meta_key = '_geo_id';

	/**
	 * The key for caching Venue ID queries.
	 *
	 * @since 1.0
	 * @access public
	 * @var string $cache_key The key for caching Venue ID queries.
	 */
	public $cache_key = 'wpcv_eo_maps_venue_id_';

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
	 * Initialise this object.
	 *
	 * @since 1.0
	 */
	public function initialise() {

	}

	/**
	 * Get an Event Organiser Venue ID given a Location.
	 *
	 * @since 1.0
	 *
	 * @param int   $location_id The numeric ID of the Location.
	 * @param float $latitude The latitude of the Location.
	 * @param float $longitude The longitude of the Location.
	 * @return int|bool $venue_id The numeric ID of the Venue, or false if not found.
	 */
	public function venue_id_get_by_location_id( $location_id, $latitude = null, $longitude = null ) {

		// Check cache first.
		$venue_id = wp_cache_get( $this->cache_key . $location_id );
		if ( false !== $venue_id ) {
			return $venue_id;
		}

		// We should have a matching ID in the Venue's meta table.

		// Access db.
		global $wpdb;

		// To avoid the pro plugin, query the database directly.
		$sql = $wpdb->prepare(
			"SELECT eo_venue_id FROM $wpdb->eo_venuemeta WHERE
			meta_key = %s AND
			meta_value = %d",
			$this->meta_key,
			$location_id
		);

		// This should return a value.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$venue_id = $wpdb->get_var( $sql );

		// If we get one, cache and return it.
		if ( ! empty( $venue_id ) ) {
			wp_cache_set( $this->cache_key . $location_id, $venue_id );
			return (int) $venue_id;
		}

		// As a fallback, check if we have an identical Location.

		// Do we have geo data?
		if ( ! empty( $latitude ) && ! empty( $longitude ) ) {

			/*
			 * To avoid the pro plugin, query the database directly.
			 * This could use some refinement from someone better at SQL than me.
			 */
			$sql = $wpdb->prepare(
				"SELECT eo_venue_id FROM $wpdb->eo_venuemeta
				WHERE
					( meta_key = '_lat' AND meta_value = %f )
				AND
				eo_venue_id = (
					SELECT eo_venue_id FROM $wpdb->eo_venuemeta WHERE
					( meta_key = '_lng' AND meta_value = %f )
				)",
				floatval( $latitude ),
				floatval( $longitude )
			);

			// This should return a value.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			$venue_id = $wpdb->get_var( $sql );

			// If we get one, cache and return it.
			if ( ! empty( $venue_id ) ) {
				wp_cache_set( $this->cache_key . $location_id, $venue_id );
				return (int) $venue_id;
			}

		}

		// --<
		return false;

	}

	/**
	 * Gets the Location ID for a given Event Organiser Venue ID.
	 *
	 * @since 1.0
	 *
	 * @param int $venue_id The numeric ID of the Venue.
	 * @return int $location_id The numeric ID of the Location.
	 */
	public function location_id_get_by_venue_id( $venue_id ) {

		// Get Venue meta data.
		$location_id = eo_get_venue_meta( $venue_id, $this->meta_key, true );

		// --<
		return (int) $location_id;

	}

	/**
	 * Saves the Location ID for a given Event Organiser Venue ID.
	 *
	 * @since 1.0
	 *
	 * @param int $venue_id The numeric ID of the Venue.
	 * @param int $location_id The numeric ID of the Location.
	 */
	public function location_id_save( $venue_id, $location_id ) {

		// Update Venue meta.
		eo_update_venue_meta( $venue_id, $this->meta_key, (int) $location_id );

		// Bust the cache.
		wp_cache_delete( $this->cache_key . $location_id );

	}

	/**
	 * Clears the Location ID for a given Event Organiser Venue ID.
	 *
	 * @since 1.0
	 *
	 * @param int $venue_id The numeric ID of the Venue.
	 * @param int $location_id The numeric ID of the Location.
	 */
	public function location_id_clear( $venue_id, $location_id = null ) {

		// Delete Venue meta.
		eo_delete_venue_meta( $venue_id, $this->meta_key );

		// Can't bust the cache if there's no Location ID.
		if ( empty( $location_id ) ) {
			return;
		}

		// Okay, bust the cache.
		wp_cache_delete( $this->cache_key . $location_id );

	}

}
