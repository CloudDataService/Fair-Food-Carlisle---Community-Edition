$(document).ready(function() {
	
	// validate input
	$('#group_edit_form').validate(
	{
	
		rules: {
			bg_name: {
				required: true
			},
			bg_deliveryday: {
				required: true
			},
			bg_status: {
				required: true
			}
		}
	}
	);
						  
});