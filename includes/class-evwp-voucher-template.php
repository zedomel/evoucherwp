<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Voucher Template Class.
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-product.php
 *
 *
 * @class 		EVWP_Voucher_Template
 * @version		1.0.0
 * @package		EVoucherWP/Classes/Vouchers
 * @category	Class
 * @author 		Jose A. Salim
 */
class EVWP_Voucher_Template {

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
	 * Initialize template.
	 *
	 * @param mixed $voucher
	 */
	public function __construct( $template ) {
		if ( is_numeric( $template ) ) {
			$this->id   = absint( $template );
			$this->post = get_post( $this->id );
		} elseif ( $template instanceof EVWP_Voucher_Template ) {
			$this->id   = absint( $template->id );
			$this->post = $template->post;
		} elseif ( isset( $template->ID ) ) {
			$this->id   = absint( $template->ID );
			$this->post = $template;
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
		if ( in_array( $key, array( 'fields' ) ) ) {
			$value = $value ? $value : array();
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
}
