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

//TODO: doc-me
function render_voucher( $voucher_id ){
    require_once( 'externals/simple_html_dom.php' );

    $voucher = new EVWP_Voucher( $voucher_id );

    if ( isset( $voucher ) ){
        $template_id = $voucher->get_template();
        if ( ! empty( $template_id ) ){
            $template = new EVWP_Voucher_Template( $template_id );
            if ( ! empty( $template ) ){
                $fields = $voucher->get_fields();
                $html = str_get_html( $template->get_post_data()->post_content );
                $elems = $html->find('span[id^=_field_], img[id^=_field_]');
                foreach ( $elems as $elem ) {
                    $id = $elem->id;
                    switch ( $elem->getAttribute( 'data-type' ) ) {
                        case 'text':
                            if ( isset( $fields->$id ) ){
                                $html->find( '#' . $id, 0 )->innertext = esc_html( $fields->$id );
                            }
                            break;
                        case 'img':
                            if ( isset( $fields->$id ) ){
                                $img_src = wp_get_attachment_url( $fields->$id );
                                $html->find( '#' . $id, 0 )->src = esc_url( $img_src );
                            }
                            break;
                        case 'guid':
                            $prefix = $voucher->codeprefix;
                            $suffix = $voucher->codesuffix;
                            foreach ( $html->find( '#' . $id ) as $elem ) {
                                $elem->innertext = esc_html( $prefix . $voucher->guid . $suffix );
                            }
                            break;
                        case 'date':
                            $df = $elem->getAttribute( 'data-df' );    
                            $date = 0;
                            if ( $id == '_field_startdate' ){
                                $date = $voucher->startdate;
                            }
                            elseif ( $id == '_field_expirydate' ){
                                $date = $voucher->expiry;
                            }

                            if ( $date > 0 ){
                                foreach ( $html->find( '#' . $id ) as $elem ) {
                                    $elem->innertext = date_i18n( $df, $date );
                                }
                            }
                            else{
                                foreach( $html->find( '#' . $id ) as $elem) {
                                    $elem->innertext = '';   
                                }
                            }
                            break;
                    }
                }

                $str_html = $html->save();
                $css = get_post_meta( $template->id, '_template_css', true );
                $css .= '.button{ color: #fff;
                        background-color: #337ab7;
                        border-color: #2e6da4;
                        display: inline-block;
                        padding: 6px 12px;
                        margin-bottom: 0;
                        font-size: 14px;
                        font-weight: 400;
                        line-height: 1.42857143;
                        text-align: center;
                        white-space: nowrap;
                        vertical-align: middle;
                        border: 1px solid transparent;
                        border-radius: 4px;';
                echo '<html><head><style>' . esc_attr( $css ) . '</style></head><body>' . $str_html . '</body></html>';
                $html->clear();

                echo '<a  class="button" href="javascript:window.print()">' . __('Print E-Voucher', 'evoucherwp' ) . '</a>';
            }
        }
    }
}
add_action( 'evoucherwp_create_voucher_html', 'render_voucher', 10 );



// check a code address is valid for a voucher
function voucher_is_valid( $voucher_id, $voucher_guid, $security_code ) {
    
    if ( empty( $voucher_id) || empty( $voucher_guid ) || empty( $security_code ) ){
        return false;
    }
    
    global $wpdb;
    $prefix = $wpdb->prefix;
    $voucher = new EVWP_Voucher( $voucher_id );
    if ( $voucher->guid === $voucher_guid && $voucher->security_code === $security_code ){
    	return $voucher->is_valid();
    }
    return "unavailable";
}

// check if the site is using pretty URLs
function evwp_pretty_urls() {
    $structure = get_option( "permalink_structure" );
    if ( "" != $structure || false === strpos( $structure, "?" ) ) {
        return true;
    }
    return false;
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
