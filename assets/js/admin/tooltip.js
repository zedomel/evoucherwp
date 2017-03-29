jQuery(document).on( 'init_tooltips', function() {
	var tiptip_args = {
		'attribute': 'data-tip',
		'fadeIn': 50,
		'fadeOut': 50,
		'delay': 200
	};

	jQuery( '.tips, .help_tip, .evoucherwp-help-tip' ).tipTip( tiptip_args );
});

// Tooltips
jQuery( document.body ).trigger( 'init_tooltips' );