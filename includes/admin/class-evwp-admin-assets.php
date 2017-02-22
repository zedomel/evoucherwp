<?php
/**
 * Load assets
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-assets.php
 *
 * @author      Jose A. Salim
 * @category    Admin
 * @package     EvoucherWP/Admin
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EVWP_Admin_Assets' ) ) :

/**
 * EVWP_Admin_Assets Class.
 */
class EVWP_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {
		global $wp_scripts;

		$screen         = get_current_screen();
		$screen_id      = $screen ? $screen->id : '';

		// Register admin styles
		wp_register_style( 'evoucherwp-admin-style', EvoucherWP()->plugin_url() . '/assets/admin/css/admin.css', array(), EVWP_VERSION );

		// Admin styles for EVoucherWP pages only
		if ( in_array( $screen_id, array( 'evoucher_template', 'evoucher' ) ) ) {
			wp_enqueue_style( 'evoucherwp-admin-style' );
		}
	}


	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
		global $wp_query, $post;

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		$wc_screen_id = sanitize_title( __( 'EVoucherWP', 'evoucherwp' ) );
		$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register scripts
		wp_enqueue_script( 'jquery' );
	    //wp_register_script( 'evoucherwp-admin-script', EvoucherWP()->plugin_url() . '/assets/js/admin' . $suffix . '.js', array( 'jquery' ), EVWP_VERSION );


		// Edit evoucher
		if ( in_array( $screen_id, array( 'evoucher' ) ) ) {
			wp_enqueue_media();
		    wp_register_script( 'evoucherwp-field-metabox', EVoucherWP()->plugin_url() . '/assets/admin/js/field-meta-box.js', array( 'jquery' ), EVWP_VERSION );
		    $ajax_url = EVoucherWP()->ajax_url();
		    wp_localize_script( 'evoucherwp-field-metabox', 'url', $ajax_url );
		    //localize messages i18n
		    wp_localize_script( 'evoucherwp-field-metabox', 'evoucherwp_i18n', array(
				'i18n_upload_btn'            => _x( 'Select Media', 'field metabox', 'evoucherwp' ),
				'i18n_media_title'            => _x( 'Select a image', 'field metabox', 'evoucherwp' ),
				'i18n_media_btn'           => _x( 'Use this image', 'field metabox', 'evoucherwp' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'field metabox', 'evoucherwp' )
			) );
		    wp_enqueue_script( 'evoucherwp-field-metabox' );
		}
	}
}

endif;

return new EVWP_Admin_Assets();
