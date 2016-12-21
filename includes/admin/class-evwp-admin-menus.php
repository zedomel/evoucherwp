<?php
/**
 * Setup menus in WP admin.
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
		add_action( "admin_menu", array( $this, "admin_menu"), 9 );

		//add_action( 'admin_menu', array( $this, 'reports_menu' ), 20 );
		//add_action( 'admin_menu', array( $this, 'settings_menu' ), 50 );
		//add_action( 'admin_menu', array( $this, 'status_menu' ), 60 );

		//add_action( 'admin_head', array( $this, 'menu_highlight' ) );
		//add_action( 'admin_head', array( $this, 'menu_order_count' ) );
		//add_filter( 'menu_order', array( $this, 'menu_order' ) );
		//add_filter( 'custom_menu_order', array( $this, 'custom_menu_order' ) );

		// Add endpoints custom URLs in Appearance > Menus > Pages
		//add_action( 'admin_init', array( $this, 'add_nav_menu_meta_boxes' ) );

		// Admin bar menus
		//if ( apply_filters( 'woocommerce_show_admin_bar_visit_store', true ) ) {
	//		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menus' ), 31 );
	//	}
	}

	/** 
	  * Add the admin menu items
	*/
	public function admin_menu() {
		global $menu;

		if ( current_user_can( 'manage_evoucherwp' ) ) {
			$menu[] = array( '', 'read', 'separator-evoucherwp', '', 'wp-menu-separator evoucherwp' );
		}

	    add_menu_page( __( "E-Vouchers", "evoucherwp" ), __( "E-Vouchers", "evoucherwp" ), "manage_evoucherwp", "evoucherwp", "evoucherwp_admin" );
	}
}

endif;

return new EVWP_Admin_Menus();
