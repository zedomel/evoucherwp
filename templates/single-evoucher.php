<?php
	
if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
}


global $post;

// if requesting a voucher
$status = 'unavaiable';
if ( isset( $_GET[ 'evoucher' ] ) && ! empty( $_GET[ 'evoucher' ] ) ) {
    // get the details
    $voucher_guid = $_GET[ 'evoucher' ];
    $security_code = $_GET[ 'sc' ];

    // check the template exists
    $status = voucher_is_valid( $post->ID, $voucher_guid, $security_code );
}

error_log($status);

if ( $status === 'valid'){

	do_action( 'evoucherwp_create_voucher_html', $post->ID );

} elseif ( $status === 'unregistered' ){
	$out = "";
    $showform = true;

    get_header();
    ?>

    <div id="content" class="narrowcolumn" role="main">
    <div class="post category-uncategorized" id="voucher-<?php echo $voucher->guid; ?>">

    <?php
    // if registering
    if ( !empty( @$_POST["_email"] ) && !empty( @$_POST["_name"] ) ) {

        // if the email address is valid
        if ( is_email( trim( $_POST["_email"] ) ) ) {

            // register the email address
            $download_guid = save_download( $post->ID, trim( $_POST["_email"] ), trim( $_POST["_name"] ) );

            // if the guid has been generated
            $showform = empty( $download_guid );
            do_action( "evoucherwp_registered", $post->ID, ! $showform, $_POST["_email"], $_POST["_name"] );

        } else {
            echo  '<p>' . __( 'Sorry, provide a valid e-mail address. Please try again.', 'evoucherwp' ) . '</p>';
        }
    }

    if ( $showform ) {
    	do_action( 'evoucherwp_voucher_form', $post->ID, @$_POST[ '_email' ], @$_POST[ '_name' ] );
    }
    else{
    	$voucher = new EVWP_Voucher( $post );
    	echo '<a class="button get-voucher-btn" href="' . $voucher->get_download_url() . '">' . __( 'Get Voucher!', 'evoucherwp' ) . '</a>';
    }
    
    echo '</div></div>';
    get_footer();
}
else{
	evwp_404( $status );
}
?>


	