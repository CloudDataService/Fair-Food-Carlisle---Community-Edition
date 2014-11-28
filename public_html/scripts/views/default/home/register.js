$(document).ready(function() {

	// validate input
	var container = $(".error-msg");
	$('#account_form').validate({

	//	errorContainer: container,
errorLabelContainer: container,
wrapper: 'li',
		rules: {
			bg_code: {
				remote: base_url+'home/check_group_code'
			},
			u_email: {
				remote: base_url+'home/check_email_unique'
			},
			u_email_confirm: {
				equalTo: '#u_email'
			},
			u_pword: {
				remote: base_url+'home/check_password_valid'
			},
			u_password_confirm: {
				equalTo: '#u_pword'
			}
		},
		messages: {
			u_email: {
				remote: 'E-mail address already in use'
				 },
			u_email_confirm: {
				equalTo: 'Please enter the same e-mail address'
				},
			u_pword: {
				remote: 'Password is not secure enough - Enter six or more characters'
				 },
			u_password_confirm: {
				equalTo: 'Please enter the same password'
				},
			bg_code: {
				remote: 'Code not accepted'
				 }
		}
		//errorClass: 'error-msg'
	});

	//specific registration cleverness for Carlisle
	$('.js-delivery_type').on('change', function() {
		// hide extra detail and rest
		$('.js-delivery-optional').hide();
		$('#u_bg_id').prop('selectedIndex', 0);
		$('.js-delivery-nonstandard-pcode').hide();
		// show extra detail for this type
		$('.js-optional-' + $(this).val() ).show();
		if ($(this).val() == 'home_delivery') {
			$('#u_addr_pcode').trigger('keypress'); //in case you go back the message needs to reappear
		}
	});
	// on load, change to the first type
	$('.js-delivery_type:first-child').attr('checked', true).trigger('change');


	// postcode must be in list
	$('.js-delivery-nonstandard-pcode').hide();
	$('#u_addr_pcode').on('keypress', function() {
		//on 'keypress' because 'change' requires focus to move on. Check length so we don't warn early.
		if ($(this).val().length > 2)
		{
			var parts = $(this).val().toUpperCase();
			parts = parts.split(' ');;
			var area = parts[0];
			if ( allowed_pcode_areas.indexOf( parts[0] ) < 0 )
			{
				$('.js-delivery-nonstandard-pcode').show();
			}
			else
			{
				$('.js-delivery-nonstandard-pcode').hide();
			}
		}
	});

	  $('#u_pword').pStrength({


	  	  'bind': 'keyup change',
	    'changeBackground': true,
	    'backgrounds'     : [['#fff', '#000'], ['#cc3333', '#FFF'], ['#cc6666', '#FFF'], ['#ff9999', '#FFF'],
	                        ['#e0941c', '#FFF'], ['#e8a53a', '#FFF'],  ['#66cc66', '#FFF'], ['#339933', '#FFF'], ['#006600', '#FFF']],
	    'passwordValidFrom': 60, // 60%
	    'onValidatePassword': function(percentage) { },
        'onPasswordStrengthChanged' : function(passwordStrength, percentage) {
            if ($(this).val()) {
                $.fn.pStrength('changeBackground', this, passwordStrength);
            } else {
                $.fn.pStrength('resetStyle', this);
            }
            $('#' + 'u_pword_display').html('Password Strength: ' + percentage + '%');
        }
    });


});
