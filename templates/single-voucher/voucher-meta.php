<?php
/**
 * Single Voucher Meta
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/single-voucher/voucher-meta.php.
 *
 * @author 		Jose A. Salim
 * @package 	EVoucherWP/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $voucher;

?>
<div class="voucher-meta">

	<?php do_action( 'evoucherwp_voucher_meta_start' ); ?>

		<p><strong><?php _e( 'PIN:', 'evoucherwp' ); ?></strong>

		<?php echo $voucher->codeprefix . $voucher->guid . $voucher->codesuffix; ?></p>

		<p><strong><?php _e( 'Valid until:', 'evoucherwp' ); ?></strong>
		<?php echo date_i18n( get_option( 'date_format' ), $voucher->expiry ); ?></p>

	<?php do_action( 'evoucherwp_voucher_meta_end' ); ?>

</div>
