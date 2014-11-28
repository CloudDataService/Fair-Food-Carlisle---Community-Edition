$(function() {


	$('#search').autocomplete({
		source: function(request, response) {
			//how to get the search results (option is a function so we can pass more detail to the ajax call)
			$.ajax({
				url: "ajax/product-search",
				dataType: "json",
				data: {
					term: request.term,
					return_seasons: "no",
					return_html: "yes"
				},
				success: function(data) {
					$('.item_list.products').empty(); // clear the results list
					response(data); //call function dealing with the result
					if (data.length == 0)
					{
						$('.item_list.products').html('<strong>Sorry, no produce found with that search term.</strong>');
					}
					$('.ac-search .loadicon').css('visibility', 'hidden'); //remove loading gif
				}
			})
		},
		minLength: 2,
		delay: 500, /* delay searching, incase they're still typing */
		focus: function(event, ui) {
			//item was hovered over
			$('#search').val(ui.item.p_name);
			return false;
		},
		select: function(event, ui) {
			// item was selected in dropdown list
			//update fields
			$('#search').val(ui.item.p_name);
			$('#search_id').val(ui.item.p_id);
			//update stock info
			$.each(ui.item.seasons, function(index, season) {
				//safety checks
				//display info
				//$('ul#stock-details').append('<li>' + season.p2pc_stock + ' items remaining for "' + season.pc_name + '" (' + season.pc_period_end + ' to ' + season.pc_period_end + ') </li>');
			});
			//finish off
			return false;
		}
	})
	.on("autocompletesearch", function(event, ui) {
		//when it starts to search
		$('.ac-search .loadicon').css('visibility', 'visible');
	})
	// override the function to create the "autocomplete suggestions list"
	.autocomplete( "instance" )._renderMenu = function( ul, items ) {
		$.each( items, function( index, item ) {
			$('.item_list.products').append( item.item_html );
		});

		//do a stock check again
		var itemArray = [];
		$('.item_list .item_preview').each(function(index, singleItem) {
			itemArray.push( $(singleItem).attr('data-pid') );
		});
		checkItemsStock(itemArray);
	};

	$('#ac-search-form').submit(function() {
		return false;
	});

});
