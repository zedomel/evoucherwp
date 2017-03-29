<?php
/**
 * Voucher Options
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
 * EVWP_Meta_Box_Voucher_Options Class.
 */
class EVWP_Meta_Box_Voucher_Options {

	/**
	 * Options fields.
	 *
	 * @var array
	 */
	protected static $options = array();

	/**
	 * Init billing and shipping fields we display + save.
	 */
	public static function init_options_fields() {

		self::$options = apply_filters( 'evoucherwp_admin_options_fields', array(
			'_evoucherwp_requireemail' => array(
				'label' => __( 'Require email address', 'evoucherwp' ),
				'show'  => false,
				'type'  => 'checkbox'
			),
			'_evoucherwp_live' => array(
				'label' => __( 'E-voucher is available', 'evoucherwp' ),
				'show'  => false,
				'type'  => 'checkbox'
			),
			'_evoucherwp_codeprefix' => array(
				'label' => __( 'Voucher code prefix:', 'evoucherwp' ),
				'show'  => false
			),
			'_evoucherwp_codesuffix' => array(
				'label' => __( 'Vocher code suffix:', 'evoucherwp' ),
				'show'  => false
			),
			'_evoucherwp_codestype' => array(
				'label'   => __( 'Codes type:', 'evoucherwp' ),
				'show'    => false,
				'class'   => 'select short',
				'type'    => 'select',
				'options' => array( 
					'' => __( 'Select codes type&hellip;', 'evoucherwp' ), 
					'random' => __( 'Random codes', 'evoucherwp' ),  
					'sequential' => __( 'Sequential codes', 'evoucherwp' ),
					'single' => __( 'Single code', 'evoucherwp' )
					)  
			),
			'_evoucherwp_singlecode' => array(
				'label' => __( 'Single code:', 'evoucherwp' ),
				'class'   => 'short',
				'wrapper_class' => 'hide',
				'show'  => false
			),
			'_evoucherwp_codelength' => array(
				'label' => __( 'Code length:', 'evoucherwp' ),
				'show' => false,
				'type' => 'select',
				'options' => array( '' => __( 'Select code length&hellip;', 'evoucherwp '),
					'6' => __( '6', 'evoucherwp' ),
					'7' => __( '7', 'evoucherwp'),
					'8' => __( '8', 'evoucherwp'),
					'9' => __( '9', 'evoucherwp'),
					'10' => __( '10', 'evoucherwp'),
				)
			),
			'_evoucherwp_startdate' => array(
				'label'			=> __( 'Date e-voucher starts being available:', 'evoucherwp' ),
				'show'			=> false,
				'type'			=> 'text',
				'class'			=> 'short',
				'placeholder'	=> _x( 'Start&hellip;', 'placeholder', 'evoucherwp' ) . 'YYYY-MM-DD',
				'maxlength'		=> 10,
				'pattern'			=> '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])'
			),
			'_evoucherwp_expiry' => array(
				'label'			=> __( 'Date e-voucher expires:', 'evoucherwp' ),
				'show'			=> false,
				'type'			=> 'text',
				'class'			=> 'short',
				'placeholder'	=> _x( 'Expires&hellip;', 'placeholder', 'evoucherwp' ) . '  YYYY-MM-DD',
				'maxlength'		=> 10,
				'pattern'			=> '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])'
			),
			'_evoucherwp_expiry_days' => array(
				'label'			=> __( 'Number of days before voucher expires:', 'evoucherwp' ),
				'show'			=> false,
				'type'			=> 'text',
				'class'			=> 'short',
				'placeholder'	=> _x( 'Days&hellip;', 'placeholder', 'evoucherwp' )
			)
		) );
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {

		self::init_options_fields();

		wp_nonce_field( 'evoucherwp_save_data', 'evoucherwp_meta_nonce' );
		
       	echo '<div class="panel-wrap">';

		foreach ( self::$options as $key => $field ) {
			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}
			if ( ! isset( $field['id'] ) ){
				$field['id'] = $key;
			}
			switch ( $field['type'] ) {
				case 'select' :
					evoucherwp_wp_select( $field );
				break;
				case 'checkbox' :
					evoucherwp_wp_checkbox( $field );
				break;
				default :
					if ( in_array( $field[ 'id' ], array( '_evoucherwp_startdate', '_evoucherwp_expiry' )  ) ){
						$field[ 'value' ] = ( $date = get_post_meta( $post->ID, $field[ 'id' ], true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
					}
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

		self::init_options_fields();

		$code_type = get_post_meta( $post_id, '_evoucherwp_codestype', true );
		$code_length = get_post_meta( $post_id, '_evoucherwp_codelength', true );

		// If any change in code type or length, 
		// delete GUID to calculates it again
		$change_code_type = $code_type !== sanitize_text_field( $_POST[ '_evoucherwp_codestype' ] );
		$change_code_length = $code_length !== sanitize_text_field( $_POST[ '_evoucherwp_codelength' ] );
		if ( $change_code_type || $change_code_length ){
			update_post_meta( $post_id, '_evoucherwp_guid', '' );
			// If code type was 'sequential' we must delete it from respective table
			evwp_delete_sequence_code( $post_id );
		}

		if ( ! empty( self::$options ) ) {
			foreach ( self::$options as $key => $field ) {
				if ( ! isset( $field['id'] ) ){
					$field['id'] = $key;
				}
				if ( isset( $_POST[ $field[ 'id' ] ] ) ){
					if ( ! in_array( $field[ 'id' ], array( '_evoucherwp_startdate', '_evoucherwp_expiry', '_evoucherwp_expiry_days' )  ) ){
						update_post_meta( $post_id, $field['id'], evwp_clean( $_POST[ $field['id'] ] ) );	
					}
				}
				elseif ( in_array( $field['id'], array( '_evoucherwp_live', '_evoucherwp_requireemail' ) ) ){
					update_post_meta( $post_id, $field['id'], 'no');	
				}
			}
		}

		// Update start date
		if ( empty( $_POST['_evoucherwp_startdate'] ) ) {
			$startdate = current_time('timestamp');
		} else {
			$startdate = strtotime( $_POST['_evoucherwp_startdate'] );
		}

		// $date = date_i18n( 'Y-m-d', $startdate );
		update_post_meta( $post_id, '_evoucherwp_startdate', evwp_clean( $startdate ) );

		// Update expiry date
		$expiry = 0;
		if ( !empty( $_POST['_evoucherwp_expiry'] ) ) {
			$expiry = strtotime( $_POST['_evoucherwp_expiry'] );
		}
		elseif ( !empty( $_POST['_evoucherwp_expiry_days'])  ) {
			$expiry = $startdate + ( intval( $_POST[ '_evoucherwp_expiry_days' ] ) * 24 * 60 * 60 );
		}

		if ( $expiry > 0){
			// $date = date_i18n( 'Y-m-d', $date );
			update_post_meta( $post_id, '_evoucherwp_expiry', evwp_clean( $expiry ) );
		}
		
		clean_post_cache( $post_id );
	}
}
