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


$codeprefix = $voucher->codeprefix;
$codesuffix = $voucher->codesuffix;

if ( empty( $codeprefix) ){
	$codeprefix = get_option( 'evoucherwp_codeprefix', '' );
}

if ( empty( $codesuffix) ){
	$codesuffix = get_option( 'evoucherwp_codesuffix', '' );
}

$expiry = $voucher->expiry;
if ( empty( $expiry ) && $expiry !== 0 ){
	$days_to_expiry = intval( get_option( 'evoucherwp_expiry', 0 ) );
	$startdate = floatval( $voucher->startdate );
	$expiry = $startdate + ( $days_to_expiry * 24 * 60 * 60 );
}

?>
<div class="voucher-meta">

	<?php do_action( 'evoucherwp_voucher_meta_start', $voucher ); ?>

		<p><strong><?php _e( 'PIN:', 'evoucherwp' ); ?></strong>

		<?php echo $codeprefix . $voucher->guid . $codesuffix; ?></p>

		<?php if ( $expiry > 0 ): ?>
			<p><strong><?php _e( 'Valid until:', 'evoucherwp' ); ?></strong>
			<?php echo date_i18n( get_option( 'date_format' ), $expiry ); ?></p>
		<?php endif; ?>

	<?php do_action( 'evoucherwp_voucher_meta_end', $voucher ); ?>

</div>
