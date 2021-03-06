<?php
/**
 * Voucher Header
 *
 * Functions for displaying the voucher options meta box.
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-meta-box-order-data.php
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
 * EVWP_Meta_Box_Voucher_Header Class.
 */
class EVWP_Meta_Box_Voucher_Header_Footer {

	/**
	 * Options fields.
	 *
	 * @var array	 */
	protected static $options = array();

	/**
	 * Init fields we display + save.
	 */
	public static function init_header_fields() {

		self::$options = apply_filters( 'evoucherwp_admin_header_footer_fields', array(
			'_evoucherwp_use_default_header' => array(
				'label' => __( 'Use default options', 'evoucherwp' ),
				'show'  => false,
				'type'  => 'checkbox',
				'description' => __( 'When enable this voucher will use options set in Settings menu', 'evoucherwp' ),
				'desc_tip'	=> true,
				'name'		=> 'evoucherwp_use_default_header',
				'default'	=> 'yes'
			),
			'_evoucherwp_header_image' => array(
				'label' => __( 'Header image', 'evoucherwp' ),
				'show'  => false,
				'description' => __( 'URL to an image you want to show in the voucher header. Upload image using media uploader (Admin -> Media). It will override default settings.', 'evoucherwp' ),
				'desc_tip'	=> true,
				'name'		=> 'evoucherwp_header_image'
			),
			'_evoucherwp_header_title' => array(
				'label' => __( 'Header title', 'evoucherwp' ),
				'show'  => false,
				'description' => __('It will override default settings.', 'evoucherwp' ),
				'desc_tip'	=> true,
				'name'		=> 'evoucherwp_header_title'
			),
			'_evoucherwp_footer_content' => array(
				'label' => __( 'Footer', 'evoucherwp' ),
				'show'  => false,
				'type'	=> 'textarea',
				'description' => __( 'Content to display in footer section. It will override default settings.', 'evoucherwp' ),
				'desc_tip'	=> true,
				'name'		=> 'evoucherwp_footer_content'
			)
		) );
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {

		self::init_header_fields();

		wp_nonce_field( 'evoucherwp_save_data', 'evoucherwp_meta_nonce' );
		
       	echo '<div class="panel-wrap">';

		foreach ( self::$options as $key => $field ) {
			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}
			if ( ! isset( $field['id'] ) ){
				$field['id'] = $key;
			}
			
			switch ( $field[ 'type' ] ) {
				case 'textarea':
					evoucherwp_wp_textarea_input( $field );
					break;
				case 'checkbox' :
					evoucherwp_wp_checkbox( $field );
					break;
				default:
					evoucherwp_wp_text_input( $field );
					break;
			}
		}

		echo '</div>';
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		self::init_header_fields();

		$use_default_option = false;
		if ( isset( $_POST[ 'evoucherwp_use_default_header' ] ) && $_POST[ 'evoucherwp_use_default_header' ] === 'yes' ){
			$use_default_option = $_POST[ 'evoucherwp_use_default_header' ] === 'yes' ;
		}

		if ( ! empty( self::$options ) ) {
			foreach ( self::$options as $key => $field ) {
				if ( ! isset( $field['id'] ) ){
					$field['id'] = $key;
				}
				if ( isset( $_POST[ $field[ 'id' ] ] ) ){
					update_post_meta( $post_id, $field['id'], evwp_clean( $_POST[ $field['name'] ] ) );	
				}
				elseif ( $use_default_option ){
					update_post_meta( $post_id, $field['id'], get_option( $field[ 'name' ], '' ) );		
				}
			}
		}
		
		update_post_meta( $post_id, '_evoucherwp_use_default_header', $use_default_option ? 'yes' : 'no' );
		
		clean_post_cache( $post_id );
	}
}
