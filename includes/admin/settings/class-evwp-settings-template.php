<?php
/**
 * EvoucherWP Template Settings
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

if ( ! class_exists( 'EVWP_Settings_Template' ) ) :

/**
 * EVWP_Settings_General.
 */
class EVWP_Settings_Template extends EVWP_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'template';
		$this->label = __( 'Template', 'evoucherwp' );

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

		$settings = apply_filters( 'evoucherwp_template_settings', array(

			array( 'title' => __( 'Template Options', 'evoucherwp' ), 'type' => 'title', 'desc' => '', 'id' => 'template_options' ),

			array( 
				'title'		=> __( 'Header title', 'evoucherwp' ),
				'desc'		=> __( 'Voucher header title.', 'evoucherwp' ),
				'desc_tip'	=> true,
				'default'	=> '',
				'id'		=> 'evoucherwp_header_title',
				'type'		=> 'text'
				),

			array( 
				'title' 	=> __( 'Header image', 'evoucherwp'),
				'desc'		=> __( 'URL to an image you want to show in the voucher header. Upload image using media uploader (Admin -> Media). It will override default settings.', 'evoucherwp' ),
				'desc_tip'	=> true,
				'id'		=> 'evoucherwp_header_image',
				'type'		=> 'text'
				),

			array( 
				'title'		=> __( 'Footer content', 'evoucherwp' ),
				'desc'		=> __( 'Voucher footer content', 'evoucherwp' ),
				'desc_tip'	=> true,
				'id'		=> 'evoucherwp_footer_content',
				'type'		=> 'textarea',
				'default'	=> __( 'This a demo footer content', 'evoucherwp' )
				),

			array( 'type' => 'sectionend', 'id' => 'template_options')

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

return new EVWP_Settings_Template();
