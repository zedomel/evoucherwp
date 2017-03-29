<?php

/**
 * The template for displaying product content in the single-evoucher.php template
 *
 * This template can be overridden by copying it to yourtheme/evoucherwp/content-single-evoucher.php.
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

?>

<?php
	/**
	 * evoucherwp_before_single_voucher hook.
	 */
	 do_action( 'evoucherwp_before_single_voucher' );

	 if ( post_password_required() ) {
	 	echo get_the_password_form();
	 	return;
	 }
?>

<div itemscope itemtype="<?php echo evoucherwp_get_evoucher_schema(); ?>" id="evoucher-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php
		/**
		 * evoucherwp_before_single_voucher_summary hook.
		 *
		 * @hooked evoucherwp_template_single_title - 5
		 */
		do_action( 'evoucherwp_before_single_voucher_summary' );
	?>

	<div class="summary entry-summary">

		<?php
			/**
			 * evoucherwp_single_product_summary hook.
			 *
			 * @hooked evoucherwp_show_voucher_image - 20
			 * @hooked evoucherwp_template_single_meta - 20
			 */
			do_action( 'evoucherwp_single_voucher_summary' );
		?>

	</div><!-- .summary -->

	<?php
		/**
		 * evoucherwp_after_single_voucher_summary hook.
		 *
		 * @hooked evoucherwp_output_voucher_content - 10
		 */
		do_action( 'evoucherwp_after_single_voucher_summary' );
	?>

	<meta itemprop="url" content="<?php the_permalink(); ?>" />

</div><!-- #evoucher-<?php the_ID(); ?> -->

<?php do_action( 'evoucherwp_after_single_evoucher' ); ?>