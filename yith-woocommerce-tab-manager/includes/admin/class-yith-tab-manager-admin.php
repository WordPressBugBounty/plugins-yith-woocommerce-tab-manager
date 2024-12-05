<?php
/**
 * This class manage all admin feature
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Admin' ) ) {
	/**
	 * The class that manage the admin area
	 */
	class YITH_Tab_Manager_Admin {
		use YITH_Tab_Manager_Trait_Singleton;


		/**
		 * The plugin panel name
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_wc_tab_manager_panel';

		/**
		 * The name of premium tab
		 *
		 * @var string
		 */
		protected $premium = 'premium.php';

		/**
		 * The plugin panel obj
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $panel = null;

		/**
		 * The construct
		 */
		protected function __construct() {
			$base_name = plugin_basename( YWTM_DIR . '/' . basename( YWTM_FILE ) );
			add_filter( 'plugin_action_links_' . $base_name, array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 5 );
			add_filter( 'yith_plugin_fw_get_field_template_path', array( $this, 'add_custom_types' ), 10, 2 );
			$this->add_post_type_admin();
		}


		/**
		 * Action Links, add the action links to plugin admin page
		 *
		 * @param array $links | links plugin array.
		 *
		 * @return   array
		 * @since    1.0
		 * @use plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$is_premium = defined( 'YWTM_PREMIUM' );
			$links      = yith_add_action_links( $links, $this->panel_page, $is_premium );

			return $links;
		}

		/**
		 * Add the action links to plugin admin page.
		 *
		 * @param array  $new_row_meta_args Plugin Meta New args.
		 * @param array  $plugin_meta Plugin Meta.
		 * @param string $plugin_file Plugin file.
		 * @param array  $plugin_data Plugin data.
		 * @param string $status Status.
		 * @param string $init_file Init file.
		 *
		 * @return   Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWTM_FREE_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug']       = YWTM_SLUG;
				$new_row_meta_args['is_premium'] = false;
			}

			return $new_row_meta_args;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function add_menu_page() {
			if ( ! empty( $this->panel ) ) {
				return;
			}

			$admin_tabs = array(
				'settings' => array(
					'title'       => __( 'Tab Manager', 'yith-woocommerce-tab-manager' ),
					'icon'        => '<svg dataSlot="icon" fill="none" strokeWidth={1.5} stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z" />
</svg>',
					'description' => __( 'Create and manage custom tabs to display on your product pages.', 'yith-woocommerce-tab-manager' ),
				),
			);

			$args = array(
				'ui_version'       => 2,
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => 'YITH WooCommerce Tab Manager',
				'menu_title'       => 'Tab Manager',
				'capability'       => 'manage_options',
				'plugin_slug'      => YWTM_SLUG,
				'parent'           => '',
				'class'            => yith_set_wrapper_class(),
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YWTM_DIR . '/plugin-options',
				'is_premium'       => defined( 'YWTM_PREMIUM' ),
				'help_tab'         => array(),
			);

			$premium_tab_path = YWTM_TEMPLATE_PATH . '/admin/' . $this->premium;
			if ( ! defined( 'YWTM_PREMIUM' ) && file_exists( $premium_tab_path ) ) {
				$premium             = include_once $premium_tab_path;
				$args['premium_tab'] = array(
					'features' => $premium,
				);
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Include the Post type admin class
		 *
		 * @return void
		 */
		public function add_post_type_admin() {
			YITH_Tab_Manager_Post_Type_Admin::instance();
		}

		/**
		 * Add custom type in meta-box
		 *
		 * @param string $field_path The field path.
		 * @param array  $field The field.
		 *
		 * @return string
		 */
		public function add_custom_types( $field_path, $field ) {

			$custom_types = array(
				'ywtm-modal-editor',
			);

			if ( in_array( $field['type'], $custom_types, true ) ) {
				$field_name = str_replace( 'ywtm-', '', $field['type'] );
				$field_path = YWTM_DIR . '/views/meta-boxes/types/' . $field_name . '.php';
			}

			return $field_path;
		}
	}


}
