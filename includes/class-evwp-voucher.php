<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Voucher Class.
 *
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-product.php
 *
 * @class 		EVWP_Voucher
 * @version		1.0.0
 * @package		EVoucherWP/Classes/Vouchers
 * @category	Class
 * @author 		Jose A. Salim
 */
class EVWP_Voucher {

	/**
	 * The voucher (post) ID.
	 *
	 * @var int
	 */
	public $id = 0;

	/**
	 * $post Stores post data.
	 *
	 * @var $post WP_Post
	 */
	public $post = null;

	/**
	 * Initialize voucher.
	 *
	 * @param mixed $voucher
	 */
	public function __construct( $voucher ) {
		if ( is_numeric( $voucher ) ) {
			$this->id   = absint( $voucher );
			$this->post = get_post( $this->id );
		} elseif ( $voucher instanceof EVWP_Voucher ) {
			$this->id   = absint( $voucher->id );
			$this->post = $voucher->post;
		} elseif ( isset( $voucher->ID ) ) {
			$this->id   = absint( $voucher->ID );
			$this->post = $voucher;
		}
	}

	/**
	 * __isset function.
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function __isset( $key ) {
		return metadata_exists( 'post', $this->id, '_' . $key );
	}

	/**
	 * __get function.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key ) {
		$value = get_post_meta( $this->id, '_' . $key, true );

		// Get values or default if not set
		if ( in_array( $key, array( 'guid', 'evoucherwp_template', 'security_code', 'codestype', 'codeprefix', 'codesuffix', 'singlecode' ) ) ) {
			$value = $value ? $value : '';

		} elseif ( in_array( $key, array( 'fields' ) ) ) {
			$value = $value ? $value : array();
		} elseif ( in_array( $key , array( 'expiry', 'startdate', 'codelength' ) ) ){
			$value = $value ? intval( $value ) : 0;
		} elseif ( in_array( $key, array( 'live', 'requireemail' ) ) ){
			$value = ( $value && $value === 'yes' ) ? true : false;
		}

		if ( false !== $value ) {
			$this->$key = $value;
		}

		return $value;
	}

	/**
	 * Get the voucher's post data.
	 *
	 * @return object
	 */
	public function get_post_data() {
		return $this->post;
	}

	/**
	 * Return the voucher ID
	 *
	 * @since 1.0.0
	 * @return int voucher (post) ID
	 */
	public function get_id() {

		return $this->id;
	}

	/**
	 * Wrapper for get_permalink.
	 *
	 * @return string
	 */
	public function get_permalink() {
		return get_permalink( $this->id );
	}

	/**
	 * Get the title of the post.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->post->post_title;
	}

	/**
	 * Get the template id of the voucher.
	 *
	 * @return int template voucher (post) ID.
	 */
	public function get_template() {
		return intval( $this->evoucherwp_template );
	}

	/**
	 * Returns whether or not the product post exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return empty( $this->post ) ? false : true;
	}

	public function get_fields(){
		return (object) maybe_unserialize( $this->fields );
	}


	public function is_valid(){
		if ( empty( $this->guid ) || empty( $this->security_code ) ){
	        return false;
	    }
        // if there is an expiry and the expiry is in the past
        if ( 0 < $this->expiry && time() >= $this->expiry ) {
            return "expired";
        }
        // if there is a start date and the tart date is in the future
        if ( 0 < $this->startdate && time() <  $this->startdate ) {
            return "notyetavailable";
        }

        // if emails are not required

        if ( $this->requireemail === false ) {
            return  $this->live === true ? 'valid' : 'unavailable' ;
        } 
        elseif ( $this->live === true ) {
            return 'unregistered';
        }
        return 'unavailable';
	}

	public function get_download_url( $encode = true ){
		$append = $encode ? '&amp;' : '&';
		$id = $append . 'id=' . urlencode( $this->id );
		$security_code = '';
    	if ( "" != $this->security_code ) {
    		$security_code = $append . "sc=" . urlencode( $this->security_code );
	    }
    	if ( !empty( $this->guid ) ) {
		    return get_permalink() . "/?evoucher=" . $this->guid . $id . $security_code;   
		}
		return false;
	}
}