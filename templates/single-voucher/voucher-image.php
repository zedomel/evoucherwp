<?php
/**
 * Single Voucher Image
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/single-voucher/voucher-image.php.
 *
 * @author 		Jose A. Salim
 * @package 	EVoucherWP/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $voucher;
?>
<div class="voucher-image">
	<?php
		if ( has_post_thumbnail() ) {
			$image            = get_the_post_thumbnail( $post->ID );
			echo $image;
		}
		
		do_action( 'evoucherwp_voucher_thumbnails' );
	?>
</div>
