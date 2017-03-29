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
	public function admin_styles( $hook ) {
		global $post;

		// Register admin styles
		wp_register_style( 'evoucherwp-admin-style', EvoucherWP()->plugin_url() . '/assets/css/admin/admin.css', array(), EVWP_VERSION );

		// Admin styles for EVoucherWP pages only
		if ( $hook === 'e-vouchers_page_evwp-settings' ) {
			wp_enqueue_style( 'evoucherwp-admin-style' );
		}
		elseif ( $hook == 'post.php' ||  $hook ==  'post-new.php' ){
			if ( $post->post_type == 'evoucher' ){
				wp_enqueue_style( 'evoucherwp-admin-style' );
			}
		}
	}


	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts( $hook ) {
		global $post;

		$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register scripts
		wp_enqueue_script( 'jquery' );

		wp_register_script( 'evoucherwp-metabox', EVoucherWP()->plugin_url() . '/assets/js/admin/meta-box' . $suffix . '.js', array( 'jquery' ), EVWP_VERSION, true );
		
		wp_register_script( 'jquery-tiptip', EVoucherWP()->plugin_url() . '/assets/js/admin/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), '1.3', true );

		wp_register_script( 'evoucherwp-tooltip-script', EVoucherWP()->plugin_url() . '/assets/js/admin/tooltip' . $suffix . '.js', array('jquery', 'jquery-tiptip' ), EVWP_VERSION, true ); 

		// Edit evoucher
		if ( $hook === 'post.php' ||  $hook === "post-new.php" ) {
			if ( $post->post_type === 'evoucher' ){
				wp_enqueue_script( 'jquery-tiptip' );
			    wp_enqueue_script( 'evoucherwp-metabox' );
			    wp_enqueue_script( 'evoucherwp-tooltip-script' );
			}
		}
		elseif ( $hook === 'e-vouchers_page_evwp-settings' ){
			wp_enqueue_script( 'jquery-tiptip' );
			wp_enqueue_script( 'evoucherwp-tooltip-script' );
		}

		do_action( 'evoucherwp_after_enqueue_scripts' );
	}
}

endif;

return new EVWP_Admin_Assets();
