// JavaScript Document
$(document).ready(function() {

	// shows/hides "other" input elements depending on value of select element
	function other()
	{
		$('select.other').each(function() {

			if ($(this).val() == 'Other') {
				$(this).siblings('div').show();
			}
			else {
				$(this).siblings('div').hide();
			}

		});
	}

	// run on change
	$('select.other').change(function() {
		other();
	});

	// run on load
	other();


	//some rules only if it's a new user
	if (new_user == true) {
		var the_rules = {
			u_uname: {
				required: true,
				remote: base_url + 'admin/users/check_username_unique'
			},
			u_email: {
				required: true,
				email: true,
				remote: base_url + 'admin/users/check_email_unique'
			},
			u_email_confirm: {
				required: true,
				equalTo: '#u_email'
			},
			u_pword: {
				required: true,
				password_restrict: true
			},
			u_password_confirm: {
				required: true,
				equalTo: '#u_pword'
			},
			u_type: {
				required: true
			},
			u_status: {
				required: true
			}
		}
	} else {
		var the_rules = {
			u_uname: {
				required: true
			},
			u_type: {
				required: true
			},
			u_status: {
				required: true
			}
		}
	}

	// validate user form
	$('#add_user_form').validate({

		// add success label when element is valid
		success: function(label) {
			label.addClass("valid").text("")
		},

		// set validation rules
		rules: the_rules,

		// set custom error messages
		messages: {
			u_uname: {
				remote: 'Sorry, this username is already taken'
			},
			u_email: {
				remote: 'This email address is already registered'
			},
			u_email_confirm: {
				equalTo: 'Please correctly confirm your email address'
			}
		},

	});

	$('#permission_check_btn').click(function() {
		if ($('#permission_check_box').is(':visible')) {
			$('#permission_check_box').fadeOut();
		}
		else if ($('#u_pg_id :selected').val() == 0)
		{
			$('#permission_check_box').html('No permissions to the admin area will be granted. Use this option for general members who order produce.');
			$('#permission_check_box').fadeIn();
		}
		else
		{
			var jqxhr = $.ajax({
				url: (base_url + 'ajax/permissions-info/' + $('#u_pg_id :selected').val()),
				dataType: 'json',
				success: function(data) {
					$('#permission_check_box').html(data.htmltext);
					$('#permission_check_box').fadeIn();
				},
				error: function(data){
					$('#permission_check_box').html('Details of the permissions could not be checked right now.');
					$('#permission_check_box').fadeIn();
				}
			});
		}
	});
	$('#u_pg_id').change(function() {
		$('#permission_check_box').fadeOut();
	});

});
