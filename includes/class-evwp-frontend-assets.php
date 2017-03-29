<?php
/**
 * Handle frontend scripts
 *
 * @class       EVWP_Frontend_Scripts
 * @version     1.0.0
 * @package     EVoucherWP/Classes/
 * @category    Class
 * @author      Jose A. Salim
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EVWP_Frontend_Scripts Class.
 */
class EVWP_Frontend_Scripts {

	/**
	 * Contains an array of script handles registered by WC.
	 * @var array
	 */
	private static $scripts = array();

	/**
	 * Contains an array of script handles registered by WC.
	 * @var array
	 */
	private static $styles = array();

	/**
	 * Contains an array of script handles localized by WC.
	 * @var array
	 */
	private static $wp_localize_scripts = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {
		global $post;

		if ( ! did_action( 'before_evoucherwp_init' ) ) {
			return;
		}

		$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register any scripts for later use, or used as dependencies
		wp_register_script( 'evoucherwp-single-voucher-script',  EVoucherWP()->plugin_url() . '/assets/js/evoucher-single-voucher' . $suffix . '.js', array( 'jquery'), EVWP_VERSION );

		// CSS Styles
		wp_register_style(  'evoucherwp-single-voucher-style', EVoucherWP()->plugin_url() . '/assets/css/evoucherwp-single-voucher.css', array(), EVWP_VERSION );

		// Register frontend scripts conditionally
		if ( is_voucher() ){
			wp_enqueue_script( 'evoucherwp-single-voucher-script' );
			wp_enqueue_style( 'evoucherwp-single-voucher-style' );
		}
	}
}

EVWP_Frontend_Scripts::init();
