<?php
/**
 * This class show the custom tab in the product page
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Frontend' ) ) {
	/**
	 * The class that manage the tabs in frontend
	 */
	class YITH_Tab_Manager_Frontend {
		use YITH_Tab_Manager_Trait_Singleton;

		/**
		 * The construct
		 */
		protected function __construct() {
			add_filter( 'woocommerce_product_tabs', array( $this, 'add_tabs_woocommerce' ), 20 );
		}

		/**
		 * Add the custom tab
		 *
		 * @param array $tabs The WooCommerce Tabs.
		 *
		 * @return array
		 */
		public function add_tabs_woocommerce( $tabs ) {
			$wc_tabs_opt = get_option( 'yith_tab_manager_wc_added', array() );

			$wc_tabs = ywtm_get_tabs(
				array(
					'origin'           => 'woocommerce',
					'suppress_filters' => true,
				)
			);

			foreach ( $wc_tabs as $wc_tab ) {

				$key = array_search( $wc_tab->get_id(), $wc_tabs_opt, true );
				if ( isset( $tabs[ $key ] ) ) {
					$tabs[ $key ]['priority'] = $wc_tab->get_order();
				}
			}

			$custom_tabs = $this->add_custom_tabs();

			return array_merge( $tabs, $custom_tabs );
		}

		/**
		 * Add the global tabs
		 *
		 * @return array
		 */
		protected function add_custom_tabs() {
			global $product;
			$yith_tabs = ywtm_get_tabs(
				array(
					'type'   => 'global',
					'active' => 'yes',
					'origin' => 'plugin',
					'limit'  => - 1,
				)
			);

			$wc_tabs = array();
			foreach ( $yith_tabs as $tab ) {
				if ( $tab->can_show( $product ) ) {
					$wc_tabs[ 'ywtm-' . sanitize_title( $tab->get_name() ) ] = array(
						'title'    => $tab->get_name(),
						'priority' => $tab->get_order(),
						'callback' => array( $this, 'put_content_tabs' ),
						'yith_obj' => $tab,
					);
				}
			}

			return $wc_tabs;
		}

		/**
		 * Put the content at the tabs
		 *
		 * @param string $key The tab key.
		 * @param array  $tab_data The tab data.
		 *
		 * @since 2.0.0
		 */
		public function put_content_tabs( $key, $tab_data ) {

			if ( isset( $tab_data['yith_obj'] ) ) {
				$tab = $tab_data['yith_obj'];
				echo $tab->show_tab_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
}
