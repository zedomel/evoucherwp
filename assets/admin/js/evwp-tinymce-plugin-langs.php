<?php 

// This file is based on wp-includes/js/tinymce/langs/wp-langs.php

error_log('oi');

if ( ! defined( 'ABSPATH' ) )
    exit;

if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );

function evoucherwp_translation() {
    $strings = array(
        'add_template_field' => esc_js( __( 'Add Template Field', 'evoucherwp' ) ),
        'add_text_field' => __( 'Add Text Field', 'evoucherwp' ),
        'lbl_field_name' => __( 'Field name', 'evoucherwp' ),
        'lbl_css_class' => __( 'CSS Class (optional)', 'evoucherwp' ),
        'add_image_field' => __( 'Add Image Placeholder', 'evoucherwp' ),
        'add_guid_field' => __( 'Add Voucher Number Field', 'evoucherwp' ),
        'add_date_field' => __( 'Add Date Field', 'evoucherwp' ),
        'lbl_date_type' => __( 'Date type', 'evoucherwp' ),
        'lbl_date_op_expiry' => __( 'Expiry date', 'evoucherwp' ),
        'lbl_date_op_start' => __( 'Start date', 'evoucherwp' ),
        'lbl_date_df' => __( 'Date format', 'evoucherwp' ),
    );
    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.evoucherwp", ' . json_encode( $strings ) . ");\n";

    
     return $translated;
}
error_log(print_r($translated, true));
$strings = evoucherwp_translation();

?>