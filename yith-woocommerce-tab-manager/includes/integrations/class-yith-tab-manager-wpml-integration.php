<?php
/**
 * This class manage the integration with WPML
 *
 * @package YITH\TabManager/Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_WPML_Integration' ) ) {
	/**
	 * Add the compatibility with WPML
	 */
	class YITH_Tab_Manager_WPML_Integration {
		use YITH_Tab_Manager_Trait_Singleton;


		/**
		 * The constructor
		 */
		protected function __construct() {
			$this->force_default_editor();
		}

		/**
		 * Force WPML to use the default editor for tabs
		 *
		 * @return void
		 */
		public function force_default_editor() {
			global $sitepress;

			$tm_settings = $sitepress->get_setting( 'translation-management' );
			if ( ! isset( $tm_settings[ WPML_TM_Post_Edit_TM_Editor_Mode::TM_KEY_FOR_POST_TYPE_USE_NATIVE ]['ywtm_tab'] ) || ! $tm_settings[ WPML_TM_Post_Edit_TM_Editor_Mode::TM_KEY_FOR_POST_TYPE_USE_NATIVE ]['ywtm_tab'] ) {
				$tm_settings[ WPML_TM_Post_Edit_TM_Editor_Mode::TM_KEY_FOR_POST_TYPE_USE_NATIVE ]['ywtm_tab'] = true;
				$sitepress->set_setting( 'translation-management', $tm_settings, true );
				WPML_TM_Post_Edit_TM_Editor_Mode::delete_all_posts_option( 'ywtm_tab' );
			}
		}
	}
}
