<?php
/**
 * This is the data store for tab manager
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Data_Store' ) ) {
	/**
	 * Add the data store
	 */
	class YITH_Tab_Manager_Data_Store extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {
		/**
		 * Map meta key and props
		 *
		 * @var string[]
		 */
		protected static $meta_key_to_props = array(
			'_ywtm_tab_title'   => 'name',
			'_ywtm_tab_type'    => 'type',
			'_ywtm_show_tab'    => 'active',
			'_ywtm_order_tab'   => 'order',
			'_ywtm_icon_tab'    => 'icon',
			'_ywtm_layout_type' => 'layout',
			'_ywtm_text_tab'    => 'content',
			'_ywtm_is_editable' => 'is_editable',
			'_ywtm_origin'      => 'origin',
		);


		/**
		 * Data stored in meta keys, but not considered "meta".
		 *
		 * @var array
		 */
		protected $internal_meta_keys = array(
			'_ywtm_tab_title',
			'_ywtm_show_tab',
		);

		/**
		 * Create a new tab
		 *
		 * @param YITH_Tab_Manager_Obj $tab The tab to create.
		 *
		 * @return void
		 */
		public function create( &$tab ) {

			$tab_id = wp_insert_post(
				array(
					'post_type'   => 'ywtm_tab',
					'post_status' => 'publish',
					'post_author' => get_current_user_id(),
					'post_title'  => $tab->get_name() ? $tab->get_name() : _x( 'Product tab', 'Default title of a tab', 'yith-woocommerce-tab-manager' ),
				),
				true
			);

			if ( $tab_id && ! is_wp_error( $tab_id ) ) {
				$tab->set_id( $tab_id );
				$this->update_post_meta( $tab, true );
				$tab->save_meta_data();
				$tab->apply_changes();
				do_action( 'ywtm_new_tab', $tab_id, $tab );
			}
		}

		/**
		 * Method to read a product tab from database
		 *
		 * @param YITH_Tab_Manager_Obj $tab Product tab object.
		 *
		 * @throws Exception If invalid tab.
		 */
		public function read( &$tab ) {
			$tab->set_defaults();
			$post_object = get_post( $tab->get_id() );

			if ( ! $tab->get_id() || ! $post_object || 'ywtm_tab' !== $post_object->post_type ) {
				throw new Exception( esc_html__( 'Invalid tab.', 'yith-woocommerce-tab-manager' ) );
			}

			$this->read_tab_data( $tab );
			$tab->set_object_read( true );

			do_action( 'ywtm_tab_read', $tab->get_id() );
		}

		/**
		 * Read tab data.
		 *
		 * @param YITH_Tab_Manager_Obj $tab Product tab object.
		 *
		 * @since 2.0.0
		 */
		protected function read_tab_data( &$tab ) {
			$id               = $tab->get_id();
			$post_meta_values = get_post_meta( $id );
			$set_props        = array();

			foreach ( self::$meta_key_to_props as $meta_key => $prop ) {
				$meta_value         = isset( $post_meta_values[ $meta_key ][0] ) ? $post_meta_values[ $meta_key ][0] : null;
				$set_props[ $prop ] = maybe_unserialize( $meta_value );
			}

			$tab->set_props( $set_props );
		}

		/**
		 * Update a tab
		 *
		 * @param YITH_Tab_Manager_Obj $tab The tab to create.
		 *
		 * @return void
		 */
		public function update( &$tab ) {
			$tab->save_meta_data();
			$changes = $tab->get_changes();

			if ( in_array( 'name', array_keys( $changes ), true ) ) {
				$post_data = array(
					'post_title' => $tab->get_name( 'edit' ),
				);
				if ( doing_action( 'save_post' ) ) {
					$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $tab->get_id() ) );
					clean_post_cache( $tab->get_id() );
				} else {
					wp_update_post( array_merge( array( 'ID' => $tab->get_id() ), $post_data ) );
				}

				$tab->read_meta_data( true );
			}
			$this->update_post_meta( $tab );
			$tab->apply_changes();
			do_action( 'ywtm_update_tab', $tab->get_id(), $tab );
		}

		/**
		 * Update a tab
		 *
		 * @param YITH_Tab_Manager_Obj $tab The tab to create.
		 * @param array                $args Array of args to pass to the delete method.
		 *
		 * @return void
		 */
		public function delete( &$tab, $args = array() ) {
			$id = $tab->get_id();

			if ( ! $id ) {
				return;
			}
			do_action( 'ywtm_before_delete_tab', $id );
			wp_delete_post( $id );
			$tab->set_id( 0 );
			do_action( 'ywtm_delete_tab', $id );
		}

		/**
		 * Update the boost meta
		 *
		 * @param YITH_Tab_Manager_Obj $tab The tab.
		 * @param bool                 $force Force update. Used during create.
		 *
		 * @return void
		 */
		public function update_post_meta( &$tab, $force = false ) {

			$props_to_update = $force ? self::$meta_key_to_props : $this->get_props_to_update( $tab, self::$meta_key_to_props );
			foreach ( $props_to_update as $meta_key => $prop ) {
				$value = $tab->{"get_$prop"}( 'edit' );
				$value = is_string( $value ) ? wp_slash( $value ) : $value;
				switch ( $prop ) {
					case 'active':
					case 'map_full_width':
						$value = wc_bool_to_string( $value );
						break;
				}

				$this->update_or_delete_post_meta( $tab, $meta_key, $value );
			}
		}

		/**
		 * Manage the query
		 *
		 * @param array $args The query args.
		 *
		 * @return array|int[]|object|YITH_Tab_Manager_Obj[]
		 */
		public function query( $args = array() ) {
			$default = array(
				'name'     => '',
				'active'   => '',
				'type'     => '',
				'include'  => array(),
				'exclude'  => array(),
				'return'   => 'object',
				'limit'    => get_option( 'posts_per_page' ),
				'page'     => 1,
				'offset'   => '',
				'paginate' => false,
				'order'    => 'DESC',
				'orderby'  => 'ID',
			);

			$args = wp_parse_args( $args, $default );

			$query_args = $this->get_wp_query_args( $args );

			if ( ! empty( $query_args['errors'] ) ) {
				$query = (object) array(
					'posts'         => array(),
					'found_posts'   => 0,
					'max_num_pages' => 0,
				);
			} else {
				$query = new WP_Query( $query_args );
			}

			if ( isset( $args['return'] ) && 'objects' === $args['return'] && ! empty( $query->posts ) ) {
				// Prime caches before grabbing objects.
				update_post_caches( $query->posts, 'ywtm_tab' );
			}

			$boost_rules = ( isset( $args['return'] ) && 'ids' === $args['return'] ) ? $query->posts : array_filter( array_map( 'ywtm_get_tab', $query->posts ) );

			if ( isset( $args['paginate'] ) && $args['paginate'] ) {
				return (object) array(
					'boost_rules'   => $boost_rules,
					'total'         => $query->found_posts,
					'max_num_pages' => $query->max_num_pages,
				);
			}

			return $boost_rules;
		}

		/**
		 * Get valid WP_Query args.
		 *
		 * @param array $query_vars Query vars.
		 *
		 * @return array
		 */
		protected function get_wp_query_args( $query_vars ) {

			// Map query vars to ones that get_wp_query_args or WP_Query recognize.
			$key_mapping = array(
				'page'    => 'paged',
				'include' => 'post__in',

			);
			foreach ( $key_mapping as $query_key => $db_key ) {
				if ( isset( $query_vars[ $query_key ] ) ) {
					$query_vars[ $db_key ] = $query_vars[ $query_key ];
					unset( $query_vars[ $query_key ] );
				}
			}

			$wp_query_args = parent::get_wp_query_args( $query_vars );

			if ( ! isset( $wp_query_args['date_query'] ) ) {
				$wp_query_args['date_query'] = array();
			}
			if ( ! isset( $wp_query_args['meta_query'] ) ) {
				$wp_query_args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}

			if ( ! empty( $query_vars['origin'] ) ) {
				$wp_query_args['meta_query'][] = array(
					'key'     => '_ywtm_origin',
					'value'   => $query_vars['origin'],
					'compare' => '=',
				);
			}
			if ( ! empty( $query_vars['type'] ) ) {

				$wp_query_args['meta_query'][] = array(
					'key'     => '_ywtm_tab_type',
					'value'   => $query_vars['type'],
					'compare' => is_array( $query_vars['type'] ) ? 'IN' : '=',
				);
			}

			if ( ! empty( $query_vars['active'] ) ) {
				$value                         = wc_bool_to_string( $query_vars['active'] );
				$wp_query_args['meta_query'][] = array(
					'key'     => '_ywtm_show_tab',
					'value'   => $value,
					'compare' => '=',
				);
			}
			// Handle paginate.
			if ( ! isset( $query_vars['paginate'] ) || ! $query_vars['paginate'] ) {
				$wp_query_args['no_found_rows'] = true;
			}

			// Handle orderby.
			if ( isset( $query_vars['orderby'] ) && 'include' === $query_vars['orderby'] ) {
				$wp_query_args['orderby'] = 'post__in';
			}

			$wp_query_args['post_type']   = 'ywtm_tab';
			$wp_query_args['post_status'] = 'publish';

			return apply_filters( 'ywtm_data_store_cpt_query', $wp_query_args, $query_vars, $this );
		}
	}
}
