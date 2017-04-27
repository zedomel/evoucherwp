<?php
/**
 * EVoucherWP Template Hooks
 *
 * Action/filter hooks used for EVoucherWP functions/templates.
 *
 * @author 		Jose A. Salim
 * @category 	Core
 * @package 	EVoucherWP/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


add_action( 'evoucherwp_before_single_voucher_summary', 'evoucherwp_template_single_title', 5 );

add_action( 'evoucherwp_single_voucher_summary', 'evoucherwp_show_voucher_image', 20 );

add_action( 'evoucherwp_single_voucher_summary', 'evoucherwp_template_single_meta', 20 );

add_action( 'evoucherwp_after_single_voucher_summary', 'evoucherwp_output_voucher_content', 10 );

add_action( 'evoucherwp_voucher_header', 'evoucherwp_voucher_header', 10 );

add_action( 'evoucherwp_voucher_footer', 'evoucherwp_voucher_footer', 10 );

add_filter( 'evoucherwp_voucher_status', 'evoucherwp_voucher_status', 10, 2 );

add_action( 'evoucherwp_show_download_form', 'evoucherwp_show_download_form', 10, 2 );