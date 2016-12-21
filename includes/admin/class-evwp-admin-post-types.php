<?php
/**
 * Post Types Admin
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
		//add_filter( 'manage_evoucher_template_posts_columns', array( $this, 'evoucher_template_columns' ) );

		add_action( 'manage_evoucher_posts_custom_column', array( $this, 'render_evoucher_columns' ), 2 );
		//add_action( 'manage_evoucher_template_posts_custom_column', array( $this, 'render_evoucher_template_columns' ), 2 );
		
		//add_filter( 'manage_edit-product_sortable_columns', array( $this, 'product_sortable_columns' ) );
		
		
		add_action( 'save_post_evoucher_template', array( $this, 'save_voucher_template' ), 10, 3) ;

		// Filters
		//add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
		//add_filter( 'request', array( $this, 'request_query' ) );
		//add_filter( 'parse_query', array( $this, 'product_filters_query' ) );
		//add_filter( 'posts_search', array( $this, 'product_search' ) );

		// Status transitions
		add_action( 'delete_post', array( $this, 'delete_post' ) );
		add_action( 'wp_trash_post', array( $this, 'trash_post' ) );
		add_action( 'untrash_post', array( $this, 'untrash_post' ) );

		// Edit post screens
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );
		add_filter( 'default_hidden_meta_boxes', array( $this, 'hidden_meta_boxes' ), 10, 2 );
		add_action( 'post_submitbox_misc_actions', array( $this, 'evoucher_data_visibility' ) );

		// Meta-Box Class
		include_once( 'class-evwp-admin-meta-boxes.php' );

		// Disable DFW feature pointer
		add_action( 'admin_footer', array( $this, 'disable_dfw_feature_pointer' ) );

		// Disable post type view mode options
		add_filter( 'view_mode_post_types', array( $this, 'disable_view_mode_options' ) );

		add_filter( 'mce_external_plugins', array( $this, 'add_mce_buttons' ) );
        add_filter( 'mce_buttons',  array( $this, 'register_mce_buttons' ) );
        add_filter( 'mce_css', array( $this, 'load_editor_custom_css' ) );
	}


	public function add_mce_buttons( $plugin_array ){
		global $post;
	    if ( $post->post_type === 'evoucher_template' ){
	        $plugin_array['evoucherwp'] = EVoucherWP()->plugin_url() . '/assets/admin/js/mce_editor.js';
	    }
	    return $plugin_array;
	}

	public function register_mce_buttons( $buttons ){
		global $post;
	    if ( $post->post_type === 'evoucher_template' ){
	        array_push( $buttons, 'addfield' );
	    }
	    return $buttons;
	}

	public function load_editor_custom_css( $mce_css ){
		global $post;

	    if ( $post->post_type === 'evoucher_template' ){
			if ( ! empty( $mce_css ) )
				$mce_css .= ',';

			$mce_css .= add_query_arg( array(
				'action' 	=> 'evoucherwp_tiny_templace_css',
				'_nonce' 	=> wp_create_nonce( 'evoucherwp_tiny_template_css' ),
				'post_id'	=> $post->ID
				), 
				EVoucherWP()->ajax_url() 
			);
		}

		return $mce_css;
	}

	/**
	 * Save voucher template
	 * @param mixed $post_id ID of post being deleted
	 * @param WP_Post $post
	 * @param boolean $update
	*/
	public function save_voucher_template( $post_id, $post, $update ){

	    global $wpdb;

	    error_log('oi');

	    // if ( !$update ){
	    //    // Insert template
	    //     $wpdb->insert( $wpdb->prefix . 'evoucherwp_templates', array( 'id' => $post_id ) );
	    // }

	    if ( !isset( $post ) || empty( $post->post_content ) ){
	        return;
	    }

	    // Extracts all fileds from post content
	    require_once ( 'externals/simple_html_dom.php' );

	    $html = str_get_html( $post->post_content );
	    $elems = $html->find('span[id^=_field_], img[id^=_field_]');
	    $data = array();
	    foreach ( $elems as $elem ) {
	        $data[] = array(
	            'id' => $elem->id,
	            'type' => $elem->tag,
	            'class' => ( isset( $elem->class) && !empty( $elem->class ) ) ? $elem->class :  NULL
	        );
	    }

	    // Update fields
	    update_post_meta( $post_id, '_fields', maybe_serialize( $data ) );
	    // $wpdb->update( $wpdb->prefix . 'evoucherwp_templates', array( 'name' => $post->post_title, 
	        // 'fields' => maybe_serialize ( $data ) ), array( 'id' => $post_id ) );        
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

		$messages['evoucher_template'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( 'Template updated. <a href="%s">View Template</a>', 'evoucherwp' ), esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.', 'evoucherwp' ),
			3 => __( 'Custom field deleted.', 'evoucherwp' ),
			4 => __( 'Template updated.', 'evoucherwp' ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( 'Template restored to revision from %s', 'evoucherwp' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( 'Template published. <a href="%s">View Template</a>', 'evoucherwp' ), esc_url( get_permalink( $post_ID ) ) ),
			7 => __( 'Template saved.', 'evoucherwp' ),
			8 => sprintf( __( 'Template submitted. <a target="_blank" href="%s">Preview Template</a>', 'evoucherwp' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( 'Template scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Template</a>', 'evoucherwp' ),
			  date_i18n( __( 'M j, Y @ G:i', 'evoucherwp' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( 'Template draft updated. <a target="_blank" href="%s">Preview Template</a>', 'evoucherwp' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
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
		$columns['name']  = __( 'Name', 'evoucherwp' );
		$columns['date']         = __( 'Date', 'evoucherwp' );
		$columns['download'] = __('Download', 'evoucherwp');

		return array_merge( $columns, $existing_columns );

	}

	/**
	 * Ouput custom columns for vouchers.
	 *
	 * @param string $column
	 */
	public function render_evoucher_columns( $column ) {
		global $post, $the_voucher;

		//if ( empty( $the_voucher ) || $the_voucher->id != $post->ID ) {
		//	$the_voucher = wc_get_product( $post );
		//}

		switch ( $column ) {
			case 'name' :
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

				/* Custom inline data for woocommerce. */
				echo '
					<div class="hidden" id="evoucherwp_inline_' . $post->ID . '">
					</div>
				';
				break;
			case 'download':
				$voucher = new EVWP_Voucher( $post );
				$url = $voucher->get_download_url();
				echo '<strong><a class="row-title" href="' . esc_url( $url ) . '">' . $voucher->guid . '</a>';

				break;
			default :
				break;
		}
	}

	/**
	 * Make columns sortable - https://gist.github.com/906872.
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function product_sortable_columns( $columns ) {
		$custom = array(
			'price'    => 'price',
			'featured' => array( 'featured', 1 ),
			'sku'      => 'sku',
			'name'     => 'title'
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Make columns sortable - https://gist.github.com/906872.
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function shop_coupon_sortable_columns( $columns ) {
		return $columns;
	}

	/**
	 * Make columns sortable - https://gist.github.com/906872.
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function shop_order_sortable_columns( $columns ) {
		$custom = array(
			'order_title' => 'ID',
			'order_total' => 'order_total',
			'order_date'  => 'date'
		);
		unset( $columns['comments'] );

		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Set list table primary column for products and orders.
	 * Support for WordPress 4.3.
	 *
	 * @param  string $default
	 * @param  string $screen_id
	 *
	 * @return string
	 */
	public function list_table_primary_column( $default, $screen_id ) {

		if ( 'edit-product' === $screen_id ) {
			return 'name';
		}

		if ( 'edit-shop_order' === $screen_id ) {
			return 'order_title';
		}

		if ( 'edit-shop_coupon' === $screen_id ) {
			return 'coupon_code';
		}

		return $default;
	}

	/**
	 * Set row actions for products and orders.
	 *
	 * @param  array $actions
	 * @param  WP_Post $post
	 *
	 * @return array
	 */
	public function row_actions( $actions, $post ) {
		if ( 'product' === $post->post_type ) {
			return array_merge( array( 'id' => 'ID: ' . $post->ID ), $actions );
		}

		if ( in_array( $post->post_type, array( 'shop_order', 'shop_coupon' ) ) ) {
			if ( isset( $actions['inline hide-if-no-js'] ) ) {
				unset( $actions['inline hide-if-no-js'] );
			}
		}

		return $actions;
	}

	/**
	 * Product sorting link.
	 *
	 * Based on Simple Page Ordering by 10up (https://wordpress.org/extend/plugins/simple-page-ordering/).
	 *
	 * @param  array $views
	 * @return array
	 */
	/* public function product_sorting_link( $views ) {
		global $post_type, $wp_query;

		if ( ! current_user_can( 'edit_others_pages' ) ) {
			return $views;
		}

		$class            = ( isset( $wp_query->query['orderby'] ) && $wp_query->query['orderby'] == 'menu_order title' ) ? 'current' : '';
		$query_string     = remove_query_arg(array( 'orderby', 'order' ));
		$query_string     = add_query_arg( 'orderby', urlencode('menu_order title'), $query_string );
		$query_string     = add_query_arg( 'order', urlencode('ASC'), $query_string );
		$views['byorder'] = '<a href="' . esc_url( $query_string ) . '" class="' . esc_attr( $class ) . '">' . __( 'Sort Products', 'woocommerce' ) . '</a>';

		return $views;
	}
	}*/

	/**
	 * Filters for post types.
	 */
	/* public function restrict_manage_posts() {
		global $typenow;

		if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ) ) ) {
			$this->shop_order_filters();
		} elseif ( 'product' == $typenow ) {
			$this->product_filters();
		} elseif( 'shop_coupon' == $typenow ) {
			$this->shop_coupon_filters();
		}
	}*/

	/**
	 * Show a category filter box.
	 */
	/* public function product_filters() {
		global $wp_query;

		// Category Filtering
		wc_product_dropdown_categories();

		// Type filtering
		$terms   = get_terms( 'product_type' );
		$output  = '<select name="product_type" id="dropdown_product_type">';
		$output .= '<option value="">' . __( 'Show all product types', 'woocommerce' ) . '</option>';

		foreach ( $terms as $term ) {
			$output .= '<option value="' . sanitize_title( $term->name ) . '" ';

			if ( isset( $wp_query->query['product_type'] ) ) {
				$output .= selected( $term->slug, $wp_query->query['product_type'], false );
			}

			$output .= '>';

			switch ( $term->name ) {
				case 'grouped' :
					$output .= __( 'Grouped product', 'woocommerce' );
					break;
				case 'external' :
					$output .= __( 'External/Affiliate product', 'woocommerce' );
					break;
				case 'variable' :
					$output .= __( 'Variable product', 'woocommerce' );
					break;
				case 'simple' :
					$output .= __( 'Simple product', 'woocommerce' );
					break;
				default :
					// Assuming that we have other types in future
					$output .= ucfirst( $term->name );
					break;
			}

			$output .= '</option>';

			if ( 'simple' == $term->name ) {

				$output .= '<option value="downloadable" ';

				if ( isset( $wp_query->query['product_type'] ) ) {
					$output .= selected( 'downloadable', $wp_query->query['product_type'], false );
				}

				$output .= '> &rarr; ' . __( 'Downloadable', 'woocommerce' ) . '</option>';

				$output .= '<option value="virtual" ';

				if ( isset( $wp_query->query['product_type'] ) ) {
					$output .= selected( 'virtual', $wp_query->query['product_type'], false );
				}

				$output .= '> &rarr;  ' . __( 'Virtual', 'woocommerce' ) . '</option>';
			}
		}

		$output .= '</select>';

		echo apply_filters( 'woocommerce_product_filters', $output );
	}*/

	/**
	 * Filters and sorting handler.
	 *
	 * @param  array $vars
	 * @return array
	 */
	/* public function request_query( $vars ) {
		global $typenow, $wp_query, $wp_post_statuses;

		if ( 'product' === $typenow ) {
			// Sorting
			if ( isset( $vars['orderby'] ) ) {
				if ( 'price' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key'  => '_price',
						'orderby'   => 'meta_value_num'
					) );
				}
				if ( 'featured' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key'  => '_featured',
						'orderby'   => 'meta_value'
					) );
				}
				if ( 'sku' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key'  => '_sku',
						'orderby'   => 'meta_value'
					) );
				}
			}

		} elseif ( 'shop_coupon' === $typenow ) {

			if ( ! empty( $_GET['coupon_type'] ) ) {
				$vars['meta_key']   = 'discount_type';
				$vars['meta_value'] = wc_clean( $_GET['coupon_type'] );
			}

		} elseif ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ) ) ) {

			// Filter the orders by the posted customer.
			if ( isset( $_GET['_customer_user'] ) && $_GET['_customer_user'] > 0 ) {
				$vars['meta_query'] = array(
					array(
						'key'   => '_customer_user',
						'value' => (int) $_GET['_customer_user'],
						'compare' => '='
					)
				);
			}

			// Sorting
			if ( isset( $vars['orderby'] ) ) {
				if ( 'order_total' == $vars['orderby'] ) {
					$vars = array_merge( $vars, array(
						'meta_key'  => '_order_total',
						'orderby'   => 'meta_value_num'
					) );
				}
			}

			// Status
			if ( ! isset( $vars['post_status'] ) ) {
				$post_statuses = wc_get_order_statuses();

				foreach ( $post_statuses as $status => $value ) {
					if ( isset( $wp_post_statuses[ $status ] ) && false === $wp_post_statuses[ $status ]->show_in_admin_all_list ) {
						unset( $post_statuses[ $status ] );
					}
				}

				$vars['post_status'] = array_keys( $post_statuses );
			}
		}

		return $vars;
	}*/

	/**
	 * Filter the products in admin based on options.
	 *
	 * @param mixed $query
	 */
	/* public function product_filters_query( $query ) {
		global $typenow, $wp_query;

		if ( 'product' == $typenow ) {

			if ( isset( $query->query_vars['product_type'] ) ) {
				// Subtypes
				if ( 'downloadable' == $query->query_vars['product_type'] ) {
					$query->query_vars['product_type']  = '';
					$query->is_tax = false;
					$query->query_vars['meta_value']    = 'yes';
					$query->query_vars['meta_key']      = '_downloadable';
				} elseif ( 'virtual' == $query->query_vars['product_type'] ) {
					$query->query_vars['product_type']  = '';
					$query->is_tax = false;
					$query->query_vars['meta_value']    = 'yes';
					$query->query_vars['meta_key']      = '_virtual';
				}
			}

			// Categories
			if ( isset( $_GET['product_cat'] ) && '0' === $_GET['product_cat'] ) {
				$query->query_vars['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => get_terms( 'product_cat', array( 'fields' => 'ids' ) ),
					'operator' => 'NOT IN'
				);
			}

			// Shipping classes
			if ( isset( $_GET['product_shipping_class'] ) && '0' === $_GET['product_shipping_class'] ) {
				$query->query_vars['tax_query'][] = array(
					'taxonomy' => 'product_shipping_class',
					'field'    => 'id',
					'terms'    => get_terms( 'product_shipping_class', array( 'fields' => 'ids' ) ),
					'operator' => 'NOT IN'
				);
			}
		}
	}*/


	/**
	 * Disable the auto-save functionality for Orders.
	 */
	public function disable_autosave() {
		global $post;

		if ( $post && in_array( get_post_type( $post->ID ), array( 'evoucher', 'evoucher_template' ) ) ) {
			wp_dequeue_script( 'autosave' );
		}
	}

	/**
	 * Removes variations etc belonging to a deleted post, and clears transients.
	 *
	 * @param mixed $id ID of post being deleted
	 */
	public function delete_post( $id ) {
		global $evoucherwp, $wpdb;

		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		if ( $id > 0 ) {

			$post_type = get_post_type( $id );

			//switch ( $post_type ) {
				//case 'evoucher_template' :
				//	$wpdb->delete( $wpdb->prefix . 'evoucherwp_templates', array( 'id' => $id ) );
				//break;
			//}
		}
	}

	/**
	 * trash_post function.
	 *
	 * @param mixed $id
	 */
	public function trash_post( $id ) {
		global $wpdb;

		if ( $id > 0 ) {

			$post_type = get_post_type( $id );

		}
	}

	/**
	 * woocommerce_untrash_post function.
	 *
	 * @param mixed $id
	 */
	public function untrash_post( $id ) {
		global $wpdb;

		if ( $id > 0 ) {

			$post_type = get_post_type( $id );

			if ( in_array( $post_type, wc_get_order_types( 'evoucher_template' ) ) ) {

			}
		}
	}

	/**
	 * Change title boxes in admin.
	 * @param  string $text
	 * @param  object $post
	 * @return string
	 */
	public function enter_title_here( $text, $post ) {
		switch ( $post->post_type ) {
			case 'evoucher' :
				$text = __( 'Voucher name', 'evoucherwp' );
			break;
		}

		return $text;
	}

	/**
	 * Hidden default Meta-Boxes.
	 * @param  array  $hidden
	 * @param  object $screen
	 * @return array
	 */
	public function hidden_meta_boxes( $hidden, $screen ) {
		if ( 'evoucher' === $screen->post_type && 'post' === $screen->base ) {
			$hidden = array_merge( $hidden, array( 'postcustom' ) );
		}

		return $hidden;
	}

	/**
	 * Output product visibility options.
	 */
	public function evoucher_data_visibility() {
		global $post;

		if ( 'evoucher' != $post->post_type ) {
			return;
		}

		//$current_visibility = ( $current_visibility = get_post_meta( $post->ID, '_visibility', true ) ) ? $current_visibility : apply_filters( 'woocommerce_product_visibility_default' , 'visible' );
		//$current_featured   = ( $current_featured = get_post_meta( $post->ID, '_featured', true ) ) ? $current_featured : 'no';
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
	 * Removes products, orders, and coupons from the list of post types that support "View Mode" switching.
	 * View mode is seen on posts where you can switch between list or excerpt. Our post types don't support
	 * it, so we want to hide the useless UI from the screen options tab.
	 *
	 * @since 2.6
	 * @param  array $post_types Array of post types supporting view mode
	 * @return array             Array of post types supporting view mode, without products, orders, and coupons
	 */
	public function disable_view_mode_options( $post_types ) {
		unset( $post_types['evoucher'], $post_types['evoucher_template'] );
		return $post_types;
	}
}

endif;

new EVWP_Admin_Post_Types();
