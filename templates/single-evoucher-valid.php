<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Template: Valid E-Voucher
 */

?>

<head>
	<?php	do_action( 'evoucherwp_print_scripts_and_styles' ); ?>
</head>

<?php

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