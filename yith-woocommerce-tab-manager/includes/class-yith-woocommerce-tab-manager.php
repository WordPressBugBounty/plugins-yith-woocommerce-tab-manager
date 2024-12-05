<?php
/**
 * This class is the main class
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main class
 */
class YITH_WC_Tab_Manager {
	use YITH_Tab_Manager_Trait_Singleton;

	/**
	 * The admin instance
	 *
	 * @var YITH_Tab_Manager_Admin
	 */
	public $admin;

	/**
	 * The frontend instance
	 *
	 * @var YITH_Tab_Manager_Frontend
	 */
	public $frontend;
	/**
	 * The constructor
	 */
	protected function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load' ), 12 );
		add_action( 'init', array( $this, 'load_text_domain' ), 0 );
	}

	/**
	 * Load/ include all other class
	 *
	 * @return void
	 */
	public function load() {
		if ( ! doing_action( 'plugins_loaded' ) ) {
			_doing_it_wrong( __METHOD__, 'This method should be called only once on plugins loaded!', '2.0.0' );

			return;
		}

		YITH_Tab_Manager_Post_Type::get_instance();
		YITH_Tab_Manager_Assets::get_instance();
		YITH_Tab_Manager_Install::get_instance();
		YITH_Tab_Manager_Ajax::get_instance();
		if ( $this->is_request( 'admin' ) ) {
			$this->admin = $this->get_class_name( 'YITH_Tab_Manager_Admin' )::get_instance();
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend = $this->get_class_name( 'YITH_Tab_Manager_Frontend' )::get_instance();
		}
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/yith-woocommerce-tab-manager/yith-woocommerce-tab-manager-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/yith-woocommerce-tab-manager-LOCALE.mo
	 */
	public function load_text_domain() {
		$locale = determine_locale();

		/**
		 * APPLY_FILTERS: plugin_locale
		 *
		 * Filter the locale.
		 *
		 * @param string $locale the locale.
		 * @param string $text_domain The text domain.
		 *
		 * @return string
		 */
		$locale = apply_filters( 'plugin_locale', $locale, 'yith-woocommerce-tab-manager' );
		$suffix = '';
		if ( defined( 'YWTM_PREMIUM' ) ) {
			$suffix = '-premium';
		}

		unload_textdomain( 'yith-woocommerce-tab-manager' );
		load_textdomain( 'yith-woocommerce-tab-manager', WP_LANG_DIR . '/yith-woocommerce-tab-manager' . $suffix . '/yith-woocommerce-tab-manager-' . $locale . '.mo' );
		load_plugin_textdomain( 'yith-woocommerce-tab-manager', false, plugin_basename( YWTM_DIR ) . '/languages' );
	}

	/**
	 * Check if exist the premium version of the class
	 *
	 * @param string $class_name The class name.
	 *
	 * @return string
	 * @author YITH
	 * @since  2.0.0
	 */
	public function get_class_name( $class_name ) {

		if ( class_exists( $class_name . '_Premium' ) ) {
			$class_name = $class_name . '_Premium';
		}

		return $class_name;
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	public function is_request( $type ) {
		$is_request = false;
		switch ( $type ) {
			case 'admin':
				$is_request = is_admin();
				break;
			case 'rest':
				$is_request = WC()->is_rest_api_request();
				break;
			case 'ajax':
				$is_request = defined( 'DOING_AJAX' );
				break;
			case 'cron':
				$is_request = defined( 'DOING_CRON' );
				break;
			case 'frontend':
				$is_request = ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
				break;
		}

		/**
		 * APPLY_FILTERS: yith_tab_manager_is_request
		 *
		 * Filters the check result on the current request.
		 *
		 * @param boolean $result True if this is a request for the specific "type"; false otherwise.
		 * @param string  $type The type of the request (i.e.: admin, rest, ajax, cron, frontend).
		 *
		 * @return boolean
		 */
		return apply_filters( 'yith_tab_manager_is_request', $is_request, $type );
	}
}

if ( ! function_exists( 'yith_tab_manager' ) ) {

	/**
	 * Get the main class
	 *
	 * @return YITH_WC_Tab_Manager
	 */
	function yith_tab_manager() { // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed
		return YITH_WC_Tab_Manager::get_instance();
	}
}
