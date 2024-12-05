<?php
/**
 * Singleton class trait.
 *
 * @package YITH/TabManager/Traits
 */

/**
 * Singleton trait.
 */
trait YITH_Tab_Manager_Trait_Singleton {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	protected static $instance = array();


	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function __construct() {}

	/**
	 * Get class instance.
	 *
	 * @return self
	 */
	public static function get_instance() {

		$called_class = get_called_class();

		if ( ! isset( static::$instance[ $called_class ] ) ) {
			static::$instance[ $called_class ] = new $called_class();
		}

		return static::$instance[ $called_class ];
	}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {
	}

	/**
	 * Prevent un-serializing.
	 */
	public function __wakeup() {
		_doing_it_wrong( get_called_class(), 'Unserializing instances of this class is forbidden.', YWTM_VERSION ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
