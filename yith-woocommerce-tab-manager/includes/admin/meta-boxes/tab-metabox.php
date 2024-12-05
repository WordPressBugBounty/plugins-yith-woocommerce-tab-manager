<?php
/**
 * This is the tab metabox configuration
 *
 * @package YITH WooCommerce Tab Manager\Admin
 * @since 1.0.0
 */

$args = array(
	'label'    => __( 'Tab Settings', 'yith-woocommerce-tab-manager' ),
	'pages'    => 'ywtm_tab',
	'priority' => 'default',
	'context'  => 'normal',
	'class'    => yith_set_wrapper_class() . ' ywtm_metabox',
	'tabs'     => array(
		'settings' => array(
			'label'  => __( 'Settings', 'yith-woocommerce-tab-manager' ),
			'fields' => apply_filters(
				'ywtm_options_metabox',
				array(
					'general_section'       => array(
						'type' => 'title',
						'desc' => __( 'Tab configuration', 'yith-woocommerce-tab-manager' ),
					),
					'ywtm_tab_title'        => array(
						'label'    => __( 'Tab name', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Enter a name to identify this tab.', 'yith-woocommerce-tab-manager' ),
						'type'     => 'text',
						'required' => true,
					),
					'ywtm_show_tab'         => array(
						'label' => '',
						'type'  => 'hidden',
						'std'   => 'yes',
					),
					'general_section_end'   => array(
						'type' => 'sep',

					),
					'configuration_section' => array(
						'type' => 'title',
						'desc' => __( 'Tab content', 'yith-woocommerce-tab-manager' ),
					),
					'ywtm_order_tab'        => array(
						'label' => '',
						'type'  => 'hidden',
						'std'   => ywtm_get_default_priority(),
					),
					'ywtm_text_tab'         => array(
						'label'    => __( 'Tab content', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Set the content for this tab.', 'yith-woocommerce-tab-manager' ),
						'type'     => 'ywtm-modal-editor',
						'required' => true,
					),
					'ywtm_tab_type'         => array(
						'label' => '',
						'type'  => 'hidden',
						'std'   => 'global',
					),
					'ywtm_is_editable'      => array(
						'label' => '',
						'type'  => 'hidden',
						'std'   => 'yes',
					),
					'ywtm_origin'           => array(
						'label' => '',
						'type'  => 'hidden',
						'std'   => 'plugin',
					),
					'ywtm_tab_content_in' => array(
						'label' => '',
						'type'  => 'hidden',
						'std'   => 'all',
					)
				)
			),

		),

	),

);

return $args;
