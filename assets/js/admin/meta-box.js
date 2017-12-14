jQuery(document).ready(function( $ ){
    jQuery('#_evoucherwp_codestype').change( function(){
        var selected = jQuery(this).val();
        if ( selected == 'single'){
            jQuery('._evoucherwp_singlecode_field').removeClass('hide');
            jQuery('._evoucherwp_codelength_field').addClass('hide');
            jQuery('#_evoucherwp_codelength option[value=""]').prop('selected',true)
        }
        else{
            jQuery('._evoucherwp_singlecode_field').addClass('hide');   
            jQuery('._evoucherwp_codelength_field').removeClass('hide');
        }
    });    

    $('#_evoucherwp_use_default').change( function(){
        $('#voucher_options input:not(:hidden), #voucher_options select').prop('disabled', this.checked);
        $(this).prop('disabled', false );
        $('#voucher_options #_evoucherwp_live').prop('disabled', false );
    }).change();

    $('#_evoucherwp_use_default_header').change( function(){
        $('#voucher_header input:not(:hidden), #voucher_header textarea').prop('disabled', this.checked);
        $(this).prop('disabled', false );
    }).change();
});

