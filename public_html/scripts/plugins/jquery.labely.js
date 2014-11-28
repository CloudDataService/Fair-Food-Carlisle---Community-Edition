// JavaScript Document
(function( $ ){
		
	// labely plugin
	$.fn.labely = function( options ) {
		
		// default settings
		var settings = {
			'class_name' : 'labely'
		};

		// extend default settings if any custom options are set
		if ( options ) { 
			$.extend( settings, options );
		}
		
		// when entering on the element
		$('div.' + settings.class_name + ' input[type="text"], div.' + settings.class_name + ' input[type="password"]').focus(function () {
			// get id the input element
			var id = $(this).attr('id');
			
			// if the input element is empty
			if( ! $(this).val() || $(this).attr('placeholder') != '' )
			{
				// hide the label for the input element
				$('label[for=' + id + ']').hide();
			}
		})
		
		// when leaving the element
		.blur(function () {
			// get id the input element
			var id = $(this).attr('id');
			
			// if the input element is empty
			if( ! $(this).val() )
			{
				// hide the label for the input element
				$('label[for=' + id + ']').show();
			}
		});
				
		var timeout = ($.browser.webkit == true ? 100 : 0);
		
		// Wait for "forever a chrome" and other webkit browsers to "autofill" input elements
		setTimeout(function () {
			
			$('div.' + settings.class_name + ' label').each(function () {
				// get id of input element the label is for
				var id = $(this).attr('for');
										
				// if that input element is empty
				if( $('#' + id).val() === '' )
				{
					// show the label
					$(this).show();	
				}
				else
				{
					// hide the label
					$(this).hide();	
				}
			});
			
		}, timeout);
		

	};
	
})( jQuery );