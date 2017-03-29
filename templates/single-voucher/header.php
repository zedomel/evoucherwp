<?php
/**
 * Single Voucher header
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/single-voucher/header.php.
 *
 * @author     Jose A. Salim
 * @package    EVoucherWP/Templates
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $voucher;

$header_img = '';
$header_title = '';

if ( !empty( $voucher ) ){
	$header_img = $voucher->header_image;
	$header_title = $voucher->header_title;
}

if ( empty( $header_img ) ){
	$header_img = get_option( 'evoucherwp_header_image', '' );
}

if ( empty( $header_title ) ){
	$header_title = get_option( 'evoucherwp_header_title', '' );
}

?>

<div class="voucher-header">

	<?php do_action( 'evoucherwp_voucher_header_before_image' ); ?>

	<?php if ( !empty( $header_img ) ): ?> 

		<div class="header-image">
		
			<?php echo sprintf( '<img src="%s"/>', esc_url( $header_img ) ); ?> 
		</div>

	<?php endif; ?>

	<?php do_action( 'evoucherwp_voucher_header_before_title' ); ?>
	
	<?php if ( !empty( $header_title ) ): ?>
		<div class="header-title">
		
		<?php echo sprintf( '<h1>%s</h1>', esc_html( $header_title ) );  ?>
		</div>
	<?php endif; ?>
</div>
