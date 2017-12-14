<?php

/**
 * EVoucherWP Template
 *
 * Functions for the templating system.
 *
 * File based in: 
 *
 * @author   Jose A. Salim
 * @package  EVoucherWP/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
}

function evoucherwp_voucher_status( $status, $voucher ){
	if ( $status === 'unregistered' ){
		if ( isset( $_POST[ '_email'] ) && isset( $_POST[ '_name' ] ) ){
			$email = sanitize_email( $_POST[ '_email' ] );
			$name = sanitize_text_field( $_POST[ '_name' ] );
			if ( !empty( $email ) && !empty( $name ) ){
				// TODO: add to downloads table
				$status = 'valid';
				return $status;
			}
		}
	}
	return $status;
}

function evoucherwp_show_download_form( $voucher ){
	if ( !empty( $voucher ) ){

		$email = isset(  $_POST[ '_email' ] ) ? sanitize_email( $_POST[ '_email' ] ) : '';
	    $name =  isset( $_POST[ '_name' ] ) ? sanitize_text_field( $_POST[ '_name' ] ) : '' ;

        echo '<h2>' . __( "E-mail Required", "evoucherwp" ) . '</h2>';
        echo '<p>' . __( "To download this voucher you must provide your name and email address. You will then allowed to view this voucher.", "evoucherwp" ) . '</p>';
        echo '<form id="evwp-download-form" action="' . esc_url( $voucher->get_download_url() ) . '" method="post" class="evoucherwp-form">';
        echo '<p><label for="_email">' . __( "Your e-mail:", "evoucherwp" ) . '</label>';
        echo '<input type="text" name="_email" id="_email" value="' . esc_attr( $email ) . '"/></p>';
        echo '<p><label for="_name">' . __( "Your full name", "evoucherwp" ) . '</label>';
        echo '<input type="text" name="_name" id="_name" value="' . esc_attr( $name ) . '" /></p>';
        echo '<p><input type="submit" name="evwp_submit" id="evwp-submit" value="' . __( "Register to access the e-voucher", "evoucherwp" ) . '" /></p></form>';
    }
}

// check a code address is valid for a voucher
function evwp_voucher_is_valid( $voucher, $guid ) {
    
    if ( empty( $voucher ) ){
        return false;
    }
    
    if ( is_numeric( $voucher ) ){
	    $voucher = new EVWP_Voucher( $voucher );
	}

    if ( $voucher->guid === $guid ){
    	return $voucher->is_valid();
    }
    return "unavailable";
}

// function evoucherwp_load_script_for_template(){
// 	if ( is_voucher() ){
// 		wp_enqueue_script( 'jquery' );
// 	}
// }

function is_voucher(){
	global $post;
	return !empty( $post ) ? $post->post_type === 'evoucher' && is_single() : false;
}


// function evoucherwp_print_scripts_and_styles(){
// 	wp_enqueue_script( 'jquery' );
// 	echo '<link rel="stylesheet" type="text/css" href="' . esc_url( EVoucherWP()->plugin_url() . '/assets/css/evoucherwp-single-voucher.css' ) . '"/>';
// 	echo '<script type="text/javascript" id="evoucherwp-single-voucher-script" src="' . esc_url( EVoucherWP()->plugin_url() . '/assets/js/evoucherwp-single-voucher.js' ) . '"></script>';
// }


/**
 * Get template part
 *
 *
 * @access public
 * @param mixed $slug
 * @param string $name (default: '')
 */
function evwp_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/evoucherwp/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", EVoucherWP()->template_path() . "{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( EVoucherWP()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = EVoucherWP()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/evoucherwp/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", EVoucherWP()->template_path() . "{$slug}.php" ) );
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'evwp_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get other templates passing attributes and including the file.
 *
 * @access public
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function evwp_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = evwp_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );
		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'evwp_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'evoucherwp_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'evoucherwp_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @access public
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function evwp_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	
	if ( ! $template_path ) {
		$template_path = EVoucherWP()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = EVoucherWP()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template/
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'evoucherwp_locate_template', $template, $template_name, $template_path );
}

/**
 * When the_post is called, put voucher data into a global.
 *
 * @param mixed $post
 * @return EVWP_Voucher
 */
function evwp_setup_voucher_data( $post ) {
	unset( $GLOBALS['voucher'] );

	if ( is_int( $post ) )
		$post = get_post( $post );

	if ( empty( $post->post_type ) || ! in_array( $post->post_type, array( 'evoucher' ) ) )
		return;
	
	$GLOBALS['voucher'] = new EVWP_Voucher( $post );

	return $GLOBALS['voucher'];
}
add_action( 'the_post', 'evwp_setup_voucher_data' );

/**
 * Get the placeholder image URL for vouchers.
 *
 * @access public
 * @return string
 */
function evwp_placeholder_img_src() {
	return apply_filters( 'evoucherwp_placeholder_img_src', EVoucherWP()->plugin_url() . '/assets/images/placeholder.png' );
}

if ( ! function_exists( 'evoucherwp_get_evoucher_schema' ) ) {

	/**
	 * Get a vouchers Schema.
	 * @return string
	 */
	function evoucherwp_get_evoucher_schema() {
		return 'http://schema.org/Product';
	}
}


/** Single Voucher ********************************************************/

if ( ! function_exists( 'evoucherwp_show_voucher_image' ) ) {
	/**
	 * Output the voucher image before the single voucher summary.
	 *
	 */
	function evoucherwp_show_voucher_image() {
		evwp_get_template( 'single-voucher/voucher-image.php' );
	}
}

if ( ! function_exists( 'evoucherwp_template_single_title' ) ) {
	/**
	 * Output the voucher title
	 *
	 */
	function evoucherwp_template_single_title() {
		evwp_get_template( 'single-voucher/title.php' );
	}
}

if ( ! function_exists( 'evoucherwp_template_single_meta' ) ) {
	/**
	 * Output the voucher meta.
	 *
	 */
	function evoucherwp_template_single_meta() {
		evwp_get_template( 'single-voucher/voucher-meta.php' );
	}
}

if ( ! function_exists( 'evoucherwp_output_voucher_content' ) ) {
	/**
	 * Output the voucher content.
	 *
	 */
	function evoucherwp_output_voucher_content() {
		evwp_get_template( 'single-voucher/content.php' );
	}
}

if ( ! function_exists( 'evoucherwp_voucher_header' ) ) {
	/**
	 * Output the voucher header.
	 *
	 */
	function evoucherwp_voucher_header() {
		evwp_get_template( 'single-voucher/header.php' );
	}
}

if ( ! function_exists( 'evoucherwp_voucher_footer' ) ) {
	/**
	 * Output the voucher footer.
	 *
	 */
	function evoucherwp_voucher_footer() {
		evwp_get_template( 'single-voucher/footer.php' );
	}
}

?>