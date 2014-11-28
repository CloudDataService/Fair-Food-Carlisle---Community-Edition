

	// call ajax to check stock for an array of items
	function checkItemsStock(itemArray) {
		//ajax call
		toSend = itemArray + ""; //comma seperated string

		$.ajax({
			url: base_url + "ajax/check_items_availability",
			data: 'pids=' + toSend
		})
		.done(function(data) {
			//when done, update as appropriate
			$.each(data, function(pid, status) {
				$item = $('.item_list .item_preview[data-pid="' + pid + '"]');
				if (status == 'out') {
					//display message in place of "buy" button
					$item.find('.p_action')
						.append('<strong style="color:#922;">out of stock</strong>')
						.css('text-align', 'center')
						.css('color', 'center');
					$item.find('.p_action a').remove();
					//add a crazy banner thing
					/*
					$item.prepend('<div class="sellout"><div class="sellout-inner"><span>out of stock</span></div></div>');
					$item.find('.image').addClass('postsellout');
					*/
					//$item.css('background', 'red');
				} else if (status == 'in') {
					//$item.css('background', 'blue');
				} else {
					$item.find('.p_action').append('<strong>coming soon!</strong>');
					$item.find('.p_action').css('text-align', 'center');
					$item.find('.p_action a').remove();
					//$item.css('background', 'green');
				}
			});
		});
	}
