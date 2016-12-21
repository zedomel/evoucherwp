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

function handle_voucher_request() {
    // if requesting a voucher
    if ( isset( $_GET["evoucher"] ) && "" != $_GET["evoucher"] ) {
        // get the details
        $voucher_guid = $_GET["evoucher"];
        $security_code = $_GET["sc"];
        $voucher_id = $_GET["id"];

        // check the template exists
        $status = voucher_is_valid( $voucher_id, $voucher_guid, $security_code );
        if ( $status  === 'valid' ) {
            //download_voucher( $voucher_id );
            render_voucher( $voucher_id );
        }
        elseif ( $status === 'unregistered' ) {
            // show the form
            register_form( $voucher_guid );
        }
        else{
            evwp_404( $status );
        }
        exit();
    }
    
}
add_action( "template_redirect", "handle_voucher_request" );

function render_voucher( $voucher_id ){
    require_once( 'admin/externals/simple_html_dom.php' );

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
                    if ( isset( $fields->$id ) ){

                        switch ( $elem->tag ) {
                            case 'span':
                                if ( $id == '_field_guid' ){
                                    $elem->plaintext = esc_html( $thevoucher->guid );
                                }
                                else{
                                    $elem->plaintext = esc_html( $fields->$id );
                                }
                                break;
                            case 'img':
                                $img_src = wp_get_attachment_url( $fields->$id );
                                $elem->src = esc_url( $img_src );
                                break;
                        }
                    }
                }

                $str_html = $html;
                echo generate_html_page( $str_html, $voucher, $template );
            }
        }
    }
}

function generate_html_page( $html, $voucher, $template ){
    $css = get_post_meta( $template->id, '_template_css', true );
    return '<html><head><style>' . esc_attr( $css ) . '</style></head><body>' . $html . '</body></html>';
}


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

// download a voucher
function download_voucher( $voucher_id ) {
    
    $voucher = new EVWP_Voucher( $voucher_id );
    if ( is_object( $voucher ) && 1 == $voucher->live && !empty( $voucher->template ) && evwp_template_exists( $voucher->template ) ) {
    
        // if this is not a standard POST/GET request then just return the headers
        if ( strtolower( $_SERVER['REQUEST_METHOD'] ) != 'post' && strtolower( $_SERVER['REQUEST_METHOD'] ) != 'get' ) {
            $slug = voucherpress_slug( $voucher->get_title() );
            header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
            header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
            header( "Cache-Control: no-store, no-cache, must-revalidate" );
            header( "Cache-Control: post-check=0, pre-check=0", false );
            header( "Pragma: no-cache" );
            header( 'Content-type: application/octet-stream' );
            header( 'Content-Disposition: attachment; filename="' . $slug . '.pdf"' );
            return;
        }
    
        ob_start();
        if ( strlen( trim( ob_get_contents() ) ) == 0 ) {
            
            // set this download as completed
            evoucherwp_register_download( $voucher );

            do_action( "evoucherwp_download", $voucher );

            // render the voucher
            //EVoucherWP()->pdf_factory->generate_pdf( $voucher );
        
        } else {
            headers_sent();
        }
        ob_end_flush();

    } else {
        // this voucher is not available
        print "<!-- The voucher could not be found -->";
        evwp_404( false );
    }
}

function evwp_template_exists( $template_id ){
	$template = new EVWP_Voucher_Template( $template_id );
	return $template->exists();
}

// person functions
// show the registration form
function register_form( $voucher, $plain = false ) {

    $out = "";
    $showform = true;

    if ( !$plain ) {
        get_header();
        echo '<div id="content" class="narrowcolumn" role="main">
        <div class="post category-uncategorized" id="voucher-' . $voucher->guid . '">';
    }

    // if registering
    if ( "" != @$_POST["_email"] && "" != @$_POST["_name"] ) {

        // if the email address is valid
        if ( is_email( trim( $_POST["_email"] ) ) ) {

            // register the email address
            $download_guid = save_download( $voucher, trim( $_POST["_email"] ), trim( $_POST["_name"] ) );
            // if the guid has been generated
            if ( !empty( $download_guid ) ) {

                echo '<p>' . sprintf( __( 'Thank you for registering. You will shortly receive the download link of your voucher in the e-mail %s. Please, check your inbox', 'evoucherwp' ), trim( $_POST["_email"] ) ) . "\n\n" . $voucher->get_download_url( false ) . '</p>';

                // send the email
                wp_mail( trim( $_POST["_email"] ), $voucher->get_title() . " for " . trim( $_POST["_name"] ), $message );

                do_action( "evoucherwp_register", $voucher, $_POST["_email"], $_POST["_name"], $message );

                $showform = false;
            } else {
                echo '<p>' . __( "Sorry, we can't process your registration. Have you already registered to download this e-voucher? Please try again.", "evoucherwp" ) . '</p>';
            }
        } else {
            echo  '<p>' . __( 'Sorry, provide a valid e-mail address. Please try again.', 'evoucherwp' ) . '</p>';
        }
    }

    if ( $showform ) {
            echo '<h2>' . __( "Please provide some details", "voucherpress" ) . '</h2>';
			echo '<p>' . __( "To download this voucher you must provide your name and email address. You will then receive a link by email to download your personalised voucher.", "voucherpress" ) . '</p>';
			echo '<form action="' . esc_url( $voucher->get_download_url() ) . '" method="post" class="voucherpress_form">';

        echo '<p><label for="voucher_email">' . __( "Your e-mail:", "evoucherwp" ) . '</label>';
		echo '<input type="text" name="_email" id="_email" value="' . trim( @$_POST[ '_email' ] ) . '" /></p>';
		echo '<p><label for="_name">' . __( "Your full name", "evoucherwp" ) . '</label>';
		echo '<input type="text" name="_name" id="_name" value="' . trim( @$_POST[ '_name' ] ) . '" /></p>';
		echo '<p><input type="submit" name="voucher_submit" id="voucher_submit" value="' . __( "Register to download the e-voucher", "evoucherwp" ) . '" /></p></form>';
    }

    if ( !$plain ) {
        echo '</div></div>';
        get_footer();
    }
}

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
    //if ( file_exists( TEMPLATEPATH.'/404.php' ) ) {
    //  require TEMPLATEPATH.'/404.php';
    //} else {
    if ( $found ) {
        wp_die( __( "Sorry, that item is not available", "evoucherwp" ) );
    } else {
        wp_die( __( "Sorry, that item was not found", "evoucherwp" ) );
    }
    //}
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
