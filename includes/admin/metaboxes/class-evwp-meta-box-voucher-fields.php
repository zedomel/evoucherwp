<?php
/**
 * Voucher Fields
 *
 * Functions for displaying the voucher fields meta box.
 *
 * @author 		Jose A. Salim
 * @category 	Admin
 * @package 	EVoucherWP/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EVWP_Meta_Box_Voucher_Fields Class.
 */
class EVWP_Meta_Box_Voucher_Fields {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {

	    wp_nonce_field( 'evoucherwp_save_data', 'evoucherwp_meta_nonce' );

	    $template_id = get_post_meta( $post->ID, '_evoucherwp_template', true );

	    $templates = get_posts( array( 
	    		'post_type' => 'evoucher_template',
	    		'post_status' => 'publish', 
	    		'post_per_page' => -1
	    	) );
	    // $templates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}evoucherwp_templates" );
	    if ( !empty( $templates ) ) {
	        echo '<select id="_evoucherwp_template" name="_evoucherwp_template"><option value="">' . __('Select a template', 'evoucherwp' ) . '</option>';
	        foreach ( $templates as $template ){
	            echo '<option value="' . $template->ID . '" ' . selected( intval( $template_id ), $template->ID, false ) . '>' . $template->post_title . '</option>';
	        }
	        echo '</select>';
	    }
	    else {
	        echo '<p>' . __( 'No template found! Create a new template before to continue', 'evoucherwp' ) . '</p>';
	        return;
	    }

	    echo '<div id="evoucherwp_fields">';
	    if ( !empty( $template_id ) ) { 
	    	// $template = $wpdb->get_row( $wpdb->prepare( "SELECT id, fields FROM {$wpdb->prefix}evoucherwp_templates WHERE id = %d", inval( $template_id ) ) );
	    	$template = new EVWP_Voucher_Template( $template_id );
            $voucher = new EVWP_Voucher( $post->ID ); //get_post_meta( $post->ID, '_fields', true );
            
            // Get URL from GUID
            $url = $voucher->get_download_url();
            echo '<div class="inside evoucherwp-url"><a class="button" href="' . esc_url( $url ) . '">' . __('Preview', 'evoucherwp' ) . '</a></div>';

            $fields = $template->get_fields();
            $voucher_fields = $voucher->get_fields();
            error_log(print_r($voucher_fields, true));
            foreach ( $fields as $field ){
                echo '<div class="inside">';
                $id = $field[ 'id' ];
                $name = substr( $id , 7 ) . ': ';
                if ( $field[ 'type' ] == 'span' && $id != '_field_guid' ){
                    echo '<label for="' . $id . '">' . $name;
                    echo '<input id="' . $id . '" name="' . $id . '" type="text" ' . 
                        'value="' . ( isset( $voucher_fields->$id ) ? $voucher_fields->$id : '' ) . '"></label>';        
                }
                elseif ( $field[ 'type' ] == 'img' ){
                    $img_src = '';
                    $img_id = '';
                    if ( isset( $voucher_fields->$id ) ){
                        $img_src = wp_get_attachment_url( $voucher_fields->$id );
                        $img_id = $voucher_fields->$id;
                    }

                    echo '<div class="image-preview-wrapper">';
                    echo '<p>' . $name . '<img class="image-preview" src="' . ( !empty( $img_src ) ? esc_url( $img_src ) : '' ) . '" >';
                    echo '<input type="hidden" class="input-image"  name="' .  $id .  '" id="' . 
                        $id . '" value="' . ( !empty( $img_id ) ? $img_id : '' ) . '">';
                    echo '<input id="upload_image_button" type="button" class="button" value="' . __( 'Select Media' , 'evoucherwp' ) . '"/></p>';
                    echo '</div>';
                }
                echo '</div>';
            }
	    }    
	    echo '</div>'; 
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public static function save( $post_id, $post ) {

		global $wpdb;

	    if ( isset( $_POST[ '_evoucherwp_template' ] ) && $_POST[ '_evoucherwp_template' ] > 0 ){
	        $template = get_post_meta( $post_id, '_evoucherwp_template', true );
	        // The template have been changed, delete all fields from post
	        if ( !empty( $template ) && $template !== $_POST[ '_evoucherwp_template' ] ){
	        	delete_post_meta( $post_id, '_fields' );   
	        }
        
        	update_post_meta( $post_id, '_evoucherwp_template', $_POST[ '_evoucherwp_template'] );
        	$fields = array();
	        foreach ( $_POST as $key => $value ){
                if ( strncmp($key, '_field_', 7) === 0 ){
                	$fields[ $key ] = sanitize_text_field( $value );
                }
            } 

            update_post_meta( $post_id, '_fields' , maybe_serialize( $fields ) );

	        //Generate and save e-voucher GUID
	        //TODO: move from here!
	        $guid = get_post_meta( $post_id, '_guid', true );
	        // $sql = $wpdb->prepare( 'SELECT guid FROM ' . $wpdb->prefix . 'evoucherwp_vouchers WHERE post_id = %d', $post_id );
	        // $guid = $wpdb->get_var( $sql );
	        if ( empty( $guid ) ){
	        	$codestype = $_POST[ '_codestype' ];
	            if ( $codestype == 'single' ){
	            	$code = get_post_meta( $post_id, '_singlecode', true );
	            	if ( !empty( $code ) ){
		                $guid = sanitize_text_field( $code );
		            }
		            else{
		            	$guid = evwp_generate_guid( 6 );
		            }
	            }
	            elseif ( $codestype == 'sequential' ){
	            	$length = intval( $_POST[ '_codelength' ] );
	                if ( empty( $length ) || $length <= 0)
	                    $length = 6;
	                $guid = $wpdb->get_var("SELECT LPAD(COUNT(*) + 1, {$length}, 0) FROM {$wpdb->posts} WHERE post_type = 'evoucher' " );
	            }
	            else { //( $options[ 'evoucherwp_option_codestype' ] == 'random' ){
	            	$length = intval( $_POST[ '_codelength' ] );
	                if ( empty( $length ) || $length <= 0)
	                    $length = 6;
	                $guid = evwp_generate_guid( $length );
	            }

	            update_post_meta( $post_id, '_guid', $guid );
	            update_post_meta( $post_id, '_security_code', sanitize_text_field( $_POST[ 'evoucherwp_meta_nonce' ] ) );
	        }
	    }

		clean_post_cache( $post_id );
	}
}
