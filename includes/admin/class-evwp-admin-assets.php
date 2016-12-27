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
	    //wp_localize_script( 'evoucherwp-admin-script', 'vp_siteurl', get_option( 'siteurl' ) );
	    //wp_localize_script( 'evoucherwp-admin-script', 'url', admin_url( 'admin-ajax.php' ) );

	    //TODO: localize messages i18n
	    /* wp_localize_script( 'wc-enhanced-select', 'wc_enhanced_select_params', array(
			'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
			'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
			'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
			'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
			'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
			'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
			'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
			'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
			'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'search_products_nonce'     => wp_create_nonce( 'search-products' ),
			'search_customers_nonce'    => wp_create_nonce( 'search-customers' )
		) );*/

	    wp_enqueue_script( 'evoucherwp-admin-script' );

		// EVoucherWP admin pages
		if ( in_array( $screen_id, array( 'evoucher_template', 'evoucher')) ) {
			wp_enqueue_script( 'evoucherwp-admin-script' );
		}

		// Edit evoucher
		if ( in_array( $screen_id, array( 'evoucher' ) ) ) {
			wp_enqueue_media();
		    wp_register_script( 'evoucherwp-field-metabox', EVoucherWP()->plugin_url() . '/assets/admin/js/field-meta-box.js', array( 'jquery' ), EVWP_VERSION );
		    $ajax_url = EVoucherWP()->ajax_url();
		    wp_localize_script( 'evoucherwp-field-metabox', 'url', $ajax_url );
		    wp_enqueue_script( 'evoucherwp-field-metabox' );
		}
	}
}

endif;

return new EVWP_Admin_Assets();
