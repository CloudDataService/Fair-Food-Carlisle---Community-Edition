$(document).ready(function() {



	// On load, get array of product ids
	var itemArray = [];
	$('.item_list .item_preview').each(function(index, singleItem) {
		itemArray.push( $(singleItem).attr('data-pid') );
	});

	// could divide the array into batches of 10 to avoid request timeout

	// run the checker
	checkItemsStock(itemArray);

});
