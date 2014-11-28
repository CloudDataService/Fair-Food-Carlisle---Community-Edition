$(function() {

	$('#search').autocomplete({
		source: "ajax/product-search",
		minLength: 2,
		focus: function(event, ui) {
			$('#search').val(ui.item.p_name);
			return false;
		},
		select: function(event, ui) {
			//update fields
			$('#search').val(ui.item.p_name);
			$('#search_id').val(ui.item.p_id);
			//update stock info
			$('ul#stock-details').empty();
			console.log(ui.item.seasons);
			$.each(ui.item.seasons, function(index, season) {
				//safety checks
				if (season.p2pc_stock == null)
				{
					season.p2pc_stock = '<strong>ZERO</strong>';
				}
				if (season.pc_name == null)
				{
					season.pc_name = '<em>old-style season</em>';
				}
				//display info
				$('ul#stock-details').append('<li>' + season.p2pc_stock + ' items remaining for "' + season.pc_name + '" (' + season.pc_period_end + ' to ' + season.pc_period_end + ') </li>');
			});
			//finish off
			return false;
		}
	})
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
      return $( "<li>" )
        .append( "<a>" + item.p_name + "<br>" + item.s_name + "</a>" )
        .appendTo( ul );
    };


	$(".datepicker").datepicker({
		dateFormat: "dd/mm/yy"
	});

});
