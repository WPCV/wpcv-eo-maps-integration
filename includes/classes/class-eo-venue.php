<?php
/**
 * Event Organiser class.
 *
 * Manages Event Organiser functionality.
 *
 * @package WPCV_EO_Maps
 * @since 1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Event Organiser class.
 *
 * @since 1.0
 */
class WPCV_EO_Maps_EO_Venue {

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

		// Filter Terms after Event Organiser does.
		add_filter( 'wp_get_object_terms', [ $this, 'venue_meta_update' ], 20, 4 );

		// Update Terms in cache after Event Organiser does.
		add_filter( 'get_terms', [ $this, 'venue_meta_cache_update' ], 20, 2 );
		add_filter( 'get_event-venue', [ $this, 'venue_meta_cache_update' ], 20, 2 );

	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0
	 */
	public function register_mapper_hooks() {

		// Add Event callbacks.
		add_action( 'eventorganiser_save_event', [ $this, 'event_saved' ], 10 );

		// Add Venue callbacks.
		add_action( 'eventorganiser_insert_venue', [ $this, 'venue_created' ], 10 );
		add_action( 'eventorganiser_save_venue', [ $this, 'venue_edited' ], 10 );
		// Add Term deletion callbacks (before "Venue deleted").
		add_action( 'delete_term', [ $this, 'venue_term_pre_delete' ], 20, 4 );
		add_action( 'delete_event-venue', [ $this, 'venue_term_tax_pre_delete' ], 20, 3 );
		// Add "Venue deleted" callback.
		add_action( 'eventorganiser_venue_deleted', [ $this, 'venue_deleted' ], 10 );

	}

