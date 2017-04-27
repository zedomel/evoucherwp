<?php
/**
 * Post Types Admin
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-post-types.php
 *
 * @author   Jose A. Salim
 * @category Admin
 * @package  EVoucherWP/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'EVWP_Admin_Post_Types' ) ) :

/**
 * EVWP_Admin_Post_Types Class.
 *
 * Handles the edit posts views and some functionality on the edit post screen for EVWP post types.
 */
class EVWP_Admin_Post_Types {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_post_updated_messages' ), 10, 2 );

		// Disable Auto Save
		add_action( 'admin_print_scripts', array( $this, 'disable_autosave' ) );

		// WP List table columns. Defined here so they are always available for events such as inline editing.
		add_filter( 'manage_evoucher_posts_columns', array( $this, 'evoucher_columns' ) );

		add_action( 'manage_evoucher_posts_custom_column', array( $this, 'render_evoucher_columns' ), 2 );

		add_action( 'save_post_evoucher', array( $this, 'save_voucher' ), 20, 3) ;
		add_action( 'before_delete_post', array( $this, 'delete_voucher_code' ) );

		// Edit post screens
		
		add_filter( 'default_hidden_meta_boxes', array( $this, 'hidden_meta_boxes' ), 10, 2 );
		add_action( 'post_submitbox_misc_actions', array( $this, 'evoucher_data_visibility' ) );

		// Meta-Box Class
		include_once( 'class-evwp-admin-meta-boxes.php' );

		// Disable DFW feature pointer
		add_action( 'admin_footer', array( $this, 'disable_dfw_feature_pointer' ) );

