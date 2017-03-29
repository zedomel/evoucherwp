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
});

