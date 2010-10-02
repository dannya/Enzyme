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


function setupDatabase() {
	if (!$('setup-database-details') || !$('setup-database-output')) {
    return false;	
	}

  // disable button
  $('setup-database-button').writeAttribute('disabled', 'disabled');

	// setup database
  new Ajax.Request(BASE_URL + '/get/setup-database.php', {
    method: 'post',
    onSuccess: function(transport) {
      var result = transport.headerJSON;
      
      // show output
      if (typeof result.output != 'undefined') {
        $('setup-database-output').update(result.output);

        // change content display
        $('setup-database-details').hide();
        $('setup-database-output').appear({ duration:0.3 });
      }

      if ((typeof result.success != 'undefined') && result.success) {
        // success, change button
        if ($('setup-database-button') && $('setup-database-next')) {
        	$('setup-database-button').hide();
        	$('setup-database-next').show();

        } else {
          location.reload('true');
        }

      } else {
        // error
        if ($('setup-database-error')) {
          $('setup-database-error').appear({ duration:0.3 });

        } else {
	        if (typeof strings.failure == 'string') {
	          alert(strings.failure);
	        } else {
	          alert('Error');
	        }
        }
      }
    }
  });
}


function setupUser() {
  if (!$('setup-user-username') || !$('setup-user-username')) {
    return false; 
  }
  
  // check that needed fields are filled
  var fields          = $('setup-user-details').select('input');

  var theData         = {};
  var unfilled        = false;
  var neededFields    = ['username', 'password', 'email', 'firstname', 'lastname'];

  fields.each(function(field) {
    if (neededFields.indexOf(field.readAttribute('name')) != -1) {
	    // check if filled
	    if (field.value.empty()) {
	      field.addClassName('failure');

        // focus first unfilled field
	      if (!unfilled) {
	      	field.focus();
	      }

	      unfilled = true;
	
	    } else {
	      field.removeClassName('failure');
	      theData[field.readAttribute('name')] = field.value;
	    }
    }
  });
  

  // setup database
  if (!unfilled) {
	  new Ajax.Request(BASE_URL + '/get/setup-user.php', {
	    method: 'post',
	    parameters: {
	    	data: Object.toQueryString(theData)
	    },
	    onSuccess: function(transport) {
	      var result = transport.headerJSON;
	
	      if ((typeof result.success != 'undefined') && result.success) {
	        // success, reload to go to next stage
	        location.reload('true');
	
	      } else {
	        // disable button
	        $('setup-user-button').writeAttribute('disabled', 'disabled');
	
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
}


function saveSetup() {
	if (!$('setup-form')) {
	  return false;
	}


	// iterate through form fields and serialize, skipping those with example values
	var theData = {};

	$('setup-form').select('select, input[type="text"]').each(function(input) {
		if (!input.hasClassName('prompt')) {
		  theData[input.id] = input.value;
		}
	});


	// send off form data
  new Ajax.Request(BASE_URL + '/get/setup.php', {
    method: 'post',
    parameters: {
      data: Object.toQueryString(theData)
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
      	// success
      	if (!$('sidebar')) {
          location.reload(true);
      	} else {
      	  location.href = '/';
      	}

      } else {
        // error
        if (typeof strings.settings_failure == 'string') {
          alert(strings.settings_failure);
        } else {
          alert('Failed to save settings');
        }
      }
    }
  });
}