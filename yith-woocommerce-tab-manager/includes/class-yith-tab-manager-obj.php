<?php
/**
 * This class manage the Tab object
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage the tab entities
 */
class YITH_Tab_Manager_Obj extends WC_Data {
	/**
	 * The default data of the price rule
	 *
	 * @var array
	 */
	protected $data = array(
		'name'        => '',
		'type'        => 'global',
		'active'      => 'yes',
		'order'       => 20,
		'icon'        => array(
			'select' => 'none',
			'icon'   => '',
			'custom' => '',
		),
		'layout'      => 'default',
		'form'        => array(),
		'is_editable' => 'yes',
		'origin'      => 'plugin',
		'content'     => '',
	);

	/**
	 * Create a price rule object
	 *
	 * @param int|string|WP_Post|YITH_Tab_Manager_Obj $tab The tab to retrieve.
	 *
	 * @throws Exception Error from WC_Data_Store::load.
	 */
	public function __construct( $tab = 0 ) {
		parent::__construct( $tab );

		$this->object_type = 'product-tab';
		if ( is_numeric( $tab ) && absint( $tab ) > 0 ) {
			$this->set_id( absint( $tab ) );
		} elseif ( $tab instanceof self ) {
			$this->set_id( absint( $tab->get_id() ) );
		} elseif ( ! empty( $tab->ID ) ) {
			$this->set_id( absint( $tab->ID ) );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( 'ywtm-data-store' );
		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/**
	 * Customize the hook prefix
	 *
	 * @return string
	 */
	public function get_hook_prefix() {
		return 'ywtm_' . $this->object_type;
	}

	// SETTER METHOD.

	/**
	 * Set the name of the tab
	 *
	 * @param string $name The name.
	 *
	 * @return void
	 */
	public function set_name( $name ) {
		$this->set_prop( 'name', $name );
	}


	/**
	 * Set the tab type ( global, for product or categories )
	 *
	 * @param string $type The type.
	 *
	 * @return void
	 */
	public function set_type( $type ) {
		$this->set_prop( 'type', $type );
	}

	/**
	 * Set if the tab should be views on product or not
	 *
	 * @param string $active Yes or not.
	 *
	 * @return void
	 */
	public function set_active( $active ) {
		$this->set_prop( 'active', $active );
	}

	/**
	 * Set the position of the tab
	 *
	 * @param int $order The order.
	 *
	 * @return void
	 */
	public function set_order( $order ) {
		$this->set_prop( 'order', $order );
	}

	/**
	 * Set the icon of the tab
	 *
	 * @param array $icon The icon.
	 *
	 * @return void
	 */
	public function set_icon( $icon ) {
		$this->set_prop( 'icon', $icon );
	}

	/**
	 * Set the tab layout ( content, faq, downloads etc )
	 *
	 * @param string $layout The layout.
	 *
	 * @return void
	 */
	public function set_layout( $layout ) {
		$this->set_prop( 'layout', $layout );
	}

	/**
	 * Set the default content for the tab
	 *
	 * @param string $content The content.
	 *
	 * @return void
	 */
	public function set_content( $content ) {
		$this->set_prop( 'content', $content );
	}


	/**
	 * Set if the tab is editable or not
	 *
	 * @param string $is_editable Yes or no.
	 *
	 * @return void
	 */
	public function set_is_editable( $is_editable ) {
		$this->set_prop( 'is_editable', $is_editable );
	}

	/**
	 * Set the tab origin
	 *
	 * @param string $origin plugin|woocommerce.
	 *
	 * @return void
	 */
	public function set_origin( $origin ) {
		$this->set_prop( 'origin', $origin );
	}
	// GETTER METHOD.

	/**
	 * Get the name of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_name( $context = 'view' ) {
		return $this->get_prop( 'name', $context );
	}


	/**
	 * Set the tab type ( global, for product or categories )
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_type( $context = 'view' ) {
		return $this->get_prop( 'type', $context );
	}

	/**
	 * Get if the tab should be views on product or not
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_active( $context = 'view' ) {
		return $this->get_prop( 'active', $context );
	}

	/**
	 * Get the position of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_order( $context = 'view' ) {
		return $this->get_prop( 'order', $context );
	}


	/**
	 * Get the icon of the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_icon( $context = 'view' ) {
		return $this->get_prop( 'icon', $context );
	}

	/**
	 * Get the tab layout ( content, faq, downloads etc )
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_layout( $context = 'view' ) {
		return $this->get_prop( 'layout', $context );
	}

	/**
	 * Get the default content for the tab
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_content( $context = 'view' ) {
		return $this->get_prop( 'content', $context );
	}


	/**
	 * Set if the tab is editable or not
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_is_editable( $context = 'view' ) {
		return $this->get_prop( 'is_editable', $context );
	}

	/**
	 * Set the tab origin
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_origin( $context = 'view' ) {
		return $this->get_prop( 'origin', $context );
	}

	/**
	 * The tab can be shown in the product
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return boolean
	 */
	public function can_show( $product ) {
		return true;
	}

	/**
	 * Get the tab content
	 *
	 * @return string
	 */
	public function show_tab_content() {
		$template_name = 'default';
		$args          = array(
			'default' => $this->get_content(),
		);

		return wc_get_template_html( "woocommerce/single-product/tabs/{$template_name}.php", $args, '', YWTM_TEMPLATE_PATH );
	}

	/**
	 * Return the html icon
	 *
	 * @return string
	 */
	public function get_html_icon() {

		return '';
	}

	/**
	 * Return the name with the icon
	 *
	 * @return string
	 */
	public function get_formatted_name() {
		$name = $this->get_name();
		$icon = $this->get_html_icon();

		/**
		 * APPLY_FILTERS: ywtm_get_tab_title
		 *
		 * The filter allow to show or not the tab in the product.
		 *
		 * @param string               $title The tab title with the icon.
		 * @param YITH_Tab_Manager_Obj $tab The tab.
		 *
		 * @return string
		 */
		return apply_filters( 'ywtm_get_tab_title', $icon . $name, $this );
	}
}
