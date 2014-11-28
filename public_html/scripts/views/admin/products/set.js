$(document).ready(function() {

	//ui features
	$( ".datepicker" ).each(function() {
		$(this).datepicker();
		$(this).datepicker("option", "dateFormat", "dd/mm/yy");
	});

	//slug generation
	$("#p_name").on("change", function() {
		function gotSlug(data)
		{
			$("#p_slug").attr('value', data.slug );
		}

		var jqxhr = $.ajax({
			url: (base_url + 'ajax/get_url_slug/'+ escape($(this).val()) +'/product'),
			dataType: 'json',
			success: gotSlug
		});
	});

	//for the categories that we can add/remove
	$('#p_cat_add.btn').on('click', function() {

		var newItem = '<li style="margin-bottom:0;"><input type="hidden" name="p_cats[]" value="' + $('select#p_cat_add').val() + '" >' + $('select#p_cat_add :selected').text() + '<span class="p_cat_remove"><strong>X</strong></span></li>';

		$('ul#p_cat_list').append(newItem);

		$('select#p_cat_add :selected').remove();
		return false;
	});

	$('.p_cat_remove').on('click', function() {
		$(this).parent().remove();
	})

	/* check stuff before they submit */
	$('#add_product_form').submit(function() {
		/* check nothing in the season box that should be added */
		if ( $('input#p_pc_new_max_qty').val() != ''
				/*
				|| $('input#p_pc_new_min_qty').val() != ''
				|| $('input#p_pc_new_preseason_gap').val() != ''
				|| $('input#p_pc_new_period_start').val() != ''
				|| $('input#p_pc_new_period_end').val() != ''
				|| $('select#p_pc_new_id').val() != ''
				*/
				|| $('input#p_pc_new_max_qty').val() != ''

			) {
				alert('Please click "Add Season" to save when the product can be ordered');
				return false;
		} else {
			//let the form submit
		}

		/* check the category was added */
		if ( $('select#p_cat_add :selected').val() != '') {
			alert('Please click "Add" next to the category list.');
			return false;
		}
	});


	// validate input
	$('#add_product_form').validate(
	{

		rules: {
			p_name: {
				required: true
			},
			p_slug: {
				required: true
			},
			p_s_id: {
				required: true,
				number: true
			},
			p_price: {
				required: true,
				number: true
			},
			p_cost: {
				required: true,
				number: true
			}
		}
	}
	);


	/* Generic MULTIADD system */
	$('fieldset.multiadd .multiadd_btn').on('click', function() {

		var listID = 'ul#'+$(this).parent().attr('id')+'_list';
		var nextLI = $(listID).attr('multiadd_last');
		nextLI++;
		$(listID).attr('multiadd_last', nextLI);

		var fieldsInComplete = 0;
		var newItem = '<li style="margin-bottom:0;">';
		var newText = '';
		$(this).parent().find('input,select').each(function() {
			newItem += '<input type="hidden" multiadd_ref="'+nextLI+'" name="'+ $(this).attr('ref') +'['+ nextLI +']" value="'+ $(this).val() +'">';
			newText += $(this).attr('multiadd_label');
			if ($(this).text() != '') {
				newText += $(this).find(':selected').text(); //for select items
			} else {
				newText += $(this).val(); //for text inputs and most items
			}
			if ($(this).val() == '') {fieldsInComplete++;} //field required
		});
		newItem += newText + '. <span class="multiadd_remove"><img src="/img/icons/cross.png" title="Remove"></span>';
		newItem += '</li>';

		//all fields are required
		if (fieldsInComplete == 0) {
			$(listID).append(newItem);
			//clear the fields...
			$('#p_pc_new_id :selected').prop('selected', false);
			$('#p_pc_new_max_qty').val('');
			$('#p_pc_new_min_qty').attr('value', '');
			$('#p_pc_new_preseason_gap').attr('value', '');
			$('#p_pc_new_predelivery_gap').attr('value', '');
			$('#p_pc_new_period_start').attr('value', '');
			$('#p_pc_new_period_end').attr('value', '');
			$(this).parent().find('input,select').each(function() {
				$(this).attr('value', '')
			});
		} else {
			alert('Please fill in all fields in the row before clicking add.');
		}

		//$('select#p_cat_add :selected').remove();
		return false;
	});

	$('.multiadd_remove').on('click', function() {
		$(this).parent().remove();
	})


	/* end the MULTIADD system */


});
