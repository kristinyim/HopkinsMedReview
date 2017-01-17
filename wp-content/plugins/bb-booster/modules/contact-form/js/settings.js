(function($){

	FLBuilder.registerModuleHelper('contact-form', {
    
		init: function()
		{
			var form      = $( '.fl-builder-settings' ),
				action    = form.find( 'select[name=success_action]' );
				
			this._actionChanged();
			
			action.on( 'change', this._actionChanged );

			// Button background color change
			$( 'input[name=btn_bg_color]' ).on( 'change', this._bgColorChange );			
			this._bgColorChange();
		},
    
		_actionChanged: function()
		{
			var form      = $( '.fl-builder-settings' ),
				action    = form.find( 'select[name=success_action]' ).val(),
				url       = form.find( 'input[name=success_url]' );
				
			url.rules('remove');
				
			if ( 'redirect' == action ) {
				url.rules( 'add', { required: true } );
			}
		},

		_bgColorChange: function()
		{
			var bgColor = $( 'input[name=btn_bg_color]' ),
				style   = $( '#fl-builder-settings-section-btn_style' );
			

			if ( '' == bgColor.val() ) {
				style.hide();
			}
			else {
				style.show();
			}
		}    
    
	});

})(jQuery);