/*-------------------------------------------------------+
| Enzyme
| Copyright 2010 Danny Allen <danny@enzyme-project.org>
| http://www.enzyme-project.org/
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/


document.observe('dom:loaded', function() {
	// check for location hash
	var validJobs    = ['reviewer', 'classifier', 'editor', 'translator'];
	var specifiedJob = location.hash.sub('#', '', 1);

	if (!specifiedJob.empty() && (validJobs.indexOf(specifiedJob) != -1)) {
		// show specified job
		apply('', specifiedJob);

	} else {
	  if ($('login-username') && $('login-password')) {
	  	// focus username / password box
	  	if ($('login-username').value.empty()) {
	      $('login-username').focus();
	  	} else {
	  	  $('login-password').focus();
	  	}

	  } else if ($('reset-username')) {
	    // focus username box (ResetUi)
	    $('reset-username').focus();
	  }
	}


	// submit form on enter keypress
	if ($('authenticate')) {
		$('authenticate').observe('keydown', function(event) {
		  if (event.keyCode === 13) {
		  	$('authenticate-button').onclick();
		  }
		});

	} else if ($('reset')) {
    $('reset').observe('keydown', function(event) {
      if (event.keyCode === 13) {
        $('reset-button').onclick();
      }
    });
	}
});


function login() {
	if (!$('login-username') || !$('login-password')) {
		return false;
	}

	// check both fields are filled
	if ($('login-username').value.empty()) {
		$('login-username').addClassName('failure');
		$('login-username').focus();
		return false;

	} else {
	  $('login-username').removeClassName('failure');
	}
	
  if ($('login-password').value.empty()) {
    $('login-password').addClassName('failure');
    $('login-password').focus();
    return false;

  } else {
    $('login-password').removeClassName('failure');
  }


  // attempt login
	$('authenticate').submit();
}


function forgotPassword(event) {
	if (typeof event == 'object') {
		Event.stop(event);
	}
	
	if (!$('login-username')) {
		return false;
	}


  // ensure username field is filled
	if ($('login-username').value.empty()) {
		$('login-username').addClassName('failure');
		$('login-username').focus();

		return false;

	} else {
		// remove prompt
		$('login-username').removeClassName('failure');
	}


  // send reset password request
  new Ajax.Request(BASE_URL + '/get/reset-password.php', {
    method: 'post',
    parameters: {
      username: $('login-username').value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // success
        if (typeof strings.reset_success == 'string') {
          alert(strings.reset_success);
        } else {
          alert('Your password has been reset. Please check your registered email account for further instructions.');
        }

      } else {
        // error
        if (typeof strings.failure == 'string') {
          alert(strings.failure);
        } else {
          alert('Error');
        }
      }
    }
  });
}


function apply(event, job) {
	if (typeof event == 'object') {
    Event.stop(event);
  }

  if ((typeof job == 'undefined') || !$('jobs') || !$('apply') || !$('apply-job')) {
    return false;
  }


  // change job dropdown to selected job
	for (var i = 0; i < $('apply-job').length; i++){
	  if (job == $('apply-job').options[i].value) {
	    $('apply-job').selectedIndex = i;
      break;
	  }
	}
	
	// enable paths input?
	if ($('apply-paths')) {
		if ((job == 'reviewer') || (job == 'classifier')) {
			$('apply-paths').disabled = false;
		} else {
		  $('apply-paths').disabled = true;
		}
	}

  // hide jobs, show application form
  $('jobs').hide();
  $('apply').show();
}


function checkPathsInput(event) {
  if (typeof event == 'undefined') {
    return false;
  }

  var element   = event.element();
  var selected  = element.options[element.selectedIndex].value;

  if ((selected == 'reviewer') || (selected == 'classifier')) {
  	$('apply-paths').disabled = false;
  } else {
  	$('apply-paths').disabled = true;
  }
}


function cancelApply(event) {
  if (typeof event != 'undefined') {
    Event.stop(event);
  }


  // clear all fields
  $('apply-form').select('select, textarea, input[type="text"]').each(function(input) {
  	input.value = '';
  });

  // hide application form, show jobs
  $('apply').hide();
  $('jobs').show();
}


function submitApply(event) {
  if (typeof event != 'undefined') {
    Event.stop(event);
  }


  if (!$('apply-form')) {
    return false;
  }


  // check all fields are filled
  var filled  = true;
  var theData = {};

  $('apply-form').select('select, textarea, input[type="text"]').each(function(input) {
  	if (!input.disabled && !input.hasClassName('optional') && input.value.empty()) {
  		input.addClassName('failure');
  		filled = false;

  	} else {
  		input.removeClassName('failure');
  		
  		if (input.hasClassName('prompt')) {
  		  theData[input.id] = '';
  		} else {
  		  theData[input.id] = input.value;
  		}
  	}
  });
  
  
  // if all fields are filled, send off application
  if (filled) {
	  new Ajax.Request(BASE_URL + '/get/apply.php', {
	    method: 'post',
	    parameters: {
	      data: Object.toQueryString(theData)
	    },
	    onSuccess: function(transport) {
	      var result = transport.headerJSON;
	
	      if ((typeof result.success != 'undefined') && result.success) {
	        // success
	        $('apply').hide();
	        $('success-message').show();

	      } else {
	        // error
	        if (typeof strings.application_failure == 'string') {
	          alert(strings.application_failure);
	        } else {
	          alert('Error: Application failed');
	        }      
	      }
	    }
	  });
  }
}


function resetPassword(theCode) {
	if ((typeof theCode != 'string') ||
	    !$('reset-username') || !$('reset-password')) {

    return false;		
	}
	

	// check both boxes are filled
	if ($('reset-username').value.empty()) {
	  $('reset-username').addClassName('failure');
	  $('reset-username').focus();
	  return false;

	} else {
		$('reset-username').removeClassName('failure');
	}

  if ($('reset-password').value.empty()) {
    $('reset-password').addClassName('failure');
    $('reset-password').focus();
    return false;

  } else {
    $('reset-password').removeClassName('failure');
  }


	// send password change request
  new Ajax.Request(BASE_URL + '/get/reset-password.php', {
    method: 'post',
    parameters: {
      code:         theCode,
      new_password: $('reset-password').value,
      username:     $('reset-username').value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // success, redirect to login page, specifying username
			  location.href = BASE_URL + '/?username=' + $('reset-username').value;

      } else {
        // error
        if (typeof strings.failure == 'string') {
          alert(strings.failure);
        } else {
          alert('Error');
        }
      }
    }
  }); 
}