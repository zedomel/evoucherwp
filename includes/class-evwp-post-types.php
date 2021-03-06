<?php
/**
 * Post Types
 *
 * Registers post types and taxonomies.
 * This file was adapted from https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-post-types.php
 *
 * @class     EVWP_Post_types
 * @version   1.0.0
 * @package   EVoucherWP/Classes/Vouchers
 * @category  Class
 * @author    Jose A. Salim
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EVWP_Post_types Class.
 */
class EVWP_Post_types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {

		if ( post_type_exists('evoucher') ) {
	        return;
	    }

	    do_action( 'evoucherwp_register_post_type' );

	    register_post_type( 'evoucher', 
	        array(
	            'labels'    => array(
	                'name'                  =>  __('Vouchers', 'evoucherwp'),
	                'singular_name'         => __('Voucher', 'evoucherwp'),
	                'menu_name'             => _x('Vouchers', 'Admin menu name', 'evoucherwp' ),
	                'add_new'               => __( 'Create Voucher', 'evoucherwp' ),
	                'add_new_item'          => __( 'Create New Voucher', 'evoucherwp' ),
	                'edit'                  => __( 'Edit', 'evoucherwp' ),
	                'edit_item'             => __( 'Edit Voucher', 'evoucherwp' ),
	                'new_item'              => __( 'New Voucher', 'evoucherwp' ),
	                'view'                  => __( 'View Voucher', 'evoucherwp' ),
	                'view_item'             => __( 'View Voucher', 'evoucherwp' ),
	                'search_items'          => __( 'Search Voucher', 'evoucherwp' ),
	                'not_found'             => __( 'No Vouchers found', 'woocommerce' ),
	                'not_found_in_trash'    => __( 'No Vouchers found in trash', 'evoucherwp' ),
	                'filter_items_list'     => __( 'Filter vouchers', 'evoucherwp' ),
	                'items_list_navigation' => __( 'Vouchers navigation', 'evoucherwp' ),
	                'items_list'            => __( 'Vouchers list', 'evoucherwp' ),
	                ),
	            'description'         => __( 'This is where you can add new vouchers.', 'evoucherwp' ),
	            'public'              => true,
	            'show_ui'             => true,
	            'capability_type'     => array('evoucher', 'evouchers'),
	            'map_meta_cap'        => true,
	            'publicly_queryable'  => true,
	            'exclude_from_search' => true,
	            'show_in_menu'        => current_user_can( 'edit_evoucher' ) ? 'evoucherwp_menu' : false,
	            'hierarchical'        => false,
	            'show_in_nav_menus'   => false,
	            'rewrite'             => array( 'slug' => untrailingslashit( 'evoucher' ), 'with_front' => false, 'feeds' => true ),
	            'query_var'           => false,
	            'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
	            'has_archive'         => false,
	            'menu_icon'			  => null,
	            'menu_position'		  => 25,
 	        )
	    );
	}
}

EVWP_Post_types::init();
