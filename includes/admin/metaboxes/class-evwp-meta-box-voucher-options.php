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
			'_evoucherwp_use_default' => array(
				'label' => __( 'Use default options', 'evoucherwp' ),
				'show'  => false,
				'type'  => 'checkbox',
				'description' => __( 'When enable this voucher will use options set in Settings menu', 'evoucherwp' ),
				'desc_tip'	=> true,
				'name'		=> 'evoucherwp_use_default'

			),
			'_evoucherwp_requireemail' => array(
				'label' => __( 'Require email address', 'evoucherwp' ),
				'show'  => false,
				'type'  => 'checkbox',
				'name'	=> 'evoucherwp_requireemail'
			),
			'_evoucherwp_live' => array(
				'label' => __( 'E-voucher is available', 'evoucherwp' ),
				'show'  => false,
				'type'  => 'checkbox',
				'name'	=> 'evoucherwp_live'
			),
			'_evoucherwp_codeprefix' => array(
				'label' => __( 'Voucher code prefix:', 'evoucherwp' ),
				'show'  => false,
				'name'	=> 'evoucherwp_codeprefix'
			),
			'_evoucherwp_codesuffix' => array(
				'label' => __( 'Vocher code suffix:', 'evoucherwp' ),
				'show'  => false,
				'name'	=> 'evoucherwp_codesuffix'
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
					),
				'name'	=> 'evoucherwp_codestype' 
			),
			'_evoucherwp_singlecode' => array(
				'label' => __( 'Single code:', 'evoucherwp' ),
				'class'   => 'short',
				'wrapper_class' => 'hide',
				'show'  => false,
				'name'	=> 'evoucherwp_singlecode'
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
				),
				'name'	=> 'evoucherwp_codelength'
			),
			'_evoucherwp_startdate' => array(
				'label'			=> __( 'Date e-voucher starts being available:', 'evoucherwp' ),
				'show'			=> false,
				'type'			=> 'text',
				'class'			=> 'short',
				'placeholder'	=> _x( 'Start&hellip;', 'placeholder', 'evoucherwp' ) . 'YYYY-MM-DD',
				'maxlength'		=> 10,
				'pattern'		=> '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
				'name'			=> 'evoucherwp_startdate'
			),
			'_evoucherwp_expiry' => array(
				'label'			=> __( 'Date e-voucher expires:', 'evoucherwp' ),
				'show'			=> false,
				'type'			=> 'text',
				'class'			=> 'short',
				'placeholder'	=> _x( 'Expires&hellip;', 'placeholder', 'evoucherwp' ) . '  YYYY-MM-DD',
				'maxlength'		=> 10,
				'pattern'		=> '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
				'name'			=> 'evoucherwp_expiry'
			),
			'_evoucherwp_expiry_days' => array(
				'label'			=> __( 'Number of days before voucher expires:', 'evoucherwp' ),
				'show'			=> false,
				'type'			=> 'text',
				'class'			=> 'short',
				'placeholder'	=> _x( 'Days&hellip;', 'placeholder', 'evoucherwp' ),
				'name'			=> 'evoucherwp_expiry_days'
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
		if ( isset( $POST[ 'evoucherwp_codestype' ] ) && isset( $_POST[ 'evoucherwp_codelength' ] ) ){
			$change_code_type = $code_type !== sanitize_text_field( $_POST[ 'evoucherwp_codestype' ] );
			$change_code_length = $code_length !== sanitize_text_field( $_POST[ 'evoucherwp_codelength' ] );
		}

		if ( ( isset( $change_code_type) && isset( $change_code_length ) ) && ( $change_code_type || $change_code_length  ) ){
			update_post_meta( $post_id, '_evoucherwp_guid', '' );
			// If code type was 'sequential' we must delete it from respective table
			evwp_delete_sequence_code( $post_id );
		}

		$use_default_option = false;
		if ( isset( $_POST[ 'evoucherwp_use_default' ] ) && $_POST[ 'evoucherwp_use_default' ] === 'yes' ){
			$use_default_option = $_POST[ 'evoucherwp_use_default' ] === 'yes' ;
		}

		if ( ! empty( self::$options ) ) {
			foreach ( self::$options as $key => $field ) {
				if ( ! isset( $field['id'] ) ){
					$field['id'] = $key;
				}

				if ( in_array( $field['id'], array( '_evoucherwp_live', '_evoucherwp_requireemail' ) ) ){
					if ( $use_default_option ){
						update_post_meta( $post_id, $field['id'], get_option( $field['name'], 'no' ) );	
					}
					else{
						update_post_meta( $post_id, $field['id'], 'no' );	
					}
				}
				elseif ( ! in_array( $field[ 'id' ], array( 'evoucherwp_startdate', 'evoucherwp_expiry', 'evoucherwp_expiry_days' )  ) ) {
						if ( $use_default_option ){
							update_post_meta( $post_id, $field['id'], evwp_clean( get_option( $field['name'], '' ) ) );	
						}
						elseif ( isset( $_POST[ $field[ 'id' ] ] ) ) {
							update_post_meta( $post_id, $field['id'], evwp_clean( $_POST[ $field['name'] ] ) );	
						}
				}	
			}
		}

		update_post_meta( $post_id, '_evoucherwp_use_default', $use_default_option ? 'yes' : 'no' );

		// Update start date
		if ( empty( $_POST['evoucherwp_startdate'] ) ) {
			$startdate = current_time('timestamp');
		} else {
			$startdate = strtotime( $_POST[ 'evoucherwp_startdate' ] );
		}

		// $date = date_i18n( 'Y-m-d', $startdate );
		update_post_meta( $post_id, '_evoucherwp_startdate', evwp_clean( $startdate ) );

		// Update expiry date
		// Never expires === 0
		$expiry = '';
		if ( !empty( $_POST[ 'evoucherwp_expiry' ] ) ) {
			$expiry = strtotime( $_POST[ 'evoucherwp_expiry' ] );
		}
		elseif ( !empty( $_POST[ 'evoucherwp_expiry_days' ] ) || $use_default_option  ) {
			$days = $use_default_option ? intval( get_option( 'evoucherwp_expiry', 0 ) ) : intval( $_POST[ 'evoucherwp_expiry_days' ] );
			if ( $days > 0){
				$expiry = $startdate + ( $days * 24 * 60 * 60 );
			}
			else{
				$expiry = 0;
			}
		}

		if ( $expiry >= 0 || empty( $expiry ) ){
			update_post_meta( $post_id, '_evoucherwp_expiry', evwp_clean( $expiry ) );
		}

		// Generates a new code if something has changed
		if ( ( isset( $change_code_type) && isset( $change_code_length ) )  && ( $change_code_type || $change_code_length ) ){
			$code = evwp_generate_voucher_code( $post_id );
	    	if ( $code )
	        	update_post_meta( $post_id, '_evoucherwp_guid', $code );
	    }
		
		clean_post_cache( $post_id );
	}
}
