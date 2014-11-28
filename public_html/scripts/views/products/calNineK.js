
function CalNineK(theContainerRef, theOrderListRef, unitHolder) {
	var containerRef = theContainerRef; //e.g. '#orderingcal'
	var orderListRef = theOrderListRef; //e.g. '#orderdates'
	var productUnit = $('#oi_unit').html(); //TODO: tidy this by putting it as a var in the html?

	var datesOrdered = new Array(); //keeps track of the dates we want to order for

	//some needed vars from DOM/PHP
	var availableWeekFirst = {'value': $('.calNineK-config .availableWeekFirst').data('value'), 'label': $('.calNineK-config .availableWeekFirst').data('label')}; //was jsonDates['first'] (date object)
	var availableWeekSecond = {'value': $('.calNineK-config .availableWeekSecond').data('value'), 'label': $('.calNineK-config .availableWeekSecond').data('label')};

	//some vars that will come in handy later
	var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

	this.changeFrequency = function(newFreq, newQuantity) {
		$(orderListRef + ' li[freq]').remove();
		if ( newFreq== 'selected' )
		{
			//show the day selector again
			$('#orderitem_form label[for="orderitem_btn"] .stepcount').html('4');
			$('.singledateselection').slideDown('slower');
			$('#oi_frequency').val('selected'); //order frequency is now custom
		}
		else if ($('#oi_quantity').val() == '')
		{
			$('#commitment_notes').html('You must set a quantity before selecting a frequency.');
			$('#commitment_notes').fadeIn();
			$('#oi_frequency').val('selected');
		}
		else
		{
			//hide the day selector
			$('#orderitem_form label[for="orderitem_btn"] .stepcount').html('3');
			$('.singledateselection').slideUp('slower');
			//remove specific day orders
			datesOrdered = new Array();
			$('.calNineK-day-element.dayOrdering').removeClass('dayOrdering');
			$(orderListRef + ' li[data-date]').remove();
			//add kind of frequency do they want?
			if ( newFreq == 'weekly' )
			{
				var freqItemText = 'a week';
				var freqStartDay = availableWeekFirst;
				$('#or_startmilli').val( availableWeekFirst.value ); //set start date
			}
			else if ( newFreq == 'fortnight' )
			{
				var freqItemText = 'every other week';
				var freqStartDay = availableWeekFirst;
				$('#or_startmilli').val( availableWeekFirst.value ); //set start date
			}
			else if ( newFreq == 'everyother' )
			{
				var freqItemText = 'every other week';
				var freqStartDay = availableWeekSecond;
				$('#or_startmilli').val( availableWeekSecond.value ); //set start date
			}
			else if ( newFreq == 'monthly' )
			{
				var freqItemText = 'once a month';
				var freqStartDay = availableWeekFirst;
				$('#or_startmilli').val( availableWeekFirst.value ); //set start date
			}
			//add a frequency item to the list
			var newItem = '<li freq="'+ newFreq +'">' + newQuantity +' '+ productUnit
						+ ' once '+ freqItemText +', starting on the '
						+ freqStartDay.label
						//+'<img src="'+base_url+'img/icons/cross.png" class="removeOrderItem" title="remove order">'
						+'<span class="removeOrderItem btn" style="float:right; position:relative; top:3px;">Cancel</span><div class="clear"></div>'
						+'</li>';
			$(orderListRef).append(newItem);
		}
	} //end changeFrequency function


	//http://www.frequency-decoder.com/2006/07/20/correctly-calculating-a-date-suffix
	function daySuffix (d) {
		d = String(d);
		return d.substr(-(Math.min(d.length, 2))) > 3 && d.substr(-(Math.min(d.length, 2))) < 21 ? "th" : ["th", "st", "nd", "rd", "th"][Math.min(Number(d)%10, 4)];
	}

	this.addOrder = function(newDate, quantity) {
		//update our record
		var newOrder ={
			simpleDate: newDate,
			quantity: quantity,
			obj: new Date(newDate)
			//dateString?
			}
		datesOrdered.push(newOrder);
		//update the list of orders
		var newItem = '<li data-date="'+ newDate +'">' + quantity +' '+ productUnit
					+ ' on ' + newOrder.obj.getDate() + daySuffix(newOrder.obj.getDate()) + ' of '+ monthNames[newOrder.obj.getMonth()] +' '+ newOrder.obj.getFullYear()
					//+'<img src="'+base_url+'img/icons/cross.png" class="removeOrderItem" title="remove order">'
					+'<span class="removeOrderItem btn" style="float:right; position:relative; top:-6px;">Cancel</span>'
					+'</li>';
		$(orderListRef).append(newItem);
		//update the calendar
		$('.calNineK-day-element[data-date="'+ newOrder.simpleDate +'"]').addClass('dayOrdering');

	} //end addOrder function

	this.removeOrder = function(oldDate) {
		//find and remove from the array we track with
		$.each(datesOrdered, function(index, item) {
			if (item != undefined && item.simpleDate == oldDate) {
				datesOrdered = datesOrdered.splice(index, 1);
			}
		});
		//unstyle in calendar
		$('.calNineK-day-element[data-date="'+ oldDate +'"]').removeClass('dayOrdering');
		//remove in displayed list
		$('#orderdates li[data-date="'+ oldDate +'"]').remove();
	} //end removeOrder function

	this.populateForm = function(formObj) {
		//fill out a form with inputs of ordering
		$.each(datesOrdered, function(index, nextDate) {
			var nextHidden = '<input type="hidden" name="orders['+ nextDate.simpleDate +']" value="'+ nextDate.quantity +'">';
			formObj.append(nextHidden);
		});
	} //end populateForm function

	$(document).ready(function() {


		/* Now for some functions to turn the pages (months/years) */
		$('.calNineK-section-Year').on('click', '.calNineK-category-element', function() {
			//different year link clicked
			$('.calNineK-section-Year .calNineK-category-element.selected').removeClass('selected');
			$(this).addClass('selected');
			$('.calNineK-section-Month .calNineK-category-element').hide();
			$('.calNineK-category-element[data-year="' + $(this).data('date') + '"]').show().css('display', 'inline-block');
			$('.calNineK-category-element[data-year="' + $(this).data('date') + '"]:first').trigger('click');
		});
		$('.calNineK-section-Month').on('click', '.calNineK-category-element', function() {
			//different month link clicked
			$('.calNineK-section-Month .calNineK-category-element.selected').removeClass('selected');
			$(this).addClass('selected');
			$('.calNineK-day-element').hide();
			$('.calNineK-day-element[data-month="' + $(this).data('date') + '"]').show().css('display', 'inline-block');
		});

		//* On startup */
		$('.calNineK-no-js').hide(); //as we do have js.
		$('.calNineK-section-Year .calNineK-category-element.today').trigger('click'); //turn to this month, etc
	});


} //end of CalNineK class


