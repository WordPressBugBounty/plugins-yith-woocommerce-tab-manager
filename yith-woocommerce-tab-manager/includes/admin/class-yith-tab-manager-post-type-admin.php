<?php
/**
 * This class manage the metabox layout and the tab list table
 *
 * @package YITH/TabManager/classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Tab_Manager_Post_Type_Admin' ) ) {
	/**
	 * The class that manage the tab list and the meta-box style
	 */
	class YITH_Tab_Manager_Post_Type_Admin extends YITH_Post_Type_Admin {

		/**
		 * The post type.
		 *
		 * @var string
		 */
		protected $post_type = 'ywtm_tab';


		/**
		 * The object to be shown for each row.
		 *
		 * @var YITH_Tab_Manager_Obj|null
		 */
		protected $object = null;

		/**
		 * The construct
		 */
		protected function __construct() {
			parent::__construct();
			// Register metabox to tab manager.
			add_action( 'admin_init', array( $this, 'add_tab_metabox' ), 1 );
			add_action( 'admin_init', array( $this, 'can_edit_tab' ), 20 );
			add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
			add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_post_updated_messages' ), 20, 2 );
			add_action( 'admin_menu', array( $this, 'remove_publish_box' ) );
			add_action( 'admin_head', array( $this, 'disable_screen_layout_columns' ) );
			add_filter(
				"get_user_option_screen_layout_{$this->post_type}",
				array(
					$this,
					'force_single_column_screen_layout',
				)
			);
			add_action( 'dbx_post_sidebar', array( $this, 'print_save_button_in_edit_page' ) );
			add_action( "save_post_{$this->post_type}", array( $this, 'save_tab' ), 20 );
			add_filter( 'post_class', array( $this, 'filter_post_class' ), 10, 3 );
			add_action( 'admin_action_duplicate_tab', array( $this, 'duplicate_tab' ), 30 );
		}


		/**
		 * Create the object
		 *
		 * @param int $post_id The post id.
		 *
		 * @return void
		 * @throws Exception The exception.
		 */
		protected function prepare_row_data( $post_id ) {
			if ( empty( $this->object ) || $this->object->get_id() !== $post_id ) {
				$this->object = ywtm_get_tab( $post_id );
			}
		}

		/**
		 * Define bulk actions.
		 *
		 * @param array $actions Existing actions.
		 *
		 * @return array
		 */
		public function define_bulk_actions( $actions ) {
			unset( $actions['trash'] );
			unset( $actions['edit'] );
			$actions['activate']   = __( 'Enable', 'yith-woocommerce-tab-manager' );
			$actions['deactivate'] = __( 'Disable', 'yith-woocommerce-tab-manager' );
			$actions['delete']     = __( 'Delete', 'yith-woocommerce-tab-manager' );

			return $actions;
		}

		/**
		 * Define the columns
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 */
		public function define_columns( $columns ) {

			if ( isset( $columns['date'] ) ) {
				unset( $columns['date'] );
			}

			if ( isset( $columns['title'] ) ) {
				unset( $columns['title'] );
			}

			$custom_columns = array(
				'custom_title' => __( 'Tab name', 'yith-woocommerce-tab-manager' ),
				'enable'       => __( 'Enable', 'yith-woocommerce-tab-manager' ),
				'actions'      => __( 'Actions', 'yith-woocommerce-tab-manager' ),
			);

			return array_merge( $columns, $custom_columns );
		}


		/**
		 * Show the column
		 *
		 * @since  2.0.0
		 */
		public function render_custom_title_column() {
			$post          = get_post( $this->post_id );
			$edit_link     = get_edit_post_link( $this->post_id );
			$can_edit_post = current_user_can( 'edit_post', $post->ID );

			if ( $can_edit_post && 'trash' !== $post->post_status ) {
				$lock_holder = wp_check_post_lock( $post->ID );

				if ( $lock_holder ) {
					$lock_holder   = get_userdata( $lock_holder );
					$locked_avatar = get_avatar( $lock_holder->ID, 18 );
					/* translators: %s: User's display name. */
					$locked_text = esc_html( sprintf( __( '%s is currently editing' ), $lock_holder->display_name ) );
				} else {
					$locked_avatar = '';
					$locked_text   = '';
				}

				echo '<div class="locked-info"><span class="locked-avatar">' . $locked_avatar . '</span> <span class="locked-text">' . $locked_text . "</span></div>\n";  //phpcs:ignore WordPress.Security.EscapeOutput
			}

			echo '<strong>';

			$title = _draft_or_post_title();

			if ( $can_edit_post && 'trash' !== $post->post_status && 'yes' === $this->object->get_is_editable() ) {
				printf(
					'<a class="row-title" href="%s" aria-label="%s">%s</a>',
					$edit_link,  //phpcs:ignore WordPress.Security.EscapeOutput
					/* translators: %s: Post title. */
					esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $title ) ),
					$title  //phpcs:ignore WordPress.Security.EscapeOutput
				);
			} else {
				printf(
					'<span class="row-title">%s</span>',
					$title  //phpcs:ignore WordPress.Security.EscapeOutput
				);
			}
			_post_states( $post );

			if ( isset( $parent_name ) ) {
				$post_type_object = get_post_type_object( $post->post_type );
				echo ' | ' . $post_type_object->labels->parent_item_colon . ' ' . esc_html( $parent_name );  //phpcs:ignore WordPress.Security.EscapeOutput
			}

			echo "</strong>\n";

			get_inline_data( $post );
		}

		/**
		 * Render the field to toggle status
		 *
		 * @since  2.0.0
		 */
		public function render_enable_column() {

			$args = array(
				'type'  => 'onoff',
				'class' => 'ywtm-toggle-enabled',
				'value' => $this->object->get_active(),
				'data'  => array(
					'tab-id' => $this->object->get_id(),
				),
			);

			yith_plugin_fw_get_field( $args, true );
		}

		/**
		 * Render Actions column
		 *
		 * @since  1.3.0
		 */
		protected function render_actions_column() {
			$is_editable = 'yes' === $this->object->get_is_editable();
			$wc_tabs_opt = get_option( 'yith_tab_manager_wc_added', array() );
			$is_woo_tab  = in_array( $this->object->get_id(), $wc_tabs_opt, true );
			$actions     = array();

			if ( $is_editable && ! $is_woo_tab ) {
				$show_duplicate_link = add_query_arg(
					array(
						'post_type' => $this->post_type,
						'action'    => 'duplicate_tab',
						'post'      => $this->post_id,
					),
					admin_url( 'post.php' )
				);

				$show_duplicate_link = wp_nonce_url( $show_duplicate_link, 'ywtm-duplicate-rule_' . $this->post_id );
				$extra_action        = array(
					'duplicate-url' => $show_duplicate_link,
				);
				$actions             = yith_plugin_fw_get_default_post_actions( $this->post_id, $extra_action );
				if ( isset( $actions['trash'] ) ) {
					unset( $actions['trash'] );
				}
				$actions['delete']                 = array(
					'type'   => 'action-button',
					'title'  => _x( 'Delete Permanently', 'Post action', 'yith-plugin-fw' ),
					'action' => 'delete',
					'icon'   => 'trash',
					'url'    => get_delete_post_link( $this->post_id, '', true ),
				);
				$actions['delete']['confirm_data'] = array(
					'title'               => __( 'Confirm delete', 'yith-plugin-fw' ),
					// phpcs:ignore  WordPress.WP.I18n.MissingTranslatorsComment
					'message'             => sprintf( __( 'Are you sure you want to delete "%s"?', 'yith-plugin-fw' ), '<strong>' . _draft_or_post_title( $this->post_id ) . '</strong>' ) . '<br /><br />' . __( 'This action cannot be undone and you will not be able to recover this data.', 'yith-plugin-fw' ),
					'cancel-button'       => __( 'No', 'yith-plugin-fw' ),
					'confirm-button'      => _x( 'Yes, delete', 'Delete confirmation action', 'yith-plugin-fw' ),
					'confirm-button-type' => 'delete',
				);
			}
			if ( count( $actions ) > 0 ) {
				yith_plugin_fw_get_action_buttons( $actions );
			}
		}


		/**
		 * Add_tab_metabox, register metabox for global tab
		 *
		 * @since 1.0.0
		 */
		public function add_tab_metabox() {

			$args = include_once YWTM_INC . '/admin/meta-boxes/tab-metabox.php';

			if ( ! function_exists( 'YIT_Metabox' ) ) {
				require_once YWTM_DIR . 'plugin-fw/yit-plugin.php';
			}
			$metabox = YIT_Metabox( 'yit-tab-manager-setting' );
			$metabox->init( $args );
		}

		/**
		 * Change messages when a post type is updated.
		 *
		 * @param array $messages Array of messages.
		 *
		 * @return array
		 */
		public function post_updated_messages( $messages ) {

			$updated_messages = array(
				1 => __( 'Tab updated.', 'yith-woocommerce-tab-manager' ),
				4 => __( 'Tab updated.', 'yith-woocommerce-tab-manager' ),
				7 => __( 'Tab saved.', 'yith-woocommerce-tab-manager' ),
			);

			$messages[ $this->post_type ] = $updated_messages;

			return $messages;
		}

		/**
		 * Add messages for the bulk actions
		 *
		 * @param array $bulk_messages The bulk messages.
		 * @param array $bulk_counts The bulk counts.
		 *
		 * @return array
		 */
		public function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
			$bulk_messages[ $this->post_type ] = array(
				/* translators: %s: Number of tabs. */
				'deleted' => _n( '%s tab permanently deleted.', '%s tabs permanently deleted.', $bulk_counts['deleted'], 'yith-woocommerce-tab-manager' ),
			);

			return $bulk_messages;
		}

		/**
		 * Return true to use only one column in edit page.
		 *
		 * @return bool
		 */
		protected function use_single_column_in_edit_page() {
			return true;
		}

		/**
		 * Is the post type edit page?
		 *
		 * @return bool
		 */
		public function is_post_type_edit() {
			$screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$screen_id = (bool) $screen ? $screen->id : false;

			return $screen_id === $this->post_type;
		}

		/**
		 * Disable the screen layout columns, by setting it to 1 column.
		 */
		public function disable_screen_layout_columns() {
			if ( $this->is_post_type_edit() ) {
				get_current_screen()->add_option(
					'layout_columns',
					array(
						'max'     => 1,
						'default' => 1,
					)
				);
			}
		}

		/**
		 * Force using the single column layout.
		 *
		 * @return int
		 */
		public function force_single_column_screen_layout() {
			return 1;
		}

		/**
		 * Remove publish box from edit post page.
		 */
		public function remove_publish_box() {
			remove_meta_box( 'submitdiv', $this->post_type, 'side' );
		}

		/**
		 * Show blank slate.
		 *
		 * @param string $which String which table-nav is being shown.
		 */
		public function maybe_render_blank_state( $which ) {
			global $post_type;
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_GET['s'] ) && $post_type === $this->post_type && 'top' === $which ) {
				global $wp_query;

				if ( ! $wp_query->found_posts ) {
					$url               = add_query_arg( array( 'post_type' => $this->post_type ), admin_url( 'edit.php' ) );
					$selectors_to_hide = array(
						'.wp-list-table',
						'.ywtm_list_table .search-box',
						'.ywtm_list_table .wrap .subtitle',
					);
					$search            = sanitize_text_field( wp_unslash( $_GET['s'] ) );
					$search            = '<span class="ywtm_table_no_results_search">' . esc_html( $search ) . '</span>';
					// translators: %s is the tab searched.
					$message = sprintf( __( 'No tabs found for %s.', 'yith-woocommerce-tab-manager' ), $search );

					echo '<div class="ywtm_table_no_results_wrapper">';
					echo '<span>' . wp_kses_post( $message ) . '</span>';
					echo '<span><a href="' . esc_url( $url ) . '">' . esc_html__( 'Go back to the tabs list.', 'yith-woocommerce-tab-manager' ) . '</a></span>';
					echo '</div>';
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<style type="text/css">' . implode( ', ', $selectors_to_hide ) . '{ display: none; }</style>';
				}
			} else {
				parent::maybe_render_blank_state( $which );
			}

			// phpcs:enable
		}


		/**
		 * Print save button in edit page.
		 *
		 * @param WP_Post $post The post.
		 */
		public function print_save_button_in_edit_page( $post ) {
			if ( (bool) $post && isset( $post->post_type ) && $this->post_type === $post->post_type ) {
				global $post_id;
				$is_updating      = (bool) $post_id;
				$save_text        = __( 'Save', 'yith-woocommerce-tab-manager' );
				$post_type_object = get_post_type_object( $this->post_type );
				$single           = $post_type_object->labels->singular_name ?? '';

				if ( $single ) {

					$save_text = __( 'Save tab', 'yith-woocommerce-tab-manager' );
				}
				?>
				<div class="ywtm-post-type__actions yith-plugin-ui">
					<?php if ( $is_updating ) : ?>
						<button id="ywtm-post-type__save"
								class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl"><?php echo esc_html( $save_text ); ?></button>
					<?php else : ?>
						<input id="ywtm-post-type__save" type="submit"
								class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl" name="publish"
								value="<?php echo esc_html( $save_text ); ?>">
					<?php endif; ?>

					<a id="ywtm-post-type__float-save"
						class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl yith-plugin-fw-animate__appear-from-bottom"><?php echo esc_html( $save_text ); ?></a>
				</div>
				<?php
			}
		}

		/**
		 * Sync and trigger other event when a tab is created/updated
		 *
		 * @param int $tab_id The tab.
		 *
		 * @return void
		 */
        public function save_tab( $tab_id ) {

	        remove_action( "save_post_{$this->post_type}", array( $this, 'save_tab' ), 20 );
	        $this->sync_post_title( $tab_id );
	        add_action( "save_post_{$this->post_type}", array( $this, 'save_tab' ), 20 );
        }
		/**
		 * Sync the post title with the meta
		 *
		 * @param int $tab_id The tab.
		 *
		 * @return void
		 */
		public function sync_post_title( $tab_id ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$tab_title = isset( $_REQUEST['yit_metaboxes']['_ywtm_tab_title'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['yit_metaboxes']['_ywtm_tab_title'] ) ) : '';
			if ( ! empty( $tab_title ) ) {
				$post_data = array(
					'post_title' => $tab_title,
				);
				wp_update_post( array_merge( array( 'ID' => $tab_id ), $post_data ) );
			}

			// phpcs:enable
		}


		/**
		 * Add custom class in the row table
		 *
		 * @param array $classes The classes.
		 * @param array $css_class Extra css.
		 * @param int   $post_id The post id.
		 *
		 * @return array
		 */
		public function filter_post_class( $classes, $css_class, $post_id ) {

			if ( get_post_type( $post_id ) === $this->post_type ) {
				$tab = ywtm_get_tab( $post_id );
				if ( 'no' === $tab->get_is_editable() ) {
					$classes[] = 'ywtm-not-editable';
				}
			}

			return $classes;
		}

		/**
		 * Handle any custom filters.
		 *
		 * @param array $query_vars Query vars.
		 *
		 * @return array
		 * @throws Exception The error.
		 */
		protected function query_filters( $query_vars ) {

			$query_vars['post_status'] = 'publish';
			$query_vars['orderby']     = 'meta_value_num';
			$query_vars['meta_key']    = '_ywtm_order_tab'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$query_vars['order']       = 'ASC';

			return $query_vars;
		}

		/**
		 * Duplicate a tab
		 *
		 * @return void
		 * @throws Exception The error.
		 */
		public function duplicate_tab() {
			if ( empty( $_REQUEST['post'] ) ) {
				wp_die( esc_html__( 'No tab has been supplied to duplicate!', 'yith-woocommerce-tab-manager' ) );
			}

			$tab_id = sanitize_text_field( wp_unslash( $_REQUEST['post'] ) );

			check_admin_referer( 'ywtm-duplicate-rule_' . $tab_id );

			$tab     = ywtm_get_tab( $tab_id );
			$new_tab = ( clone $tab );
			$new_tab->set_id( 0 );
			// translators: %s is the Tab name to copy.
			$new_tab->set_name( sprintf( esc_html__( '%s (Copy)', 'yith-woocommerce-tab-manager' ), $new_tab->get_name() ) );

			$new_tab->set_order( ywtm_get_default_priority() );
			$new_tab->save();

			$redirect_url = add_query_arg( array( 'post_type' => 'ywtm_tab' ), admin_url( 'edit.php' ) );
			wp_safe_redirect( $redirect_url );
			exit;
		}


		/**
		 * Check if is possible edit the tab
		 *
		 * @return void
		 */
		public function can_edit_tab() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( isset( $_REQUEST['post'] ) ) {
				$tab_id    = sanitize_text_field( wp_unslash( $_REQUEST['post'] ) );
				$post_type = get_post_type( $tab_id );
				if ( 'ywtm_tab' === $post_type ) {
					$wc_tabs_opt = get_option( 'yith_tab_manager_wc_added', array() );
					$is_woo_tab  = in_array( $tab_id, $wc_tabs_opt ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					if ( $is_woo_tab ) {
						die( esc_html__( 'Can\'t edit this tab.', 'yith-woocommerce-tab-manager' ) );
					}
				}
			}
			// phpcs:enable
		}

		/**
		 * Handle the custom bulk action.
		 *
		 * @param string $redirect_to Redirect URL.
		 * @param string $do_action Selected bulk action.
		 * @param array  $post_ids Post ids.
		 *
		 * @return string
		 */
		public function handle_bulk_actions( $redirect_to, $do_action, $post_ids ) {

			if ( 'activate' !== $do_action && 'deactivate' !== $do_action ) {
				return parent::handle_bulk_actions( $redirect_to, $do_action, $post_ids );
			}

			foreach ( $post_ids as $tab_id ) {

				$post_type_object = get_post_type_object( $this->post_type );

				if ( current_user_can( $post_type_object->cap->delete_post, $tab_id ) ) {
					switch ( $do_action ) {
						case 'activate':
							update_post_meta( $tab_id, '_ywtm_show_tab', 'yes' );
							break;
						case 'deactivate':
							update_post_meta( $tab_id, '_ywtm_show_tab', 'no' );
							break;
						default:
					}
				}
			}

			return $redirect_to;
		}
	}


}