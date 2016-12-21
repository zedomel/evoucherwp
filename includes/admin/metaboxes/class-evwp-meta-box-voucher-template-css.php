<?php
/**
 * Voucher Fields
 *
 * Functions for displaying the voucher template CSS meta box.
 *
 * @author 		Jose A. Salim
 * @category 	Admin
 * @package 	EVoucherWP/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_Meta_Box_Voucher_Fields Class.
 */
class EVWP_Meta_Box_Voucher_Template_CSS {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {

	    wp_nonce_field( 'evoucherwp_save_data', 'evoucherwp_meta_nonce' );

	    echo '<div class="panel-wrap">';
	    evoucherwp_wp_textarea_input( array(
				'id' 	=> '_template_css',
				'label' => '',
				'show'  => false,
				'type'  => 'textarea'
			)
	    );
	    echo '</div>';

	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public static function save( $post_id, $post ) {
		error_log( print_r($_POST, true ) );
		if ( isset( $_POST[ '_template_css' ] ) && !empty( $_POST[ '_template_css' ] ) ){
			update_post_meta( $post_id, '_template_css', $_POST[ '_template_css' ] );
		}

		clean_post_cache( $post_id );
	}
}