$(document).ready(function() {

	var theCal = new CalNineK('#orderingcal', '#orderdates', '#oi_unit');

	//if they change the frequency option
	$('body').on('change', '#oi_frequency', function() {
		theCal.changeFrequency($('#oi_frequency').val(), $('#oi_quantity').val());
	});

	//if they remove an item in the summary list
	$('body').on('click', '.removeOrderItem', function() {
		theCal.changeFrequency('selected', $('#oi_quantity').val()); //order frequency is now custom
		var oldDate = $(this).parent().data('date');
		theCal.removeOrder(oldDate);
	});

	//when they click an day in the calendar
	$('.calNineK-day-element').click(function() {
		if ($('#oi_quantity').val() == '') {
			$('#commitment_notes').html('You must set a quantity above the calendar first');
			$('#commitment_notes').fadeIn();
		}
		else {
			$('#commitment_notes').fadeOut(); //clear warning message
			var newDate = $(this).data('date');
			var quantity = $('#oi_quantity').val();
			theCal.changeFrequency('selected', quantity); //make sure it's not on a recurring
			if ($(this).hasClass('dayOrdering')) {
				theCal.removeOrder(newDate);
			}
			else {
				theCal.addOrder(newDate, quantity);
			}

		}

	});

	//just before the order is submitted
	$('#orderitem_form').submit(function() {
			theCal.populateForm($('#orderitem_form'));
			return true;
	});


});
