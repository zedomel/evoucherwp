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
                    title: 'Add Template Field',
                    image: url + '/../images/addfield.png',
                    menu: [{
                        text: 'Add Text Field',
                        onclick: function(){
                            // Open window
                            ed.windowManager.open({
                                title: 'Add Text Field',
                                body: [
                                    {type: 'textbox', name: 'id', label: 'Field name'},
                                    {type: 'textbox', name: 'class', label: 'CSS Class (optional)'}
                                ],
                                onsubmit: function(e) {
                                    if (e.data.id != ""){
                                        ed.insertContent('<span name="text" id="' + prefix + e.data.id + '"' + ( e.data.class != "" ? 'class="' + e.data.class + '"' : "" ) 
                                            + '>' + e.data.id + '</span>');
                                    }
                                }
                            });
                        }
                    }, 
                    {
                        text: 'Add Image Placeholder',
                        onclick: function(){
                            // Open window
                            ed.windowManager.open({
                                title: 'Add Image Placeholder',
                                body: [
                                    {type: 'textbox', name: 'id', label: 'Field Name', },
                                    {type: 'textbox', name: 'class', label: 'CSS Class (optional)'}
                                ],
                                onsubmit: function(e) {
                                    if (e.data.id != ""){
                                        ed.insertContent('<img name="img" id="' + prefix + e.data.id + '"' + ( e.data.class != "" ? 'class="' + e.data.class + '"' : "" ) 
                                            + ' src="' + url + '/../images/placeholder.png"/>');
                                    }
                                }
                            });
                        }
                    },
                    {
                        text: 'Add Voucher Number Field',
                        onclick: function(){
                            ed.insertContent('<span name="guid" id="' + prefix + 'guid">0123456789</span>');
                        }
                    },
                    {
                        text: 'Add Date Field',
                        onclick: function(){
                            // Open window
                            ed.windowManager.open({
                                title: 'Add Date Field',
                                body: [
                                    {type: 'combobox', name: 'id', label: 'Date type', values: [ 
                                        { text: 'Expiry date', value: 'expirydate' }, { text: 'Start date', value: 'startdate' } ] },
                                    {type: 'textbox', name: 'df', label: 'Date format' },
                                    {type: 'textbox', name: 'class', label: 'CSS Class (optional)'}
                                ],
                                onsubmit: function(e) {
                                    if (e.data.id != ""){
                                        ed.insertContent('<span name="date" id="' + prefix + e.data.id + '"' + ( e.data.class != "" ? 'class="' + e.data.class + '"' : "" ) 
                                            + '>' + ( e.data.df ? e.data.df : 'yyyy/mm/dd' ) + '</span>');
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