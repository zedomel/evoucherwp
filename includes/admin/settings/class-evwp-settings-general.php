<?php
/**
 * EvoucherWP General Settings
 * Based on: https://github.com/woocommerce/woocommerce/blob/master/includes/admin/settings/class-wc-settings-general.php
 *
 * @author      Jose. A Salim
 * @category    Admin
 * @package     EVoucherWP/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'EVWP_Settings_General' ) ) :

/**
 * EVWP_Settings_General.
 */
class EVWP_Settings_General extends EVWP_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'general';
		$this->label = __( 'General', 'evoucherwp' );

		add_filter( 'evoucherwp_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'evoucherwp_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'evoucherwp_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = apply_filters( 'evoucherwp_general_settings', array(

			array( 'title' => __( 'General Options', 'evoucherwp' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

			array( 
				'title'		=> __( 'Voucher Code Type', 'evoucherwp' ),
				'desc'		=> __( 'Select the type of voucher code generator.', 'evoucherwp' ),
				'css'		=> '',
				'desc_tip'	=> true,
				'default'	=> 'random',
				'id'		=> 'evoucherwp_codestype',
				'type'		=> 'select',
				'options'	=> array(
					'random'		=> __( 'Random', 'evoucherwp'),
					'sequential'	=> __( 'Sequential', 'evoucherwp'),
					'single'		=> __( 'Custom code', 'evoucherwp')
					)
				),
			array( 
				'title' 	=> __( 'Custom code', 'evoucherwp'),
				'desc'		=> __( 'When choose single code type, you must provide a custom code which will be used in all vouchers.', 'evoucherwp' ),
				'desc_tip'	=> true,
				'css'		=> '',
				'id'		=> 'evoucherwp_singlecode',
				'type'		=> 'text'
				),
			array( 
				'title'		=> __( 'Voucher code length', 'evoucherwp' ),
				'desc'		=> __( 'Select the length of voucher codes.', 'evoucherwp' ),
				'desc_tip'	=> true,
				'css'		=> '',
				'default'	=> 6,
				'id'		=> 'evoucherwp_codelength',
				'type'		=> 'select',
				'options'	=> array(
					6		=> __( '6', 'evoucherwp' ),
					7		=> __( '7', 'evoucherwp' ),
					8		=> __( '8', 'evoucherwp' ),
					9		=> __( '9', 'evoucherwp' ),
					10		=> __( '10', 'evoucherwp' ),
					)
				),
			array( 
				'title'		=> __( 'Voucher code prefix', 'evoucherwp' ),
				'desc'		=> __( 'Text to add at begining voucher codes', 'evoucherwp' ),
				'desc_tip'	=> true,
				'id'		=> 'evoucherwp_codeprefix',
				'type'		=> 'text'
				),
			array( 
				'title'		=> __( 'Voucher code suffix', 'evoucherwp' ),
				'desc'		=> __( 'Text to add at the end of voucher codes', 'evoucherwp' ),
				'desc_tip'	=> true,
				'id'		=> 'evoucherwp_codesuffix',
				'type'		=> 'text'
				),
			array( 
				'title'		=> __( 'Expires', 'evoucherwp' ),
				'desc'		=> __( 'Voucher expiration period in days', 'evoucherwp' ),
				'desc_tip'	=> true,
				'id'		=> 'evoucherwp_expiry',
				'type'		=> 'number'
				),
			array( 
				'title'		=> __( 'Email required', 'evoucherwp' ),
				'desc'		=> __( 'Require a valid email address to download vouchers', 'evoucherwp' ),
				'desc_tip'	=> true,
				'default'	=> 'no',
				'id'		=> 'evoucherwp_requireemail',
				'type'		=> 'checkbox'
				),

			array( 'type' => 'sectionend', 'id' => 'general_options')

		) );

		return apply_filters( 'evoucherwp_get_settings_' . $this->id, $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();

		EVWP_Admin_Settings::save_fields( $settings );
	}

}

endif;

return new EVWP_Settings_General();
