<?php
/**
 * The file that contain the plugin options
 *
 * @package YITH WooCommerce Tab Manager\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


return array(
	'settings' => array(
		'tab_manager_list_table' => array(
			'type'          => 'post_type',
			'post_type'     => 'ywtm_tab',
			'wp-list-style' => 'classic',
			'wrapper-class' => 'ywtm_list_table',
		),
	),
);
