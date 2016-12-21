<?php 

// Change the columns for the edit admin screen
function evoucherwp_change_voucher_columns( $cols ) {
  $cols = array(
    'cb'       => '<input type="checkbox" />',
    'url'      => __( 'URL',      'trans' ),
    'referrer' => __( 'Referrer', 'trans' ),
    'host'     => __( 'Host', 'trans' ),
  );
  return $cols;
}
//add_filter( "manage_evoucher_posts_columns", "evoucherwp_change_voucher_columns" );


// // Change the columns for the edit admin screen
// function evoucherwp_change_voucher_template_columns( $cols ) {
//   $new_cols = array(
//     'cb'       => '<input type="checkbox" />',
//     'url'      => __( 'URL',      'trans' ),
//     'referrer' => __( 'Referrer', 'trans' ),
//     'host'     => __( 'Host', 'trans' ),
//   );
//   return $cols;
// }
// add_filter( "manage_evoucher_template_posts_columns", "evoucherwp_change_voucher_template_columns" );
?>