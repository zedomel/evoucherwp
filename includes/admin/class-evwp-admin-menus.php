<?php
/**
 * Setup menus in WP admin.
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-menus.php
 *
 * @author   Jose A. Salim
 * @category Admin
 * @package  EVoucherWP/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EVWP_Admin_Menus' ) ) :

/**
 * EVWP_Admin_Menus Class.
 */
class EVWP_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Add menus
		add_action( "admin_menu", array( $this, "admin_menu"), 10 );
	}

	/** 
	  * Add the admin menu items
	*/
	public function admin_menu() {
		global $menu;

		if ( current_user_can( 'manage_evoucherwp' ) ) {
			$menu[] = array( '', 'read', 'separator-evoucherwp', '', 'wp-menu-separator evoucherwp' );
		}

	    add_menu_page( __( "E-Vouchers", "evoucherwp" ), __( "E-Vouchers", "evoucherwp" ), "manage_evoucherwp", "evoucherwp_menu", array( $this, 'display_main_menu' ) );

	    add_submenu_page( 'evoucherwp_menu', __( 'E-Vouchers WP Settings', 'evoucherwp' ),  __( 'Settings', 'evoucherwp' ) , 'manage_evoucherwp', 'evwp-settings', array( $this, 'settings_page' ) );
	}


	/**
	 * Init the settings page.
	 */
	public function settings_page() {
		EVWP_Admin_Settings::output();
	}


	public function display_main_menu(){

	}
}

endif;

return new EVWP_Admin_Menus();
