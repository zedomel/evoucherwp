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
	public function __construct( $voucher = 0 ) {
		$this->init( $voucher );
	}

	protected function init( $voucher = 0){
		if ( is_numeric( $voucher ) ) {
			$this->id   = absint( $voucher );
			$this->post = get_post( $voucher );
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
		return metadata_exists( 'post', $this->id, '_evoucherwp_' . $key );
	}

	/**
	 * __get function.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get( $key ) {
		$value = get_post_meta( $this->id, '_evoucherwp_' . $key, true );

		// Get values or default if not set
		if ( in_array( $key, array( 'guid', 'codestype', 'codeprefix', 'codesuffix', 
			'singlecode', 'header_image', 'header_title' ) ) ) {
			$value = $value ? $value : '';
		} elseif ( in_array( $key , array( 'expiry', 'startdate', 'codelength' ) ) ){
			$value = $value ? intval( $value ) : 0;
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
	 * Returns whether or not the voucher post exists.
	 *
	 * @return bool
	 */
	public function exists() {
		return empty( $this->post ) ? false : true;
	}

	public function is_valid(){
		if ( empty( $this->guid ) ){
	        return false;
	    }
        // if there is an expiry and the expiry is in the past
        if ( 0 < $this->expiry && time() >= $this->expiry ) {
            return "expired";
        }
        // if there is a start date and the start date is in the future
        if ( 0 < $this->startdate && time() <  $this->startdate ) {
            return "notyetavailable";
        }
        
        return $this->live === 'yes' ? 'valid' : 'unavailable';
        
        // // if emails are not required
        // if ( $this->requireemail === 'no' ) {
        //     return  $this->live === 'yes' ? 'valid' : 'unavailable' ;
        // } 
        // elseif ( $this->live === 'yes' ) {
        //     return 'unregistered';
        // }
        // return 'unavailable';
	}

	public function can_change(){
		$elapsed_days = intval( ( time() - $this->startdate ) / 60 / 60 / 24 );
		$max_days_to_change = intval( get_option( 'evoucherwp_days_to_change', 0 ) );
		$expired = time() > $this->expiry && $this->expiry > 0;
		return ( $this->live && ! $expired && $elapsed_days < $max_days_to_change );
	}

	public function get_download_url(){
    	if ( !empty( $this->guid ) ) {
		    return get_permalink( $this->id ) . "?evoucher=" . $this->guid;
		}
		return false;
	}
}