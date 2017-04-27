<?php
/**
 * The Template for displaying all single vouchers
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/single-evoucher.php.
 *
 * HOWEVER, on occasion EVoucherWP will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		Jose A. Salim
 * @package 	EVoucherWP/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $voucher;

// Call the_post() to get EVWP_Voucher object
if ( have_posts() ){
	the_post();
}

// if requesting a voucher
$status = 'unavaiable';
if ( isset( $_GET[ 'evoucher' ] ) && ! empty( $_GET[ 'evoucher' ] ) ) {
    // get the details
    $voucher_guid = sanitize_text_field( $_GET[ 'evoucher' ] );

    // check the template exists
    $status = evwp_voucher_is_valid( $voucher, $voucher_guid );
}

?> 

<head>
	<?php wp_head(); ?>

</head>

<?php

// Check if general settings 
if ( $status === 'valid' ){
	$op_require_email = get_option( 'evoucherwp_requireemail', 'no' );
	if ( $op_require_email === 'yes' || $voucher->requireemail === 'yes' ){
		$status = 'unregistered';
	}	
}

$status = apply_filters( 'evoucherwp_voucher_status', $status, $voucher );

if ( $status === 'valid' ):

do_action( 'evoucherwp_voucher_header' );

?>

	<div id="container">
		<div id="content" role="main">

	<?php
		/**
		 * evoucherwp_before_main_content hook.
		 */
		do_action( 'evoucherwp_before_main_content' );

		evwp_get_template_part( 'content', 'single-evoucher' );

		/**
		 * evoucherwp_after_main_content hook.
		 */
		do_action( 'evoucherwp_after_main_content' );
	?>

		</div>
	</div>

<?php do_action( 'evoucherwp_voucher_footer' ); ?>


<?php 

/** Unregistered download */
elseif ( $status === 'unregistered' ):

	get_header();
	?>
	<div id="content" class="site-content">
		<div class="container">

	<?php
		do_action( 'evoucherwp_show_download_form', $voucher );
	?>

	</div>

	<?php
	get_footer();
else:
	evwp_404();
endif;
?>
