<?php
/**
 * EVoucherWP Meta Boxes
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-meta-boxes.php
 *
 * Sets up the write panels used by evoucher (custom post types).
 *
 * @author      Jose A. Salim
 * @category    Admin
 * @package     EVoucherWP/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_Admin_Meta_Boxes.
 */
class EVWP_Admin_Meta_Boxes {

	/**
	 * Is meta boxes saved once?
	 *
	 * @var boolean
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Meta box error messages.
	 *
	 * @var array
	 */
	public static $meta_box_errors  = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );

		// Save E-Voucher Meta Boxes
		add_action( 'evoucherwp_process_evoucher_meta', 'EVWP_Meta_Box_Voucher_Options::save', 10, 2 );
		add_action( 'evoucherwp_process_evoucher_meta', 'EVWP_Meta_Box_Voucher_Header_Footer::save', 20, 2 );

		// Error handling (for showing errors from meta boxes on next page load)
		add_action( 'admin_notices', array( $this, 'output_errors' ) );
		add_action( 'shutdown', array( $this, 'save_errors' ) );
	}

	/**
	 * Add an error message.
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option.
	 */
	public function save_errors() {
		update_option( 'evoucherwp_meta_box_errors', self::$meta_box_errors );
	}

	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
		$errors = maybe_unserialize( get_option( 'evoucherwp_meta_box_errors' ) );

		if ( ! empty( $errors ) ) {

			echo '<div id="evoucherwp_errors" class="error notice is-dismissible">';

			foreach ( $errors as $error ) {
				echo '<p>' . wp_kses_post( $error ) . '</p>';
			}

			echo '</div>';

			// Clear
			delete_option( 'evoucherwp_meta_box_errors' );
		}
	}

	/**
	 * Add EVoucherWP Meta boxes.
	 */
	public function add_meta_boxes() {
	    add_meta_box( 'voucher_options', __( 'E-voucher Options', 'evoucherwp' ), 'EVWP_Meta_Box_Voucher_Options::output', 'evoucher', 'normal' );
	    add_meta_box( 'voucher_header', __( 'Header and Footer', 'evoucherwp' ), 'EVWP_Meta_Box_Voucher_Header_Footer::output', 'evoucher', 'normal' );
	}

	/**
	 * Remove bloat.
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'voucher_options', 'evoucherwp', 'normal' );
		remove_meta_box( 'voucher_header', 'evoucherwp', 'normal' );
	}

	/**
	 * Check if we're saving, the trigger an action based on the post type.
	 *
	 * @param  int $post_id
	 * @param  object $post
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['evoucherwp_meta_nonce'] ) || ! wp_verify_nonce( $_POST['evoucherwp_meta_nonce'], 'evoucherwp_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// We need this save event to run once to avoid potential endless loops. This would have been perfect:
		//	remove_action( current_filter(), __METHOD__ );
		// But cannot be used due to https://github.com/woothemes/woocommerce/issues/6485
		// When that is patched in core we can use the above. For now:
		self::$saved_meta_boxes = true;

		// Check the post type
		if ( in_array( $post->post_type, array( 'evoucher' ) ) ) {
			do_action( 'evoucherwp_process_' . str_replace('-', '_', $post->post_type ) . '_meta', $post_id, $post );	
		}
	}

}

new EVWP_Admin_Meta_Boxes();