		// Disable post type view mode options
		add_filter( 'view_mode_post_types', array( $this, 'disable_view_mode_options' ) );
	}

	/**
     * Save voucher
     * @param mixed $post_id ID of post being deleted
     * @param WP_Post $post
     * @param boolean $update
    */
    public function save_voucher( $post_id, $post, $update ){

        global $wpdb;

        if ( !isset( $post ) ){
            return;
        }

        // Check if the code has not been changed.
        // If some change in code type or code length was made
        // then the meta value of _evoucher_guid key was set to empty ('').
        // Generate it again with new options.
        $code = '';
        if ( $update ){
        	$code = get_post_meta( $post_id, '_evoucherwp_guid', true );
        }

        // If not generated yet or code options have been updated 
        if ( empty( $code ) ){
        	$code = evwp_generate_voucher_code( $post_id );
        	if ( $code )
	        	update_post_meta( $post_id, '_evoucherwp_guid', $code );
        }
    }

    /**
	 * Removes voucher code generated using sequential generator
	 *
	 * @param mixed $id ID of post being deleted
	 */
	public function delete_voucher_code( $id ) {
		global $wpdb;

		$code_type = get_post_meta( $id, '_evoucherwp_codestype', true );

		if ( $code_type === 'sequential' ){
			$wpdb->delete( $wpdb->prefix . 'evoucherwp_code_seq', array( 'post_id' => $id), array( '$d') );
		}
	}


	/**
	 * Change messages when a post type is updated.
	 * @param  array $messages
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages['evoucher'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'E-Voucher updated. <a href="%s">View E-Voucher</a>', 'evoucherwp' ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'evoucherwp' ),
			3 => __( 'Custom field deleted.', 'evoucherwp' ),
			4 => __( 'E-Voucher updated.', 'evoucherwp' ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'E-Voucher restored to revision from %s', 'evoucherwp' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'E-Voucher published. <a href="%s">View E-Voucher</a>', 'evoucherwp' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __( 'E-Voucher saved.', 'evoucherwp' ),
			8 => sprintf( __( 'E-Voucher submitted. <a target="_blank" href="%s">Preview E-Voucher</a>', 'evoucherwp' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( 'E-Voucher scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview E-Voucher</a>', 'evoucherwp' ),
			  date_i18n( __( 'M j, Y @ G:i', 'evoucherwp' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'E-Voucher draft updated. <a target="_blank" href="%s">Preview E-Voucher</a>', 'evoucherwp' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

	/**
	 * Specify custom bulk actions messages for different post types.
	 * @param  array $bulk_messages
	 * @param  array $bulk_counts
	 * @return array
	 */
	public function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {

		$bulk_messages['evoucher'] = array(
			'updated'   => _n( '%s e-voucher updated.', '%s e-vouchers updated.', $bulk_counts['updated'], 'evoucherwp' ),
			'locked'    => _n( '%s e-voucher not updated, somebody is editing it.', '%s e-vouchers not updated, somebody is editing them.', $bulk_counts['locked'], 'evoucherwp' ),
			'deleted'   => _n( '%s e-voucher permanently deleted.', '%s e-vouchers permanently deleted.', $bulk_counts['deleted'], 'evoucherwp' ),
			'trashed'   => _n( '%s e-voucher moved to the Trash.', '%s e-vouchers moved to the Trash.', $bulk_counts['trashed'], 'evoucherwp' ),
			'untrashed' => _n( '%s e-voucher restored from the Trash.', '%s e-vouchers restored from the Trash.', $bulk_counts['untrashed'], 'evoucherwp' ),
		);

		return $bulk_messages;
	}

	/**
	 * Define custom columns for vouchers.
	 * @param  array $existing_columns
	 * @return array
	 */
	public function evoucher_columns( $existing_columns ) {
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
			$existing_columns = array();
		}

		unset( $existing_columns['title'], $existing_columns['comments'], $existing_columns['date'] );

		$columns          = array();
		$columns['cb']    = '<input type="checkbox" />';
		$columns['title']  = __( 'Title', 'evoucherwp' );
		$columns['expiry']         = __( 'Expiry', 'evoucherwp' );
		$columns['download'] = __('Download', 'evoucherwp');
		$columns['live']	= __('Live', 'evoucherwp');

		return array_merge( $columns, $existing_columns );

	}

	/**
	 * Ouput custom columns for vouchers.
	 *
	 * @param string $column
	 */
	public function render_evoucher_columns( $column ) {
		global $post, $the_voucher;

		if ( empty( $the_voucher ) || $the_voucher->id != $post->ID ) {
			$the_voucher = new EVWP_Voucher( $post );
		}

		switch ( $column ) {
			case 'title' :
				$edit_link = get_edit_post_link( $post->ID );
				$title     = _draft_or_post_title();

				echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . esc_html( $title ) . '</a>';

				_post_states( $post );

				echo '</strong>';

				if ( $post->post_parent > 0 ) {
					echo '&nbsp;&nbsp;&larr; <a href="'. get_edit_post_link( $post->post_parent ) .'">'. get_the_title( $post->post_parent ) .'</a>';
				}

				// Excerpt view
				if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
					echo apply_filters( 'the_excerpt', $post->post_excerpt );
				}

				//$this->_render_evouchers_row_actions( $post, $title );

				get_inline_data( $post );

				/* Custom inline data. */
				echo '
					<div class="hidden" id="evoucherwp_inline_' . $post->ID . '">
					</div>
				';
				break;
			case 'download':
				$url = $the_voucher->get_download_url();
				echo '<strong><a class="row-title" target="_blank" href="' . esc_url( $url ) . '">' . $the_voucher->guid . '</a>';

				break;
			case 'expiry':
				$expiry = sanitize_title( __( 'Not set', 'evoucherwp' ) );
				if ( !empty( $the_voucher->expiry ) ){
					$expiry = date_i18n( get_option( 'date_format' ), $the_voucher->expiry );
				}
				echo '<abbr title="' . $expiry . '">' . $expiry . '</abbr>';
				break;
			case 'live':
				echo '<strong><p>' . ( $the_voucher->live === 'yes' ? __( 'Yes', 'evoucherwp' ) : __( 'No', 'evoucherwp' ) ) . '</p></strong>';
				break;
			default :
				break;
		}
	}

	/**
	 * Disable the auto-save functionality for Orders.
	 */
	public function disable_autosave() {
		global $post;

		if ( $post && in_array( get_post_type( $post->ID ), array( 'evoucher' ) ) ) {
			wp_dequeue_script( 'autosave' );
		}
	}

	/**
	 * Hidden default Meta-Boxes.
	 * @param  array  $hidden
	 * @param  object $screen
	 * @return array
	 */
	public function hidden_meta_boxes( $hidden, $screen ) {
		if ( 'evoucher' === $screen->post_type && 'post' === $screen->base ) {
			$hidden = array_merge( $hidden, array( 'postcustom', 'slugdiv' ) );
		}

		return $hidden;
	}

	/**
	 * Output voucher visibility options.
	 */
	public function evoucher_data_visibility() {
		global $post;

		if ( 'evoucher' !== $post->post_type ) {
			return;
		}
	}

	/**
	 * Disable DFW feature pointer.
	 */
	public function disable_dfw_feature_pointer() {
		$screen = get_current_screen();

		if ( $screen && 'evoucher' === $screen->id && 'post' === $screen->base ) {
			remove_action( 'admin_print_footer_scripts', array( 'WP_Internal_Pointers', 'pointer_wp410_dfw' ) );
		}
	}

	/**
	 * Removes vouchers from the list of post types that support "View Mode" switching.
	 * View mode is seen on posts where you can switch between list or excerpt. Our post types don't support
	 * it, so we want to hide the useless UI from the screen options tab.
	 *
	 * @since 1.0
	 * @param  array $post_types Array of post types supporting view mode
	 * @return array             Array of post types supporting view mode, without evoucher
	 */
	public function disable_view_mode_options( $post_types ) {
		unset( $post_types['evoucher'] );
		return $post_types;
	}
}

endif;

new EVWP_Admin_Post_Types();
