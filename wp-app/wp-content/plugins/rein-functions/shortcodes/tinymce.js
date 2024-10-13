(function() {
    editor = tinymce.PluginManager.add('epcl_shortcodes', function( editor, url ) {
        editor.addButton( 'epcl_shortcodes', {
            // text: 'EP Shortcodes',
            icon: 'epcl-shortcode-button',
            type: 'button',
            title: 'EstudioPatagon Shortcodes',
            onclick: function() {
                editor.windowManager.open( {
                    title: 'EstudioPatagon Shortcodes',
                    width: ( jQuery(window).width() < 720 ) ? jQuery(window).width() : 850,
                    height: ( jQuery(window).height() < 720 ) ? jQuery(window).height() : 720,
                    url: url+'/lightbox/',
                    onsubmit: function( e ) {

                    },
                    custom_param: 'asd=2'
                });
            }
        });
    });
})();

