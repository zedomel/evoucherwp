<?php
/**
 * Single Voucher Content
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/single-voucher/content.php.
 *
 * @author     Jose A. Salim
 * @package    EVoucherWP/Templates
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="voucher-content">
	<?php 
		do_action( 'evoucherwp_before_voucher_content' );

		the_content();

		do_action( 'evoucherwp_after_voucher_content' );
	?>
</div>
