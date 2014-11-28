$(document).ready(function() {

	// validate input
	var container = $(".error-msg");
	$('#account_form').validate({
		
	//	errorContainer: container,
errorLabelContainer: container,
wrapper: 'li',
		rules: {

			u_pword: {
				remote: base_url+'home/check_password_valid'
			},
			u_password_confirm: {
				equalTo: '#u_pword'
			}
		},
		messages: {

			u_pword: {
				remote: 'Password is not secure enough - Enter six or more characters'
				 },
			u_password_confirm: {
				equalTo: 'Please enter the same password'
				},
		}
		//errorClass: 'error-msg'
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
