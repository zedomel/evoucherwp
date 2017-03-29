<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVoucherWP EVWP_AJAX.
 *
 * AJAX Event Handler.
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-ajax.php
 *
 * @class    EVWP_AJAX
 * @version  1.0.0
 * @package  EvoucherWP/Classes
 * @category Class
 * @author   Jose A. Salim
 */
class EVWP_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_evwp_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Get EVWP Ajax Endpoint.
	 * @param  string $request Optional
	 * @return string
	 */
	public static function get_endpoint( $request = '' ) {
		return esc_url_raw( apply_filters( 'evoucherwp_ajax_get_endpoint', add_query_arg( 'evwp-ajax', $request ), $request ) );
	}

	/**
	 * Set EVWP AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['evwp-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'EVWP_DOING_AJAX' ) ) {
				define( 'EVWP_DOING_AJAX', true );
			}
			// Turn off display_errors during AJAX events to prevent malformed JSON
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for EVWP Ajax Requests
	 * @since 1.0.0
	 */
	private static function evwp_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Check for EVWP Ajax request and fire action.
	 */
	public static function do_evwp_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['evwp-ajax'] ) ) {
			$wp_query->set( 'evwp-ajax', sanitize_text_field( $_GET['evwp-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'evwp-ajax' ) ) {
			self::evwp_ajax_headers();
			do_action( 'evwp_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// evoucherwp_EVENT => nopriv
		$ajax_events = array();

		$ajax_events = apply_filters( 'evoucherwp_ajax_events', $ajax_events );

		foreach ( $ajax_events as $ajax_event => $prop ) {
			add_action( 'wp_ajax_evoucherwp_' . $ajax_event, array( $prop[ 'class' ], $ajax_event ) );
			
			if ( $prop[ 'nopriv' ] ) {
				add_action( 'wp_ajax_nopriv_evoucherwp_' . $ajax_event, array( $prop[ 'class' ], $ajax_event ) );

				// AJAX can be used for frontend ajax requests
				add_action( 'evwp_ajax_' . $ajax_event, array( $prop[ 'class' ], $ajax_event ) );
			}
		}
	}
}
