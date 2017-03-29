<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap evoucherwp">
	<form method="<?php echo esc_attr( apply_filters( 'evoucherwp_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper evwp-nav-tab-wrapper">
			<?php
				foreach ( $tabs as $name => $label ) {
					echo '<a href="' . admin_url( 'admin.php?page=evwp-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
				}
				do_action( 'evoucherwp_settings_tabs' );
			?>
		</nav>
		<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
		<?php
			do_action( 'evoucherwp_sections_' . $current_tab );

			self::show_messages();

			do_action( 'evoucherwp_settings_' . $current_tab );
		?>
		<p class="submit">
			<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
				<input name="save" class="button-primary evoucherwp-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'evoucherwp' ); ?>" />
			<?php endif; ?>
			<?php wp_nonce_field( 'evoucherwp-settings' ); ?>
		</p>
	</form>
</div>
