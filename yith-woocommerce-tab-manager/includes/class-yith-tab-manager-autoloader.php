<?php
/**
 * Autoloader class
 *
 * @class   YITH_Tab_Manager_Autoloader
 * @package YITH/TabManager/Classes
 * @since   2.0.0
 * @author  YITH
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_Tab_Manager_Autoloader' ) ) {
	/**
	 * The autoloader class
	 */
	class YITH_Tab_Manager_Autoloader {

		/**
		 * Constructor
		 */
		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Get mapped file. Array of class => file to use on autoload.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		protected function get_mapped_files() {
			/**
			 * APPLY_FILTERS: ywtm_autoloader_mapped_files
			 *
			 * The filter allow to add remove the class to autoload.
			 *
			 * @param array $mapped_files The mapped files array.
			 *
			 * @return array
			 */
			return apply_filters( 'ywtm_autoloader_mapped_files', array() );
		}

		/**
		 * Autoload callback
		 *
		 * @param string $class_name The class to load.
		 *
		 * @since  1.0.0
		 */
		public function autoload( $class_name ) {

			$class = strtolower( $class_name );
			$class = str_replace( '_', '-', $class );

			if ( false === strpos( $class, 'yith-tab-manager' ) ) {
				return; // Pass over.
			}

			$base_path = YWTM_DIR . 'includes/';
			// Check first for mapped files.
			$mapped = $this->get_mapped_files();
			if ( isset( $mapped[ $class ] ) ) {
				$file = $base_path . $mapped[ $class ] . '.php';
			} elseif ( false !== strpos( $class, 'trait' ) ) {
					$file = $base_path . 'traits/trait-' . $class . '.php';
			} elseif ( false !== strpos( $class, 'admin' ) ) {
				$file = $base_path . 'admin/class-' . $class . '.php';
			} elseif ( false !== strpos( $class, 'data-store' ) ) {
				$file = $base_path . 'data-stores/class-' . $class . '.php';
			} elseif ( false !== strpos( $class, 'integration' ) ) {
				$file = $base_path . 'integrations/class-' . $class . '.php';
			} else {
				$file = $base_path . 'class-' . $class . '.php';
			}
			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	}
}

new YITH_Tab_Manager_Autoloader();
