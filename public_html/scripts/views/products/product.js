$(document).ready(function() {

	//check the quantity values are valid
	/*
	$('form#orderitem_form select, form#orderitem_form input').change(function() {
		if ($('select#oi_period').val() != '' && $('select#oi_fequency').val() != '') {
			//run an ajax check
			$.ajax({
				url: '/ajax/check_product_allowance/'+ $('select#oi_fequency').val() + '/' + $('select#oi_period').val() +'/'+ $('input#oi_quantity').val(),
				success: function(res) {
					if (res.status == 'err') {
						$('#commitment_notes').html('That combination is not available for this product.');
						$('#orderitem_btn').attr('disabled', 'disabled');
					} else {
						var showText = '';
						if (res.accepted == false) {
							showText += '<strong style="color:red;">That quantitiy is not available.</strong> ';
							$('#orderitem_btn').attr('disabled', 'disabled');
						} else {
							$('#orderitem_btn').removeAttr('disabled');
						}
						if (res.min != '' && res.max != '') {
							showText += 'For this frequency, orders must be quantities of '+ res.min +' to '+ res.max +'.';
						}
						$('#commitment_notes').html(showText);
					}
					//console.log(res);
				}
			});
		} else {
			//not enough info to work out if this is right
			$('#commitment_notes').html('');
		}
	});
	*/




	//order only integers of product
	$('#oi_quantity').focusout(function() {
		$('#oi_quantity').val( Math.floor($('#oi_quantity').val()) );
		if ($('#oi_quantity').val() < 1) {
			$('#oi_quantity').val('');
		}
	});

});

	submitFormOkay = false;

	//where you going?
	window.onbeforeunload = function(e){
		if (!submitFormOkay && $('ul#orderdates li').size() > 0) {
			submitFormOkay = false;
			return 'You have selected dates for delivery but not clicked the button to order. The produce will not be ordered if you leave this page.';
		}
		submitFormOkay = false;
	}
