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
			'_requireemail' => array(
				'label' => __( 'Require email address', 'evoucherwp' ),
				'show'  => false,
				'type'  => 'checkbox'
			),
			'_live' => array(
				'label' => __( 'E-voucher is available', 'evoucherwp' ),
				'show'  => false,
				'type'  => 'checkbox'
			),
			'_codeprefix' => array(
				'label' => __( 'Voucher code prefix:', 'evoucherwp' ),
				'show'  => false
			),
			'_codesuffix' => array(
				'label' => __( 'Vocher code suffix:', 'evoucherwp' ),
				'show'  => false
			),
			'_codestype' => array(
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
			'_singlecode' => array(
				'label' => __( 'Single code:', 'evoucherwp' ),
				'class'   => 'hide short',
				'show'  => false
			),
			'_codelength' => array(
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

		$voucher = get_post_meta( $post->ID );
		
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
					evoucherwp_wp_text_input( $field );
				break;
			}
		}

		$startdate = ( $date = get_post_meta( $post->ID, '_startdate', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
		$expiry   = ( $date = get_post_meta( $post->ID, '_expiry', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';



		echo '<p class="form-field">
			<label for="_startdate">' . __( 'Date e-voucher starts being avaiable:', 'evoucherwp' ) . '</label>
			<input type="text" class="short" name="_startdate" id="_startdate" value="' . esc_attr( $startdate ) . '" placeholder="' . _x( 'Start&hellip;', 'placeholder', 'evoucherwp' ) . ' YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
			</p>
			<p class="form-field">
			<label for="_expiry">' . __( 'Date e-voucher expires:', 'evoucherwp' ) . '</label>
			<input type="text" class="short" name="_expiry" id="_expiry" value="' . esc_attr( $expiry ) . '" placeholder="' . _x( 'Expires&hellip;', 'placeholder', 'evoucherwp' ) . '  YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
			</p>
			<p class="form-field">
			<label for="_expirydays">' . __( 'Number of days before voucher expires:', 'evoucherwp' ) . '</label>
			<input type="text" class="short" name="_expirydays" id="_expirydays" value="" placeholder="' . _x( 'Days&hellip;', 'placeholder', 'evoucherwp' ) . '  YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
		</p>';


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

		if ( ! empty( self::$options ) ) {
			foreach ( self::$options as $key => $field ) {
				if ( ! isset( $field['id'] ) ){
					$field['id'] = $key;
				}
				if ( isset( $_POST[ $field['id'] ] ) ){
					update_post_meta( $post_id, $field['id'], evwp_clean( $_POST[ $field['id'] ] ) );	
				}
				elseif ( in_array( $field['id'], array( '_live', '_requireemail' ) ) ){
					update_post_meta( $post_id, $field['id'], 'no');	
				}
			}
		}

		// Update start date
		if ( empty( $_POST['_startdate'] ) ) {
			$startdate = current_time('timestamp');
		} else {
			$startdate = strtotime( $_POST['_startdate'] );
		}

		// $date = date_i18n( 'Y-m-d', $startdate );
		update_post_meta( $post_id, '_startdate', evwp_clean( $startdate ) );

		// Update expiry date
		$expiry = 0;
		if ( !empty( $_POST['_expiry'] ) ) {
			$expiry = strtotime( $_POST['_expiry'] );
		}
		elseif ( !empty( $_POST['_expirydays'])  ) {
			$expiry = $startdate + ( intval( $_POST["_expirydays"] ) * 24 * 60 * 60 );
		}

		if ( $expiry > 0){
			// $date = date_i18n( 'Y-m-d', $date );
			update_post_meta( $post_id, '_expiry', evwp_clean( $expiry ) );
		}


		clean_post_cache( $post_id );
	}
}
