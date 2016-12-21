<?php
/**
 * EVoucherWP Admin
 *
 * @class    EVWP_Admin
 * @author   Jose A. Salim
 * @category Admin
 * @package  EVoucherWP/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_Admin class.
 */
class EVWP_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		//add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		//add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public function buffer() {
		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( 'evwp-meta-box-functions.php' );
		include_once( 'class-evwp-admin-post-types.php' );
		include_once( 'class-evwp-admin-menus.php' );
		include_once( 'class-evwp-admin-assets.php' );

		//include_once( 'evoucherwp-admin-evoucher.php' );
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {
		if ( ! $screen = get_current_screen() ) {
			return;
		}

		switch ( $screen->id ) {
			case 'dashboard' :
				include( 'class-wc-admin-dashboard.php' );
			break;
			case 'options-permalink' :
				include( 'class-wc-admin-permalink-settings.php' );
			break;
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
				include( 'class-wc-admin-profile.php' );
			break;
		}
	}

	/**
	 * Prevent any user who cannot 'edit_posts' (subscribers, customers etc) from accessing admin.
	 */
	public function prevent_admin_access() {
		$prevent_access = false;

		if ( 'yes' === get_option( 'evoucherwp_lock_down_admin', 'yes' ) && ! is_ajax() && basename( $_SERVER["SCRIPT_FILENAME"] ) !== 'admin-post.php' ) {
			$has_cap     = false;
			$access_caps = array( 'edit_posts', 'manage_evoucherwp', 'view_admin_dashboard' );

			foreach ( $access_caps as $access_cap ) {
				if ( current_user_can( $access_cap ) ) {
					$has_cap = true;
					break;
				}
			}

			if ( ! $has_cap ) {
				$prevent_access = true;
			}
		}

		if ( apply_filters( 'evucherwp_prevent_admin_access', $prevent_access ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Change the admin footer text on EVoucherWP admin pages.
	 *
	 * @since  1.0
	 * @param  string $footer_text
	 * @return string
	 */
	/*public function admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_evoucherwp' ) ) {
			return;
		}
		$current_screen = get_current_screen();
		$wc_pages       = wc_get_screen_ids();

		// Set only wc pages
		$wc_pages = array_flip( $wc_pages );
		if ( isset( $wc_pages['profile'] ) ) {
			unset( $wc_pages['profile'] );
		}
		if ( isset( $wc_pages['user-edit'] ) ) {
			unset( $wc_pages['user-edit'] );
		}
		$wc_pages = array_flip( $wc_pages );

		// Check to make sure we're on a WooCommerce admin page
		if ( isset( $current_screen->id ) && apply_filters( 'woocommerce_display_admin_footer_text', in_array( $current_screen->id, $wc_pages ) ) ) {
			// Change the footer text
			if ( ! get_option( 'woocommerce_admin_footer_text_rated' ) ) {
				$footer_text = sprintf( __( 'If you like <strong>WooCommerce</strong> please leave us a %s&#9733;&#9733;&#9733;&#9733;&#9733;%s rating. A huge thanks in advance!', 'woocommerce' ), '<a href="https://wordpress.org/support/view/plugin-reviews/woocommerce?filter=5#postform" target="_blank" class="wc-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'woocommerce' ) . '">', '</a>' );
				wc_enqueue_js( "
					jQuery( 'a.wc-rating-link' ).click( function() {
						jQuery.post( '" . WC()->ajax_url() . "', { action: 'woocommerce_rated' } );
						jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
					});
				" );
			} else {
				$footer_text = __( 'Thank you for selling with WooCommerce.', 'woocommerce' );
			}
		}

		return $footer_text;
	}*/
}

return new EVWP_Admin();
