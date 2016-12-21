<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'EVoucherWP ') ):

require_once('externals/tcpdf/config/lang/eng.php');
require_once('externals/tcpdf/tcpdf.php');
require_once( 'externals/simple_html_dom.php' );

/**
 * Voucher Class.
 *
 *
 * @class 		EVWP_Voucher_PDF_Factory
 * @version		1.0.0
 * @package		EVoucherWP/Classes/Vouchers
 * @category	Class
 * @author 		Jose A. Salim
 */
class EVWP_Voucher_PDF_Factory  {

	public static function create_pdf( $voucher ){
		if ( is_numeric( $voucher ) ) {
			$thevoucher = new EVWP_Voucher( $voucher );
		} elseif ( $voucher instanceof EVWP_Voucher ) {
			$thevoucher = $voucher;
		} elseif ( isset( $voucher->ID ) ) {
			$thevoucher = new EVWP_Voucher( $voucher->ID );
		}
        else{
            return;
        }

		if ( isset( $thevoucher ) ){
			$template_id = $thevoucher->template;
            if ( ! empty( $template_id ) ){
                $template = EVWP_Voucher_Template( $template_id );
                if ( ! empty( $template ) ){
                    $fields = $thevoucher->get_fields();
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

                    generate_pdf( $html, $thevoucher );
                }
            }
		}
	}

    static function generate_pdf( $html, $voucher ){
        $pdf = new TCPDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor( $voucher->get_post_data()->post_author );
        $pdf->SetTitle(  $voucher->get_post_data()->post_title );

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('dejavusans', '', 10);

        // add a page
        $pdf->AddPage();

        // output the HTML content
        $str_html = $html;
        $pdf->writeHTML( $str_html, false, false, false, false, '');


    }

	// render a voucher
function voucherpress_render_voucher( $voucher, $code ) {

    global $current_user;
    // get the voucher template image
    if ( voucherpress_template_exists( $voucher->template ) ) {
        // get the current memory limit
        $memory = ini_get( 'memory_limit' );

        // try to set the memory limit
        //@ini_set( 'memory_limit', '64mb' );

        $slug = voucherpress_slug( $voucher->name );

        header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
        header( "Cache-Control: no-store, no-cache, must-revalidate" );
        header( "Cache-Control: post-check=0, pre-check=0", false );
        header( "Pragma: no-cache" );
        header( 'Content-type: application/octet-stream' );
        header( 'Content-Disposition: attachment; filename="' . $slug . '.pdf"' );

        // include the TCPDF class and VoucherPress PDF class
        require_once("voucherpress_pdf.php");

        // create new PDF document
        $pdf = new voucherpress_pdf( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

        // set the properties
        $pdf->voucher_image = plugin_dir_path( __FILE__ ) . 'templates/' . $voucher->template . '.jpg';
        $pdf->voucher_image_w = 200;
        $pdf->voucher_image_h = 90;
        $pdf->voucher_image_dpi = 150;

        // set document information
        $pdf->SetCreator( PDF_CREATOR );
        $pdf->SetAuthor( $current_user->user_nicename );
        $pdf->SetTitle( $voucher->name );

        // set header and footer fonts
        $pdf->setHeaderFont( Array( PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN ) );

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

        //set margins
        $pdf->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
        $pdf->SetHeaderMargin( 0 );
        $pdf->SetFooterMargin( 0 );

        // remove default footer
        $pdf->setPrintFooter( false );

        //set auto page breaks
        $pdf->SetAutoPageBreak( TRUE, PDF_MARGIN_BOTTOM );

        //set image scale factor
        $pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );

        //set some language-dependent strings
        $pdf->setLanguageArray( $l );

        // set top margin
        $pdf->SetTopMargin( 15 );

        // add a page
        $pdf->AddPage( 'L', array( 200, 90 ) );

        // set title font
        $pdf->SetFont( $voucher->font, '', 32 );
        // print title
        $pdf->writeHTML( stripslashes( $voucher->name), $ln = true, $fill = false, $reseth = false, $cell = false, $align = 'C' );

        // set text font
        $pdf->SetFont( $voucher->font, '', 18 );
        // print text
        $pdf->WriteHTML( stripslashes( $voucher->text ), $ln = true, $fill = false, $reseth = false, $cell = false, $align = 'C');

        $registered_name = "";
        if ( "" != $voucher->registered_name ) {
            $registered_name = __( "Registered to:", "voucherpress" ) . " " . stripslashes( $voucher->registered_name ) . ": ";
        }

        // set code font
        $pdf->SetFont( $voucher->font, '', 14 );
        // print code
        $pdf->Write( 10, $registered_name . $code, $link = '', $fill = 0, $align = 'C', $ln = true );

        // get the expiry, if it exists
        $expiry = "";
        if ( "" != $voucher->expiry && 0 < ( int ) $voucher->expiry ) {
            $expiry = " " . __( "Expiry:", "voucherpress" ) . " " . date( "Y/m/d", $voucher->expiry );
        }

        // set terms font
        $pdf->SetFont( $voucher->font, '', 10 );
        // print terms
        $pdf->Write( 5, stripslashes( $voucher->terms ) . $expiry, $link = '', $fill = 0, $align = 'C', $ln = true );

        // close and output PDF document
        $pdf->Output( $slug . '.pdf', 'D' );

        // try to set the memory limit back
        //@ini_set( 'memory_limit', @memory );

        exit();
    } else {

        return false;
    }
}

}

endif;