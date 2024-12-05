<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * This file manage all plugins updates
 *
 * @package YITH\TabManager/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'yith_tab_manager_update_200_porting_global_tabs' ) ) {
	/**
	 * Porting the old free tabs , update the priority with new logic
	 *
	 * @return bool
	 */
	function yith_tab_manager_update_200_porting_global_tabs() {

		$tabs = get_posts(
			array(
				'post_type'  => 'ywtm_tab',
				'limit'      => 20,
				'fields'     => 'ids',
				'meta_key'   => '_ywtm_order_tab',
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
				'meta_query' => array(
					array(
						'key'     => '_ywtm_tab_ported',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_ywtm_is_editable',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		if ( ! $tabs ) {
			delete_option( 'ywtm_porting_200_counter' );

			return false;
		}

		$counter = get_option( 'ywtm_porting_200_counter', 4 );
		$backup  = get_option( 'ywtm_porting_200_backup', array() );
		foreach ( $tabs as $tab_id ) {
			$backup[ $tab_id ] = get_post_meta( $tab_id );
			$show_tab          = get_post_meta( $tab_id, '_ywtm_show_tab', true );
			$show_tab          = wc_bool_to_string( $show_tab );
			update_post_meta( $tab_id, '_ywtm_order_tab', $counter );
			update_post_meta( $tab_id, '_ywtm_show_tab', $show_tab );
			update_post_meta( $tab_id, '_ywtm_tab_title', get_the_title( $tab_id ) );
			update_post_meta( $tab_id, '_ywtm_tab_ported', 'yes' );
			update_post_meta( $tab_id, '_ywtm_is_editable', 'yes' );
			update_post_meta( $tab_id, '_ywtm_origin', 'plugin' );
			update_post_meta( $tab_id, '_ywtm_tab_content_in', 'all' );
			++$counter;
		}
		update_option( 'ywtm_porting_200_backup', $backup );
		update_option( 'ywtm_porting_200_counter', $counter );

		return true;
	}
}

if ( ! function_exists( 'yith_tab_manager_update_200_db_version' ) ) {
	/**
	 * Update the db version
	 *
	 * @return void
	 */
	function yith_tab_manager_update_200_db_version() {
		YITH_Tab_Manager_Install::update_db_version( '2.0.0' );
	}
}
