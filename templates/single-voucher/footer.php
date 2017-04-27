<?php
/**
 * Single Voucher footer
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/single-voucher/footer.php.
 *
 * @author     Jose A. Salim
 * @package    EVoucherWP/Templates
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $voucher;

$footer_content = '';

if ( !empty( $voucher ) ){
	$footer_content = $voucher->footer_content;
}

if ( empty( $footer_content ) ){
	$footer_content = get_option( 'evoucherwp_footer_content', '' );
}

?>

<div class="voucher-footer">

	<?php do_action( 'evoucherwp_voucher_before_footer' ); ?>

	<?php if ( !empty( $footer_content ) ): ?> 

		<div class="footer-content">
			<?php echo $footer_content; ?> 
		</div>

	<?php endif; ?>

	<?php do_action( 'evoucherwp_voucher_after_footer' ); ?>

</div>