jQuery(document).ready(function( $ ){

    var file_frame, set_to_post_id;
    var wp_media_post_id = wp.media.model.settings.post.id; // Store the id

    jQuery('#_evoucherwp_template').change( function(){
        var template = jQuery( "#_evoucherwp_template option:selected" );
        if ( template.length > 0 && template.val() != "" ){
            jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: url,
                    data: {
                        'action'    : 'evoucherwp_select_template',
                        'template_id'  : template.val(), 
                    },
                    success: function( data ){
                        if ( data.valid ){
                            var container = jQuery('#evoucherwp_fields');
                            container.empty();
                            jQuery.each( data.fields, function( index, item ){
                                if ( item.id != '_field_guid'){
                                    var html = '<div class="inside">';
                                    var name = item.id.substr(7) + ': ';
                                    if ( item.type == 'span' ){
                                        html += '<label for="' + item.id + '">' + name;
                                        html += '<input id="' + item.id + '" name="' + item.id +  '" type="text"></label>';        
                                    }
                                    else if (item.type == 'img' ){
                                        html += '<div class="image-preview-wrapper">';
                                        html += '<p>' + name + '<img class="image-preview" src="" >';
                                        html += '<input type="hidden" class="input-image"  name="' + item.id +  '" id="' + item.id + '" value="">';
                                        html += '<input id="upload_image_button" type="button" class="button" value="Select Media"/></p>';
                                        html += '</div>';
                                    }
                                    html += '</div>';
                                    container.append(html);
                                }
                            });
                            //var okBtn = '<div class="evoucherwp-btn"><input id="create-voucher-btn" type="button" ' +
                            //   'class="button button-primary button-large" value="Create Voucher"/></div>';
                            //container.append(okBtn);

                            jQuery("#upload_image_button").on('click', function( event ){

                                event.preventDefault();

                                if ( file_frame ){
                                    // Set the post ID to what we want
                                    file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                                    // Open frame
                                    file_frame.open();
                                    return;
                                }
                                else {
                                    // Set the wp.media post id so the uploader grabs the ID we want when initialised
                                    wp.media.model.settings.post.id = set_to_post_id;
                                }

                                file_frame = wp.media.frames.file_frame = wp.media({
                                    title: 'Select a image',
                                    button: {
                                        text: 'Use this image'
                                    },
                                    multiple: false
                                });

                                file_frame.on('select', function(){
                                    attachment = file_frame.state().get('selection').first().toJSON();
                                    var input_image = jQuery( event.target ).prev('.input-image');
                                    input_image.val( attachment.id );
                                    var preview_image = input_image.prev('.image-preview').attr('src', attachment.url );

                                    // Restore the main post ID
                                    wp.media.model.settings.post.id = wp_media_post_id;
                                });

                                file_frame.open();
                            });
                            
                        }
                        else{
                            jQuery('.change-voucher').prepend('<div class="woocommerce-message">' + data.message + '</div>');
                        }
                    }
                });
        }
    });
});