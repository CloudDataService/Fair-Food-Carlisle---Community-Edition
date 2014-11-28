$(document).ready(function() {

	// Super-duper "view orders" ajax request popup thing.
	$('.js-load-orders').on('click', function() {
		if ($('.js-loaded-orders').is(':visible'))
		{
			$('.js-loaded-orders').slideUp();
		}
		else
		{
			//show loading icon?
			$('.js-load-orders img.loadicon').css('visibility', 'visible');
			//load it
			$.ajax({
				url: base_url + "ajax/view_next_orders"
			})
			.done(function(data) {
				$('.js-load-orders .loadicon').css('visibility', 'hidden');
				$('.js-loaded-orders').html( data );
				$('.js-loaded-orders').slideDown();
			});
		}
	});



	//box lists (e.g. category index page)
	$(".item_list .item_preview img").on("click", function() {
		window.location.href = $(this).parent().parent().find('h3 a').attr('href');
	});

	// SimpleModal closing
	$("a.close-dialog").on("click", function(e) {
		e.preventDefault();
		$.modal.close();
	});

	// Delete link confirmation in modals
	$(".action-delete").on("click", function(e) {

		var el = $(this);

		// Get data attributes from element to allow the setting of information
		// displayed in the dialog.
		var data = el.data();

		$("form#delete_form div#hidden_inputs").empty();

		$("#delete_dialog").modal({
			overlayClose: true,
			opacity: 90,
			minWidth: 500,
			maxWidth: 500,
			minHeight: 100,
			maxHeight: 320,
			onShow: function() {
				var container = $(this.d.container);
				container.find("span.name").text(el.data("name"));
				container.find("p.text").html(el.data("confirm"));
				container.find("form#delete_form").attr("action", el.data("url"));

				// For each of the data attributes, create hidden form elements for them
				$.each(data, function(key) {
					// Use .attr() to get actual value, not the value that jQuery guesses using .data()
					var val = el.attr("data-" + key);
					$('<input type="hidden">')
						.attr("name", key)
						.val(val)
						.appendTo('form#delete_form div#hidden_inputs');
				});
			}
		});

		e.preventDefault();
		e.stopImmediatePropagation();

	});

	$('#menu').mainMenu();

});

$.browser={ msie: ( navigator.appName == 'Microsoft Internet Explorer') ? true : false };
var ie = $.browser.msie;

$.fn.mainMenu = function() {
	$(this).find('li').each(function() {
		if ($(this).find('> ul').size() > 0) {
			$(this).addClass('has_child');
		}
	});

	var closeTimer = null;
	var menuItem = null;

	function cancelTimer() {
		if (closeTimer) {
			window.clearTimeout(closeTimer);
			closeTimer = null;
		}
	}

	function close() {
		$(menuItem).find('> ul ul').hide();
		ie ? $(menuItem).find('> ul').fadeOut() : $(menuItem).find('> ul').slideUp(250);
		menuItem = null;
	}

	$(this).find('li').hover(function() {
		cancelTimer();

		var parent = false;
		$(this).parents('li').each(function() {
			if (this == menuItem) parent = true;
		});
		if (menuItem != this && !parent) close();

		$(this).addClass('hover');
		ie ? $(this).find('> ul').fadeIn() : $(this).find('> ul').slideDown(250);
	}, function() {
		$(this).removeClass('hover');
		menuItem = this;
		cancelTimer();
		closeTimer = window.setTimeout(close, 500);
	});

		$(this).find('ul a').css('display', 'inline-block');
		$(this).find('ul ul').css('top', '0');
}

