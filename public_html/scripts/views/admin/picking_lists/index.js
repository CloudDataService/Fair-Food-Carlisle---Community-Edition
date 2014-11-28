$(document).ready(function() {

	$(".datepicker").datepicker({
		dateFormat: "dd/mm/yy",
		buttonImage: "calendar.gif",
		appendText: " <i class=\"fa fa-calendar\"></i>",
		defaultDate: -7
	});
	
});