<?php
/**
 * This class manage updates , create table and register data stores
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Install' ) ) {
	/**
	 * The class that manage update and first installation
	 */
	class YITH_Tab_Manager_Install {
		use YITH_Tab_Manager_Trait_Singleton;

		/**
		 * The updates to fire.
		 *
		 * @var callable[][]
		 */
		private $db_updates = array(
			'2.0.0' => array(
				'yith_tab_manager_update_200_porting_global_tabs',
				'yith_tab_manager_update_200_db_version',
			),
		);


		/**
		 * Callbacks to be fired soon, instead of being scheduled.
		 *
		 * @var callable[]
		 */
		private $soon_callbacks = array();
		/**
		 * The version option.
		 */
		const VERSION_OPTION = 'yith_woocommerce_tab_manager_version';

		/**
		 * The version option.
		 */
		const DB_VERSION_OPTION = 'yith_tab_manager_db_version';

		/**
		 * The update scheduled option.
		 */
		const DB_UPDATE_SCHEDULED_OPTION = 'yith_tab_manager_db_update_scheduled_for';

		/**
		 * The construct
		 */
		protected function __construct() {
			$this->install_data_stores();
			add_action( 'init', array( $this, 'check_version' ), 5 );
			add_action( 'yith_tab_manager_run_update_callback', array( $this, 'run_update_callback' ) );
		}

		/**
		 * Get list of DB update callbacks.
		 *
		 * @return array
		 */
		public function get_db_update_callbacks() {
			return $this->db_updates;
		}

		/**
		 * Return true if the callback needs to be fired soon, instead of being scheduled.
		 *
		 * @param string $callback The callback name.
		 *
		 * @return bool
		 */
		private function is_soon_callback( $callback ) {
			return in_array( $callback, $this->soon_callbacks, true );
		}

		/**
		 * Check if install updates
		 *
		 * @return void
		 */
		public function check_version() {
			if ( version_compare( get_option( self::VERSION_OPTION, '1.0.0' ), YWTM_VERSION, '<' ) ) {

				$this->install();
				/**
				 * DO_ACTION: yith_tab_manager_updated
				 * Action fired after updating the plugin.
				 */
				do_action( 'yith_tab_manager_updated' );

			}
		}

		/**
		 * Install
		 *
		 * @return void
		 */
		public function install() {
			// Check if we are not already running this routine.
			if ( 'yes' === get_transient( 'yith_tab_manager_installing' ) ) {
				return;
			}

			set_transient( 'yith_tab_manager_installing', 'yes', MINUTE_IN_SECONDS * 10 );
			if ( ! defined( 'YITH_TAB_MANAGER_INSTALLING' ) ) {
				define( 'YITH_TAB_MANAGER_INSTALLING', true );
			}

			$this->maybe_create_default_tabs();
			$this->maybe_update_db_version();
			update_option( self::VERSION_OPTION, YWTM_VERSION );

			delete_transient( 'yith_tab_manager_installing' );

			/**
			 * DO_ACTION: yith_tab_manager_installed
			 * Action fired after installing the plugin.
			 */
			do_action( 'yith_tab_manager_installed' );
		}

		/**
		 * In a fresh installation or after update to 1.x.x to 2.x.x the plugin needs to create the WooCommerce Tabs
		 *
		 * @throws Exception The exception.
		 */
		public function maybe_create_default_tabs() {

			$wc_tabs_added = get_option( 'yith_tab_manager_wc_added', array() );
			$tabs_to_add   = array(
				'description'            => array(
					'title'    => __( 'Description', 'woocommerce' ),
					'priority' => 1,
				),
				'additional_information' => array(
					'title'    => __( 'Additional Information', 'woocommerce' ),
					'priority' => 2,
				),
				'reviews'                => array(
					'title'    => __( 'Reviews', 'woocommerce' ),
					'priority' => 3,
				),
			);
			if ( empty( $wc_tabs_added ) ) {
				$wc_tabs = ywtm_get_tabs(
					array(
						'type'   => 'woocommerce',
						'return' => 'ids',
					)
				);

				if ( empty( $wc_tabs ) ) {

					foreach ( $tabs_to_add as $type => $tab ) {
						$new_tab = ywtm_get_tab();
						$new_tab->set_name( $tab['title'] );
						$new_tab->set_order( $tab['priority'] );
						$new_tab->set_is_editable( 'no' );
						$new_tab->set_origin( 'woocommerce' );
						$new_tab->save();
						$wc_tabs_added[ $type ] = $new_tab->get_id();
					}
				}

				update_option( 'yith_tab_manager_wc_added', $wc_tabs_added );
			}
		}

		/**
		 * Maybe update db
		 */
		private function maybe_update_db_version() {

			if ( $this->needs_db_update() ) {

				$this->update();
			} else {
				$this->update_db_version();
			}
		}

		/**
		 * The DB needs to be updated?
		 *
		 * @return bool
		 */
		public function needs_db_update() {
			$current_db_version = get_option( self::DB_VERSION_OPTION, '1.0.0' );

			return version_compare( $current_db_version, $this->get_greatest_db_version_in_updates(), '<' );
		}

		/**
		 * Retrieve the major version in update callbacks.
		 *
		 * @return string
		 */
		private function get_greatest_db_version_in_updates() {
			$update_callbacks = $this->get_db_update_callbacks();
			$update_versions  = array_keys( $update_callbacks );
			usort( $update_versions, 'version_compare' );

			return end( $update_versions );
		}

		/**
		 * Update DB version to current.
		 *
		 * @param string|null $version New DB version or null.
		 */
		public static function update_db_version( $version = null ) {
			delete_option( self::DB_VERSION_OPTION );
			add_option( self::DB_VERSION_OPTION, is_null( $version ) ? YWTM_VERSION : $version );

			// Delete "update scheduled for" option, to allow future update scheduling.
			delete_option( self::DB_UPDATE_SCHEDULED_OPTION );
		}

		/**
		 * Install data stores for the plugin
		 *
		 * @return void.
		 */
		protected function install_data_stores() {
			add_filter( 'woocommerce_data_stores', array( $this, 'add_data_stores' ) );
		}

		/**
		 * Add plugin's data stores to list of available ones
		 *
		 * @param array $data_stores Available Data Stores.
		 *
		 * @return array Filtered array of Data Stores
		 */
		public function add_data_stores( $data_stores ) {
			return array_merge(
				$data_stores,
				array(
					'ywtm-data-store' => 'YITH_Tab_Manager_Data_Store',
				)
			);
		}

		/**
		 * Push all needed DB updates to the queue for processing.
		 */
		private function update() {
			$current_db_version   = get_option( self::DB_VERSION_OPTION, '1.0.0' );
			$loop                 = 0;
			$greatest_version     = $this->get_greatest_db_version_in_updates();
			$is_already_scheduled = get_option( self::DB_UPDATE_SCHEDULED_OPTION, '' ) === $greatest_version;
			if ( ! $is_already_scheduled ) {
				foreach ( $this->get_db_update_callbacks() as $version => $update_callbacks ) {
					if ( version_compare( $current_db_version, $version, '<' ) ) {
						foreach ( $update_callbacks as $update_callback ) {
							if ( $this->is_soon_callback( $update_callback ) ) {
								$this->run_update_callback( $update_callback );
							} else {
								WC()->queue()->schedule_single(
									time() + $loop,
									'yith_tab_manager_run_update_callback',
									array(
										'update_callback' => $update_callback,
									),
									'yith-tab-manager-db-updates'
								);
								++$loop;
							}
						}
					}
				}
				update_option( self::DB_UPDATE_SCHEDULED_OPTION, $greatest_version );
			}
		}


		/**
		 * Run an update callback when triggered by ActionScheduler.
		 *
		 * @param string $callback Callback name.
		 */
		public function run_update_callback( $callback ) {
			include_once YWTM_INC . '/functions.yith-tab-manager-update.php';

			if ( is_callable( $callback ) ) {
				self::run_update_callback_start( $callback );
				$result = (bool) call_user_func( $callback );
				self::run_update_callback_end( $callback, $result );
			}
		}

		/**
		 * Triggered when a callback will run.
		 *
		 * @param string $callback Callback name.
		 */
		protected function run_update_callback_start( $callback ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			if ( ! defined( 'YITH_TAB_MANAGER_UPDATING' ) ) {
				define( 'YITH_TAB_MANAGER_UPDATING', true );
			}
		}

		/**
		 * Triggered when a callback has ran.
		 *
		 * @param string $callback Callback name.
		 * @param bool   $result Return value from callback. Non-false need to run again.
		 */
		protected function run_update_callback_end( $callback, $result ) {
			if ( $result ) {
				WC()->queue()->add(
					'yith_tab_manager_run_update_callback',
					array(
						'update_callback' => $callback,
					),
					'yith-tab-manager-db-updates'
				);
			}
		}
	}
}
