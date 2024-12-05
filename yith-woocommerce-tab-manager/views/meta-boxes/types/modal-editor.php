<?php
/**
 * This file manage the new editor field in admin
 *
 * @package YITH\TabManager\MetaBoxes\Types
 *
 * @var array $field The field.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$value = ! empty( $field['value'] ) ? $field['value'] : '';
?>

<div class="ywtm-modal-editor-field" id="ywtm-modal-editor-<?php echo esc_attr( $field['id'] ); ?>" data-id="<?php echo esc_attr( $field['id'] ); ?>">
	<div class="ywtm-modal-editor-view">
		<span class="yith-icon yith-icon-edit ywtm-edit-content"></span>
		<div class="ywtm-edit-content-wrapper">
			<?php echo wp_kses_post( $value ); ?>
		</div>
	</div>
	<div class="ywtm-modal-editor-popup">
		<div class="ywtm-modal-editor-popup-wrapper">
			<div class="ywtm-modal-editor-popup-container">
				<div class="ywtm-modal-editor-popup-header">
					<div class="ywtm-modal-editor-popup-icon yith-icon yith-icon-close">
					</div>
				</div>
				<div class="ywtm-modal-editor-popup-form-content">
					<?php
					yith_plugin_fw_get_field(
						array(
							'id'            => $field['id'],
							'type'          => 'textarea-editor',
							'value'         => $value,
							'name'          => $field['name'],
							'textarea_rows' => 5,
							'wpautop'       => false,
						),
						true,
						false
					);
					?>
					<div class="ywtm-modal-editor-popup-action">
						<button class="ywtm-set-content-btn yith-plugin-fw__button--primary yith-plugin-fw__button--xl"><?php esc_html_e( 'Set content', 'yith-woocommerce-tab-manager' ); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>