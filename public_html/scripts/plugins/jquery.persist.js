// JavaScript Document
(function( $ ){
	
	// counts down 10 seconds
	function count_down()
	{
		// get current value of seconds
		var second = parseInt($('span#seconds').text());
		
		// if seconds is greater than 0
		if(second > 0)
		{
			// decrement by 1 second
			$('span#seconds').text(second-1);
			
			// run this function again
			count_down_timeout = setTimeout(function () { count_down() }, 1000);
		}
		else
		{
			// if 10 seconds has past, redirect to log out script with timeout variable
			window.location = '/logout?timeout=1';
		}
	}
	
	// checks when user was last active, prompts them to persist session with dialog box, logs out if user does not respond
	function session_timer(settings)
	{
		// check to see if user's was active less than 10 minutes ago
		$.get("/persist", { check_persist_session: "1" }, function (data) {

			// if milliseconds elapses and user has not been active
			if(data == 'false')
			{	
				var html = '';
				html += '<div id="' + settings.div_id + '">';
				html += '<h2>Your session is about to time out.</h2>';
				html += '<p>You have been inactive for a period of time. For security reasons, you will be logged out in <span id="seconds">11</span> seconds.</p>'
				html += '</div>';
			
				// prompt to log out
				$('body').append(html);

				$('#' + settings.div_id).dialog({
					open: count_down(), // on open, start counting down seconds
					width: 500,
					resizable: false,
					modal: true,
					close: function () {
						
						// remove dialog div					
						$('#' + settings.div_id).remove();
						
						// stop count down
						clearTimeout(count_down_timeout);
			
						// persist session
						$.ajax({
							url: '/persist'			
						});
						
						// start session timer again
						setTimeout(function() { session_timer(settings); },
				   								settings.milliseconds);
							
						// exit script					
						return false;
					},
					buttons: {						
						"Continue working": function() {
							// when button is clicked, close event is triggered
							$(this).dialog( "close" );
						}
					}
				});
			}
			else
			{
				// start session timer again (the user is likely active in another tab)
				setTimeout(function() { session_timer(settings); },
				   						settings.milliseconds);
			}
													
		});
	}	
	
	// persist plugin
	$.fn.persist = function( options ) {
		
		// default settings
		var settings = {
			'milliseconds' : 900000, // 15 minutes
			'persist_url' : '/persist', // url used to persit user's session
			'div_id' : 'timeout' // the id of the dialog box
		};

		// extend default settings if any custom options are set
		if ( options ) { 
			$.extend( settings, options );
		}

		// start session timer
		setTimeout(function() { session_timer(settings); },
				   settings.milliseconds);

	};
	
})( jQuery );