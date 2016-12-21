<?php

/**
 * Plugin Name: EVoucherWP
 * Plugin URI: https://github.com/zedomel/evoucherwp
 * Description: EVoucherWP allows you to offer downloadable, printable vouchers from your Wordpress site. E-vouchers can be available to anyone, or require a name and email address before they can be downloaded, or even, sent by e-mail.
 * Author: José Augusto Salim
 * Version: 1.0.0
 * Author URI: https://github.com/zedomel/
 *
 *
 * @package EVoucherWP
 * @author José Augusto Salim
 * @version 1.0.0
 **/

if ( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'EVoucherWP ') ):

/**
 * Main EVoucherWP class
 * 
 * @class EVoucherWP
 * @version 1.0.0
 */
final class EVoucherWP {

	/**
	* EVoucherWP version
	* 
	* @var string
	*/
	public $version = '1.0.0';

	/**
	 * The single instance of the class.
	 *
	 * @var EVoucherWP
	 * @since 1.0
	 */
	protected static $_instance = null;


	/**
	 * PDF Voucher Factory
	 * 
	 * @var EVWP_PDF_Voucher_Factory
	*/
	public $pdf_factory = null;

	/**
	 * Main EVoucher Instance.
	 *
	 * Ensures only one instance of EVoucherWP is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return EVoucherWP - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'evoucherwp' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'evoucherwp' ), '1.0' );
	}

	/**
	 * EvoucherWP Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'evoucherwp_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 * @since  1.0
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'EVoucherWP_Install', 'install' ) );
	}

	/**
	 * Define WC Constants.
	 */
	private function define_constants() {
		$upload_dir = wp_upload_dir();

		$this->define( 'EVWP_PLUGIN_FILE', __FILE__ );
		$this->define( 'EVWP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'EVWP_VERSION', $this->version );
		$this->define( 'EVOUCHERWP_VERSION', $this->version );
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		include_once( 'includes/class-evwp-autoloader.php' );

		include_once( 'includes/evwp-voucher-functions.php' );

		include_once( 'includes/class-evwp-install.php' );
		include_once( 'includes/class-evwp-ajax.php' );

		if ( $this->is_request( 'admin' ) ) {
			include_once( 'includes/admin/class-evwp-admin.php' );
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}

		include_once( 'includes/class-evwp-post-types.php' ); // Registers post types
		include_once( 'includes/class-evwp-voucher.php' ); // Vouchers
		include_once( 'includes/class-evwp-voucher-template.php' ); // Vouchers Templates
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Include required frontend files.
	 */
	public function frontend_includes() {

	}

	/**
	 * Init EVoucherWP when WordPress Initialises.
	 */
	public function init() {
		// Before init action.
		do_action( 'before_evoucherwp_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();

		// Load class instances.
		//$this->pdf_factory = new EVWP_PDF_Voucher_Factory();

		// Init action.
		do_action( 'evoucherwp_init' );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/woocommerce/woocommerce-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/woocommerce-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'evoucherwp' );

		load_textdomain( 'evoucherwp', WP_LANG_DIR . '/evoucherwp/evoucherwp-' . $locale . '.mo' );
		load_plugin_textdomain( 'evoucherwp', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the template path.
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'evoucherwp_template_path', 'evoucherwp/' );
	}

	/**
	 * Get Ajax URL.
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}
}

endif;

/**
 * Main instance of EVoucherWP.
 *
 * Returns the main instance of EVoucherWP to prevent the need to use globals.
 *
 * @since  1.0
 * @return EVoucherWP
 */
function EVoucherWP() {
	return EVoucherWP::instance();
}

// Global for backwards compatibility.
$GLOBALS['evoucherwp'] = EVoucherWP();