	/**
	 * Unregister hooks.
	 *
	 * @since 1.0
	 */
	public function unregister_mapper_hooks() {

		// Remove all callbacks.
		remove_action( 'eventorganiser_save_event', [ $this, 'event_saved' ], 10 );
		remove_action( 'eventorganiser_insert_venue', [ $this, 'venue_created' ], 10 );
		remove_action( 'eventorganiser_save_venue', [ $this, 'venue_edited' ], 10 );
		remove_action( 'delete_term', [ $this, 'venue_term_pre_delete' ], 20 );
		remove_action( 'delete_event-venue', [ $this, 'venue_term_tax_pre_delete' ], 20 );
		remove_action( 'eventorganiser_venue_deleted', [ $this, 'venue_deleted' ], 10 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Intercept "Save Event".
	 *
	 * @since 1.0
	 *
	 * @param int $event_id The numeric ID of the Event Organiser Event.
	 */
	public function event_saved( $event_id ) {

		// Get Venue for this Event.
		$venue_id = eo_get_venue( $event_id );

		// Clear Post Location and bail when empty.
		if ( empty( $venue_id ) ) {
			wpcv_eo_maps()->geo->location_clear( $event_id );
			return;
		}

		// Get the correspondence.
		$location_id = wpcv_eo_maps()->mapping->location_id_get_by_venue_id( $venue_id );

		// Assign Post Location and bail.
		if ( ! empty( $location_id ) ) {
			$result = wpcv_eo_maps()->geo->location_save( $event_id, $location_id );
			return;
		}

		// Get the full Venue data.
		$venue = $this->venue_get_by_id( $venue_id );
		if ( empty( $venue ) ) {
			return;
		}

		// Convert Venue data to Location data.
		$location = wpcv_eo_maps()->geo->location_prepare( $venue );

		// Update the Post Location.
		$location_id = wpcv_eo_maps()->geo->location_save( $event_id, $location );
		if ( empty( $location_id ) ) {
			return;
		}

		// Save the correspondence.
		wpcv_eo_maps()->mapping->location_id_save( $venue_id, $location_id );

	}

	// -------------------------------------------------------------------------

	/**
	 * Intercept insert Venue.
	 *
	 * @since 1.0
	 *
	 * @param int $venue_id The numeric ID of the Venue.
	 */
	public function venue_created( $venue_id ) {

		// Get the full Venue data.
		$venue = $this->venue_get_by_id( $venue_id );
		if ( empty( $venue ) ) {
			return;
		}

		// Convert Venue data to Location data.
		$location = wpcv_eo_maps()->geo->location_prepare( $venue );

		// Create a Geo Mashup Location.
		$location_id = wpcv_eo_maps()->geo->create( $location, true );
		if ( empty( $location_id ) ) {
			return;
		}

		// Save the correspondence.
		wpcv_eo_maps()->mapping->location_id_save( $venue_id, $location_id );

	}

	/**
	 * Intercept save Venue.
	 *
	 * @since 1.0
	 *
	 * @param int $venue_id The numeric ID of the Venue.
	 */
	public function venue_edited( $venue_id ) {

		// Get the full Venue data.
		$venue = $this->venue_get_by_id( $venue_id );
		if ( empty( $venue ) ) {
			return;
		}

		// Convert Venue data to Location data.
		$location = wpcv_eo_maps()->geo->location_prepare( $venue );

		// Get the correspondence.
		$existing_location_id = wpcv_eo_maps()->mapping->location_id_get_by_venue_id( $venue_id );

		// Assign Location ID if one exists.
		if ( ! empty( $existing_location_id ) ) {
			$location['id'] = $existing_location_id;
		}

		// Create or update the Geo Mashup Location.
		if ( empty( $existing_location_id ) ) {
			$location_id = wpcv_eo_maps()->geo->create( $location, true );
		} else {
			$location_id = wpcv_eo_maps()->geo->update( $location, true );
		}

		// Maybe save the correspondence.
		if ( empty( $existing_location_id ) ) {
			wpcv_eo_maps()->mapping->location_id_save( $venue_id, $location_id );
		}

	}

	/**
	 * Intercept before a Venue Term is deleted.
	 *
	 * @since 1.0
	 *
	 * @param object $term The Term object of the Venue.
	 * @param int    $tt_id The numeric ID of the Venue Term Taxonomy.
	 * @param string $taxonomy The deleted Term's Taxonomy name.
	 * @param object $deleted_term The deleted Term object of the Venue.
	 */
	public function venue_term_pre_delete( $term, $tt_id, $taxonomy, $deleted_term ) {

		// Sanity checks.
		if ( ! is_object( $deleted_term ) ) {
			return;
		}
		if ( is_array( $taxonomy ) && ! in_array( 'event-venue', $taxonomy, true ) ) {
			return;
		}
		if ( ! is_array( $taxonomy ) && 'event-venue' !== $taxonomy ) {
			return;
		}

		// Delete anything associated with this Venue.
		$this->venue_pre_delete( $deleted_term );

	}

	/**
	 * Intercept before delete Venue Term by 'delete_$taxonomy' hook.
	 *
	 * @since 1.0
	 *
	 * @param object $term The Term object of the Venue.
	 * @param int    $tt_id The numeric ID of the Venue Term Taxonomy.
	 * @param object $deleted_term The deleted Term object of the Venue.
	 */
	public function venue_term_tax_pre_delete( $term, $tt_id, $deleted_term ) {

		// Sanity check.
		if ( ! is_object( $deleted_term ) ) {
			return;
		}

		// Delete anything associated with this Venue.
		$this->venue_pre_delete( $deleted_term );

	}

	/**
	 * Delete anything associated with this Venue.
	 *
	 * @since 1.0
	 *
	 * @param object $deleted_term The Term object of the Venue.
	 */
	private function venue_pre_delete( $deleted_term ) {

		// Only do this once.
		static $term_deleted;
		if ( isset( $term_deleted ) && $term_deleted === $deleted_term->term_id ) {
			return;
		}

		// Get Venue ID.
		$venue_id = $deleted_term->term_id;

		// Get the correspondence.
		$location_id = wpcv_eo_maps()->mapping->location_id_get_by_venue_id( $venue_id );

		// If we get a Location ID.
		if ( ! empty( $location_id ) ) {

			// Delete the Location.
			wpcv_eo_maps()->geo->delete( $location_id );

			// Clear correspondence.
			wpcv_eo_maps()->mapping->location_id_clear( $venue_id, $location_id );

		}

		// Set static variable.
		$term_deleted = $deleted_term->term_id;

	}

	/**
	 * Intercept after a Venue has been deleted.
	 *
	 * Event Organiser garbage-collects so nothing to do here for the moment.
	 *
	 * @since 1.0
	 *
	 * @param int $venue_id The numeric ID of the Venue.
	 */
	public function venue_deleted( $venue_id ) {

		/*
		// Check permissions.
		if ( ! $this->venue_edit_allowed() ) {
			return;
		}
		*/

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the full Venue data for a given ID.
	 *
	 * @since 1.0
	 *
	 * @param int $venue_id The numeric ID of the Venue.
	 * @return object $venue The numeric ID of the Venue.
	 */
	public function venue_get_by_id( $venue_id ) {

		// Get Venue data as Term object.
		$venue = eo_get_venue_by( 'id', $venue_id );
		if ( empty( $venue ) ) {
			return false;
		}

		/*
		 * Manually add Venue metadata because since Event Organiser 3.0 it is
		 * no longer added by default to the Venue object.
		 */
		$address         = eo_get_venue_address( $venue_id );
		$venue->address  = isset( $address['address'] ) ? $address['address'] : '';
		$venue->postcode = isset( $address['postcode'] ) ? $address['postcode'] : '';
		$venue->city     = isset( $address['city'] ) ? $address['city'] : '';
		$venue->country  = isset( $address['country'] ) ? $address['country'] : '';
		$venue->state    = isset( $address['state'] ) ? $address['state'] : '';

		// Add geolocation data.
		$venue->lat = number_format( floatval( eo_get_venue_lat( $venue_id ) ), 6 );
		$venue->lng = number_format( floatval( eo_get_venue_lng( $venue_id ) ), 6 );

		// --<
		return $venue;

	}

	// -------------------------------------------------------------------------

	/**
	 * Updates Venue meta cache when an Event's Venue is retrieved.
	 *
	 * @since 1.0
	 *
	 * @param array  $terms Array of Terms.
	 * @param array  $post_ids Array of Post IDs.
	 * @param string $taxonomies Should be (an array containing) 'event-venue'.
	 * @param string $args Additional parameters.
	 * @return array $terms Array of Term objects.
	 */
	public function venue_meta_update( $terms, $post_ids, $taxonomies, $args ) {

		// Passes Taxonomies as a string inside quotes.
		$taxonomies = explode( ',', trim( $taxonomies, "\x22\x27" ) );
		return $this->venue_meta_cache_update( $terms, $taxonomies );

	}

	/**
	 * Updates Venue meta cache when Event Venues are retrieved.
	 *
	 * @since 1.0
	 *
	 * @param array  $terms Array of Terms.
	 * @param string $tax Should be (an array containing) 'event-venue'.
	 * @return array $terms Array of event-venue Terms.
	 */
	public function venue_meta_cache_update( $terms, $tax ) {

		if ( is_array( $tax ) && ! in_array( 'event-venue', $tax, true ) ) {
			return $terms;
		}
		if ( ! is_array( $tax ) && 'event-venue' !== $tax ) {
			return $terms;
		}

		$single = false;
		if ( ! is_array( $terms ) ) {
			$single = true;
			$terms  = [ $terms ];
		}

		if ( empty( $terms ) ) {
			return $terms;
		}

		// Check if its array of Terms or Term IDs.
		$first_element = reset( $terms );
		if ( is_object( $first_element ) ) {
			$term_ids = wp_list_pluck( $terms, 'term_id' );
		} else {
			$term_ids = $terms;
		}

		update_meta_cache( 'eo_venue', $term_ids );

		// Loop through.
		foreach ( $terms as $term ) {

			// Skip if not useful.
			if ( ! is_object( $term ) ) {
				continue;
			}

			// Get Term ID.
			$term_id = (int) $term->term_id;

			if ( ! isset( $term->venue_geo_id ) ) {
				$term->venue_geo_id = eo_get_venue_meta( $term_id, wpcv_eo_maps()->mapping->meta_key, true );
			}

		}

		if ( $single ) {
			return $terms[0];
		}

		// --<
		return $terms;

	}

	// -------------------------------------------------------------------------

	/**
	 * Check current user's permission to edit the Venue Taxonomy.
	 *
	 * @since 1.0
	 *
	 * @return bool $allowed True if allowed, false otherwise.
	 */
	public function venue_edit_allowed() {

		// Get permission.
		$tax     = get_taxonomy( 'event-venue' );
		$allowed = current_user_can( $tax->cap->edit_terms );

		// --<
		return $allowed;

	}

}
