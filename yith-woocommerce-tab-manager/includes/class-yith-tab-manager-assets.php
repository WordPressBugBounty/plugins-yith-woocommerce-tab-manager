<?php
/**
 * Class that manage the assets
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Assets' ) ) {
	/**
	 * Register and enqueue the scripts
	 */
	class YITH_Tab_Manager_Assets {
		use YITH_Tab_Manager_Trait_Singleton;


		/**
		 * The construct
		 */
		protected function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 20 );
		}

		/**
		 * Register and enqueue the admin script
		 *
		 * @return void
		 */
		public function enqueue_admin_scripts() {

			wp_register_style( 'ywtm-admin', YWTM_ASSETS_URL . 'css/admin.css', array(), YWTM_VERSION );
			wp_register_script(
				'ywtm-admin',
				YWTM_ASSETS_URL . 'js/build/' . yit_load_js_file( 'admin.js' ),
				array(
					'jquery',
					'jquery-ui-sortable',
				),
				YWTM_VERSION,
				true
			);

			$this->localize_scripts( 'ywtm-admin' );
			$current_screen = get_current_screen();
			if ( $current_screen && 'ywtm_tab' === $current_screen->post_type ) {
				wp_enqueue_editor();
				wp_enqueue_style( 'ywtm-admin' );
				wp_enqueue_script( 'ywtm-admin' );
			}
		}

		/**
		 * Localize the scripts
		 *
		 * @param string $handle The handle.
		 *
		 * @return void
		 */
		protected function localize_scripts( $handle ) {
			$args = array(
				'ywtm-admin' => array(
					'ajax_url'      => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'actions'       => array(
						'toggle_show_tab' => 'ywtm_toggle_show_tab',
						'sort_tabs'       => 'ywtm_sort_tabs',
					),
					'nonces'        => array(
						'toggle_show_tab' => wp_create_nonce( 'toggle-show-tab' ),
						'sort_tabs'       => wp_create_nonce( 'sort-tabs' ),
						'update_preview'  => wp_create_nonce( 'update-preview' ),
					),
					'message_alert' => array(
						'title'         => __( 'Are you sure?', 'yith-woocommerce-tab-manager' ),
						'desc'          => __( 'Do you want to go back to the plugin dashboard? The changes made may not be saved.', 'yith-woocommerce-tab-manager' ),
						'confirmButton' => __( 'Yes, proceed without saving', 'yith-woocommerce-tab-manager' ),
					),
				),
			);

			if ( isset( $args[ $handle ] ) ) {
				$object_name = str_replace( '-', '_', $handle );
				wp_localize_script( $handle, "{$object_name}_args", $args[ $handle ] );
			}
		}
	}
}
