(function( ) {
    var prefix = '_field_';
    if( typeof tinymce !== 'undefined' ) {
        tinymce.create('tinymce.plugins.Evoucherwp', {
            /**
             * Initializes the plugin, this will be executed after the plugin has been created.
             * This call is done before the editor instance has finished it's initialization so use the onInit event
             * of the editor instance to intercept that event.
             *
             * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
             * @param {string} url Absolute URL to where the plugin is located.
             */
            init : function(ed, url) {
                ed.addButton('addfield', {
                    type: 'menubutton',
                    title: ed.getLang('evoucherwp.add_template_field'),
                    image: url + '/../images/addfield.png',
                    menu: [{
                        text: ed.getLang('evoucherwp.add_text_field'),
                        onclick: function(){
                            // Open window
                            ed.windowManager.open({
                                title: ed.getLang('evoucherwp.add_text_field'),
                                body: [
                                    {type: 'textbox', name: 'id', label: ed.getLang('evoucherwp.lbl_field_name') },
                                    {type: 'textbox', name: 'class', label: ed.getLang('evoucherwp.lbl_css_class') }
                                ],
                                onsubmit: function(e) {
                                    if (e.data.id != ""){
                                        ed.insertContent('<span data-type="text" id="' + prefix + e.data.id + '"' + ( e.data.class != "" ? 'class="' + e.data.class + '"' : "" ) 
                                            + '>' + e.data.id + '</span>');
                                    }
                                }
                            });
                        }
                    }, 
                    {
                        text: ed.getLang('evoucherwp.add_image_field'),
                        onclick: function(){
                            // Open window
                            ed.windowManager.open({
                                title: ed.getLang('evoucherwp.add_image_field'),
                                body: [
                                    {type: 'textbox', name: 'id', label: ed.getLang('evoucherwp.lbl_field_name') },
                                    {type: 'textbox', name: 'class', label: ed.getLang('evoucherwp.lbl_css_class') }
                                ],
                                onsubmit: function(e) {
                                    if (e.data.id != ""){
                                        ed.insertContent('<img data-type="img" id="' + prefix + e.data.id + '"' + ( e.data.class != "" ? 'class="' + e.data.class + '"' : "" ) 
                                            + ' src="' + url + '/../images/placeholder.png"/>');
                                    }
                                }
                            });
                        }
                    },
                    {
                        text: ed.getLang('evoucherwp.add_guid_field'),
                        onclick: function(){
                            ed.insertContent('<span data-type="guid" id="' + prefix + 'guid">0123456789</span>');
                        }
                    },
                    {
                        text: ed.getLang('evoucherwp.add_date_field'),
                        onclick: function(){
                            // Open window
                            ed.windowManager.open({
                                title: ed.getLang('evoucherwp.add_date_field'),
                                body: [
                                    {type: 'combobox', name: 'id', label: ed.getLang('evoucherwp.lbl_date_type'), values: [ 
                                        { text: ed.getLang('evoucherwp.lbl_date_op_expiry'), value: 'expirydate' }, 
                                        { text: ed.getLang('evoucherwp.lbl_date_op_start'), value: 'startdate' } ] },
                                    {type: 'textbox', name: 'df', label: ed.getLang('evoucherwp.lbl_date_df') },
                                    {type: 'textbox', name: 'class', label: ed.getLang('evoucherwp.lbl_css_class')}
                                ],
                                onsubmit: function(e) {
                                    if (e.data.id != ""){
                                        $df = ( e.data.df ? e.data.df : 'Y/m/d' );
                                        ed.insertContent('<span data-type="date" id="' + prefix + e.data.id + '"' + ( e.data.class != "" ? 'class="' + e.data.class + '"' : "" ) 
                                            + 'data-df="' + $df + '">' + $df + '</span>');
                                    }
                                }
                            });
                        }
                    }, 
                    ]
                });
            },
     
            /**
             * Creates control instances based in the incomming name. This method is normally not
             * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
             * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
             * method can be used to create those.
             *
             * @param {String} n Name of the control to create.
             * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
             * @return {tinymce.ui.Control} New control instance or null if no control was created.
             */
            createControl : function(n, cm) {
                return null;
            },
     
            /**
             * Returns information about the plugin as a name/value array.
             * The current keys are longname, author, authorurl, infourl and version.
             *
             * @return {Object} Name/value array containing information about the plugin.
             */
            getInfo : function() {
                return {
                    longname : 'EvoucherWP Buttons',
                    author : 'Jose A. Salim',
                    authorurl : 'https://github.com/zedomel/',
                    infourl : 'https://github.com/zedomel/evoucherwp',
                    version : "0.1"
                };
            }
        });
 
        // Register plugin
        tinymce.PluginManager.add( 'evoucherwp', tinymce.plugins.Evoucherwp );
    }
})();