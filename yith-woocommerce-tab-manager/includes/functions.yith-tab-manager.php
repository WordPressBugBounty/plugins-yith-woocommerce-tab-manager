<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * This file manage all plugin functions
 *
 * @package YITH/TabManager/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ywtm_get_tab' ) ) {

	/**
	 * Get the tab object
	 *
	 * @param int|string|WP_Post|YITH_Tab_Manager_Obj $tab The tab to retrieve.
	 *
	 * @throws Exception The error.
	 */
	function ywtm_get_tab( $tab = 0 ) {
		return new YITH_Tab_Manager_Obj( $tab );
	}
}

if ( ! function_exists( 'ywtm_get_tabs' ) ) {
	/**
	 * Get tabs by query args
	 *
	 * @param array $args The query args.
	 *
	 * @return false|int[]|YITH_Tab_Manager_Obj[]
	 * @throws Exception The error.
	 */
	function ywtm_get_tabs( $args = array() ) {
		$data_store = WC_Data_Store::load( 'ywtm-data-store' );

		if ( $data_store ) {
			return $data_store->query( $args );
		}

		return false;
	}
}

if ( ! function_exists( 'ywtm_get_default_priority' ) ) {
	/**
	 * Get the max priority that can be set for a new tab
	 *
	 * @return int
	 */
	function ywtm_get_default_priority() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare(
			"SELECT MAX( CAST( meta_value AS UNSIGNED) ) AS priority 
				   FROM {$wpdb->postmeta}
				   WHERE {$wpdb->postmeta}.meta_key = %s AND {$wpdb->postmeta}.post_id IN (
				       SELECT ID FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_type = %s AND post_status = 'publish'
				   )",
			'_ywtm_order_tab',
			'ywtm_tab'
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_var( $query );


		return ! empty( $result ) ? intval( $result ) + 1 : 1;
	}
}
