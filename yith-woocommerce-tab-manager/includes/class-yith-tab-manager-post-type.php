<?php
/**
 * The class that manage the post type
 *
 * @package YITH/TabManager\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Tab_Manager_Post_Type' ) ) {
	/**
	 * The class for tab manager post type
	 */
	class YITH_Tab_Manager_Post_Type {
		use YITH_Tab_Manager_Trait_Singleton;

		/**
		 * Post type name
		 *
		 * @var string
		 */
		public $post_type_name = 'ywtm_tab';

		/**
		 * The construct of the class
		 *
		 * @author YITH <plugins@yithemes.com>
		 * @since 2.0.0
		 */
		protected function __construct() {

			// Add action register post type.
			add_action( 'init', array( $this, 'tabs_post_type' ), 10 );
		}


		/**
		 * Register a Global Tab post type
		 *
		 * @since 1.0.0
		 */
		public function tabs_post_type() {
			$args = apply_filters(
				'yith_wctm_post_type',
				array(
					'label'               => __( 'ywtm_tab', 'yith-woocommerce-tab-manager' ),
					'description'         => __( 'Yith Tab Manager Description', 'yith-woocommerce-tab-manager' ),
					'labels'              => $this->get_tab_taxonomy_label(),
					'supports'            => array( 'title' ),
					'hierarchical'        => false,
					'public'              => false,
					'show_ui'             => true,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => false,
					'show_in_admin_bar'   => false,
					'can_export'          => false,
					'has_archive'         => false,
					'exclude_from_search' => true,
					'publicly_queryable'  => false,
					'capability_type'     => 'post',
				)
			);

			register_post_type( $this->post_type_name, $args );
		}

		/**
		 * Get the tab taxonomy label
		 *
		 * @param string $arg The string to return. Defaul empty. If is empty return all taxonomy labels.
		 *
		 * @return string|array taxonomy label
		 * @fire yith_tab_manager_taxonomy_label hooks
		 * @since  1.0.0
		 */
		protected function get_tab_taxonomy_label( $arg = '' ) {

			$label = apply_filters(
				'yith_tab_manager_taxonomy_label',
				array(
					'name'               => _x( 'Tab Manager', 'Post Type General Name', 'yith-woocommerce-tab-manager' ),
					'singular_name'      => _x( 'Tab', 'Post Type Singular Name', 'yith-woocommerce-tab-manager' ),
					'menu_name'          => __( 'Tab Manager', 'yith-woocommerce-tab-manager' ),
					'parent_item_colon'  => __( 'Parent Item:', 'yith-woocommerce-tab-manager' ),
					'all_items'          => __( 'All Tabs', 'yith-woocommerce-tab-manager' ),
					'view_item'          => __( 'View Tabs', 'yith-woocommerce-tab-manager' ),
					'add_new_item'       => __( 'Add New Tab', 'yith-woocommerce-tab-manager' ),
					'add_new'            => __( 'Add New Tab', 'yith-woocommerce-tab-manager' ),
					'edit_item'          => __( 'Edit Tab', 'yith-woocommerce-tab-manager' ),
					'update_item'        => __( 'Update Tab', 'yith-woocommerce-tab-manager' ),
					'search_items'       => __( 'Search Tab', 'yith-woocommerce-tab-manager' ),
					'not_found'          => __( 'Not found', 'yith-woocommerce-tab-manager' ),
					'not_found_in_trash' => __( 'Not found in Trash', 'yith-woocommerce-tab-manager' ),
				)
			);

			return ! empty( $arg ) ? $label[ $arg ] : $label;
		}



		/**
		 * Get all custom tab.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		public function get_tabs() {

			/*Custom query for gets all post 'Tab'*/

			$args = array(
				'post_type'        => 'ywtm_tab',
				'post_status'      => 'publish',
				'posts_per_page'   => - 1,
				'suppress_filters' => false,
				'meta_query'       => array(
					array(
						'key'   => '_ywtm_show_tab',
						'value' => true,
					),
				),

			);

			if ( function_exists( 'pll_current_language' ) ) {
				$args['lang'] = pll_current_language();
			}
			$q_tabs = get_posts( $args );
			$tabs   = array();

			foreach ( $q_tabs as $tab ) {

				$attr_tab                                  = array();
				$attr_tab['title']                         = $tab->post_title;
				$attr_tab['priority']                      = get_post_meta( $tab->ID, '_ywtm_order_tab', true );
				$attr_tab['id']                            = $tab->ID;
				$tabs[ $tab->post_title . '_' . $tab->ID ] = $attr_tab;

			}

			return $tabs;
		}
	}

}
