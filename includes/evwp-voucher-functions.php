<?php
/**
 * EVoucherWP Voucher Functions
 *
 * Functions for voucher specific things.
 *
 * @author   Jose A. Salim
 * @category Core
 * @package  EVoucherWP/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display a EVoucherWP help tip.
 *
 * @since  1.0.0
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 * @return string
 */
function evwp_help_tip( $tip, $allow_html = false ) {
    if ( $allow_html ) {
        $tip = evwp_sanitize_tooltip( $tip );
    } else {
        $tip = esc_attr( $tip );
    }

    return '<span class="evoucherwp-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * @param string $var
 * @return string
 */
function evwp_sanitize_tooltip( $var ) {
    return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
        'br'     => array(),
        'em'     => array(),
        'strong' => array(),
        'small'  => array(),
        'span'   => array(),
        'ul'     => array(),
        'li'     => array(),
        'ol'     => array(),
        'p'      => array(),
    ) ) );
}

// check if the site is using pretty URLs
function evwp_pretty_urls() {
    $structure = get_option( "permalink_structure" );
    if ( "" != $structure || false === strpos( $structure, "?" ) ) {
        return true;
    }
    return false;
}


function evwp_generate_voucher_code( $post_id ){

    $code_type = get_post_meta( $post_id, '_evoucherwp_codestype', true );
    if ( empty( $code_type ) ){
        $code_type = get_option( 'evoucherwp_codestype', 'random' );
    }

    $code = '';

    if ( $code_type === 'single' ){
        $code = get_post_meta( $post_id, '_evoucherwp_singlecode', true );
        if ( empty( $code ) ){
            $code = get_option( 'evoucherwp_singlecode', '' );
        }
    }
    else{
        $length = get_post_meta( $post_id, '_evoucherwp_codelength', true );
        // Fix length if empty or <= 0
        if ( empty( $length ) || $length <= 0)
            $length = intval( get_option( 'evoucherwp_codelength', 6 ) );
        else
            $length = intval( $length );

        if ( $code_type == 'sequential' ){
            $code = evwp_generate_sequence_code( $post_id, $length );
        }
        elseif ( $code_type == 'random' ){
            $code = evwp_generate_guid( $length );
        }
    }

    return $code;
}

function evwp_generate_sequence_code( $post_id, $length ){
    global $wpdb;

    if ( $wpdb->insert( $wpdb->prefix . 'evoucherwp_code_seq', array( 'post_id' => $post_id ), array( '%d' ) ) ){
        $code = $wpdb->insert_id;
        return str_pad( $code, $length, '0', STR_PAD_LEFT );
    }
    return false;
}

function evwp_delete_sequence_code( $post_id ){
    global $wpdb;
    return $wpdb->delete( $wpdb->prefix . 'evoucherwp_code_seq', array( 'post_id' => $post_id ), array( '%d' ) );
}

// create an md5 hash of a guid
// from http://php.net/manual/en/function.com-create-guid.php
function evwp_generate_guid( $length = 6 ) {
    if ( function_exists( 'com_create_guid' ) ) {
        return substr( md5( str_replace( "{", "", str_replace( "}", "", com_create_guid() ) ) ), 0, $length );
    } else {
        mt_srand( ( double ) microtime() * 10000 );
        $charid = strtoupper( md5( uniqid( rand(), true ) ) );
        $hyphen = chr( 45 );
        $uuid =
                substr( $charid, 0, 8 ) . $hyphen
                . substr( $charid, 8, 4 ) . $hyphen
                . substr( $charid, 12, 4 ) . $hyphen
                . substr( $charid, 16, 4 ) . $hyphen
                . substr( $charid, 20, 12 );
        return substr( md5( str_replace( "{", "", str_replace( "}", "", $uuid ) ) ), 0, $length );
    }
}

function evwp_template_exists( $template_id ){
	$template = new EVWP_Voucher_Template( $template_id );
	return $template->exists();
}

/**
*/
function registered_message( $voucher_id, $success, $email = '', $name = '' ){

    if ( $success === true ){
        echo '<p>' . sprintf( __( 'Thank you for registering. You will shortly receive the download link of your voucher in the e-mail %s. Please, check your inbox', 'evoucherwp' ), trim( $email ) ) . '</p>';
    }
    else{
        echo '<p>' . __( "Sorry, we can't process your registration. Have you already registered to download this e-voucher? Please try again.", "evoucherwp" ) . '</p>';
    }
}
add_action( 'evoucherwp_registered', 'registered_message', 10, 3 );

// person functions
// show the registration form
function register_form( $voucher_id, $email = '', $name = '' ) {

    $voucher = new EVWP_Voucher( $voucher_id );
    if ( isset( $voucher ) ){
        echo '<h2>' . __( "Please provide some details", "voucherpress" ) . '</h2>';
        echo '<p>' . __( "To download this voucher you must provide your name and email address. You will then receive a link by email to download your personalised voucher.", "voucherpress" ) . '</p>';
        echo '<form action="' . esc_url( $voucher->get_download_url() ) . '" method="post" class="voucherpress_form">';

        echo '<p><label for="voucher_email">' . __( "Your e-mail:", "evoucherwp" ) . '</label>';
        echo '<input type="text" name="_email" id="_email" value="' . trim( $email ) . '" /></p>';
        echo '<p><label for="_name">' . __( "Your full name", "evoucherwp" ) . '</label>';
        echo '<input type="text" name="_name" id="_name" value="' . trim( $name ) . '" /></p>';
        echo '<p><input type="submit" name="voucher_submit" id="voucher_submit" value="' . __( "Register to download the e-voucher", "evoucherwp" ) . '" /></p></form>';
    }
}
add_action( 'evoucherwp_voucher_form', 'register_form', 10, 3 );

// save to download name and email address
function save_download( $voucher, $email, $name ) {
    global $wpdb;
    $prefix = $wpdb->prefix;

    // if the id has been found
    if ( $voucher->exists() ) {

        // if the email address has already been registered
        $sql = $wpdb->prepare( "SELECT guid FROM {$prefix}evoucherwp_downloads WHERE id = %d and email = %s;", $voucher->id, $email );
        $download_guid = $wpdb->get_var( $sql );

        if (  empty( $download_guid ) ) {

            // get the IP address
            $ip = get_user_ip();

            // create the guid
            $download_guid = evwp_generate_guid( 6 );

            // insert the new download
            $sql = $wpdb->prepare( "INSERT INTO {$prefix}evoucherwp_downloads (voucher_id, time, email, name, ip, guid) VALUES (%d, %d, %s, %s, %s, %s)", $voucherid, time(), $email, $name, $ip, $download_guid );
            $wpdb->query( $sql );
        }

        return $download_guid;
    }
    return false;
}

// get the users IP address
// from http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
function get_user_ip() {
    if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {   //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {   //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// show a 404 page
function evwp_404( $found = true ) {
    global $wp_query;
    $wp_query->set_404();
    if ( file_exists( get_stylesheet_directory() .'/404.php' ) ) {
      require get_stylesheet_directory() .'/404.php';
    }
    
    if ( $found ) {
        wp_die( '<h1>' . __( "Sorry, that voucher is not available", "evoucherwp" ) . '</h1>');
    } else {
        wp_die( __( "Sorry, that voucher was not found", "evoucherwp" ) );
    }
    exit();
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * @param string|array $var
 * @return string|array
 */
function evwp_clean( $var ) {
    if ( is_array( $var ) ) {
        return array_map( 'evwp_clean', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }
}
