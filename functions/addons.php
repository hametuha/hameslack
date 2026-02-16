<?php
/**
 * Addon system functions
 *
 * @package hameslack
 * @since 3.0.0
 */

defined( 'ABSPATH' ) or die();

/**
 * Get all registered addons.
 *
 * Each addon should be an associative array with keys:
 * - id:          string Unique identifier (e.g. 'pending-review-notify')
 * - label:       string Human-readable name
 * - description: string Short description
 *
 * @since 3.0.0
 * @return array[] List of addon definitions.
 */
function hameslack_get_addons() {
	/**
	 * Filter registered addons.
	 *
	 * @since 3.0.0
	 * @param array[] $addons List of addon definitions.
	 * @return array[]
	 */
	return apply_filters( 'hameslack_addons', [] );
}

/**
 * Check if an addon is active.
 *
 * @since 3.0.0
 * @param string $id Addon identifier.
 * @return bool
 */
function hameslack_is_addon_active( $id ) {
	$active = (array) get_option( 'hameslack_active_addons', [] );
	return ! empty( $active[ $id ] );
}

/**
 * Get all active addons as associative array.
 *
 * @since 3.0.0
 * @return array[] Keyed by addon ID, value is addon definition.
 */
function hameslack_get_active_addons() {
	$all    = hameslack_get_addons();
	$active = [];
	foreach ( $all as $addon ) {
		if ( hameslack_is_addon_active( $addon['id'] ) ) {
			$active[ $addon['id'] ] = $addon;
		}
	}
	return $active;
}

/**
 * Sanitize active addons option value.
 *
 * Removes unknown IDs and placeholder key.
 *
 * @since 3.0.0
 * @param mixed $value Raw option value.
 * @return array Sanitized associative array of active addon IDs.
 */
function hameslack_sanitize_active_addons( $value ) {
	if ( ! is_array( $value ) ) {
		return [];
	}
	// Remove placeholder used to ensure form submission.
	unset( $value['__placeholder'] );
	// Only keep known addon IDs.
	$known_ids = array_map( function ( $addon ) {
		return $addon['id'];
	}, hameslack_get_addons() );
	return array_intersect_key( $value, array_flip( $known_ids ) );
}
