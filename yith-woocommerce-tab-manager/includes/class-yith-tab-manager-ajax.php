<?php
/**
 * This class manage all ajax call
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Ajax' ) ) {
	/**
	 * The class that manage the ajax calls
	 */
	class YITH_Tab_Manager_Ajax {
		use YITH_Tab_Manager_Trait_Singleton;

		/**
		 * The construct
		 */
		protected function __construct() {
			// ADMIN call.
			add_action( 'wp_ajax_ywtm_toggle_show_tab', array( $this, 'toggle_show_tab' ) );
			add_action( 'wp_ajax_ywtm_sort_tabs', array( $this, 'sort_tabs' ) );
		}

		/**
		 * Enable/Disable the tab
		 *
		 * @return void
		 */
		public function toggle_show_tab() {
			check_ajax_referer( 'toggle-show-tab', 'security' );

			if ( isset( $_REQUEST['tab_id'] ) ) {
				$tab_id = sanitize_text_field( wp_unslash( $_REQUEST['tab_id'] ) );
				$active = isset( $_REQUEST['enabled'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['enabled'] ) ) : 'no';
				try {
					$tab = ywtm_get_tab( $tab_id );
					$tab->set_active( $active );
					$tab->save();
					wp_send_json_success();
				} catch ( Exception $e ) {
					wp_send_json_error( $e->getMessage() );
				}
			}
		}

		/**
		 * Sort the tabs
		 *
		 * @return void
		 */
		public function sort_tabs() {
			check_ajax_referer( 'sort-tabs', 'security' );

			if ( isset( $_REQUEST['tabs_to_sort'] ) ) {
				$tabs_to_sort = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['tabs_to_sort'] ) ) );
				$old_priority = array();

				foreach ( $tabs_to_sort as $tab_id ) {
					$tab            = ywtm_get_tab( $tab_id );
					$old_priority[] = $tab->get_order();
				}
				asort( $old_priority );

				$sorted_priority = array_values( $old_priority );

				$i = 0;
				foreach ( $tabs_to_sort as $tab_id ) {
					$tab          = ywtm_get_tab( $tab_id );
					$new_priority = $sorted_priority[ $i ];
					$tab->set_order( $new_priority );
					$tab->save();
					++$i;
				}

				wp_send_json_success();
			}
		}
	}
}
