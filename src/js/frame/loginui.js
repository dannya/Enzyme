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
	// focus username box
  if ($('login-username')) {
    $('login-username').focus();
  }
  
	// submit form on enter keypress
	$('authenticate').observe('keydown', function(event) {
	  if (event.keyCode === 13) {
	    $('authenticate').submit();
	  }
	});
});


function forgotPassword(event) {
	if (typeof event != 'undefined') {
		Event.stop(event);
	}

	alert('forgot password');
}


function apply(event, job) {
  if (typeof event != 'undefined') {
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

  // hide jobs, show application form
  $('jobs').hide();
  $('apply').show();
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
  	if (input.value.empty()) {
  		input.addClassName('failure');
  		filled = false;

  	} else {
  		input.removeClassName('failure');
  		theData[input.id] = input.value;
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
	        alert('Application failed');      
	      }
	    }
	  });
  }
}