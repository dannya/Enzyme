/*-------------------------------------------------------+
| Enzyme
| Copyright 2010-2011 Danny Allen <danny@enzyme-project.org>
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


var newRowCounter = 0;


function addRepository() {
  if ($('repositories') && $('row-new-' + newRowCounter)) {
    // clone new row, so we can keep adding new rows after this one
    var newRow = $('row-new-' + newRowCounter).innerHTML;

    // pre-increment before usage (messes with minifier)
    ++newRowCounter;

    // add new row to page
    $('repositories').down('tbody').insert({ bottom: '<tr id="row-new-' + newRowCounter + '">' + newRow + '</tr>' });
  }
}


function deleteRepository(repo) {
  if (typeof repo == 'undefined') {
    return false;
  }

  // send off data
  new Ajax.Request(BASE_URL + '/get/repositories.php', {
    method: 'post',
    parameters: {
      repository: repo, 
      data:       null,
      dataType:   'delete'
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // remove repository row from table
        if ($('row-' + repo)) {
        	Element.remove($('row-' + repo));
        }
      }
    }
  });
}


function saveNewRepository(event) {
  if (typeof event == 'undefined') {
    return false;
  }

  // get data fields
  var parentRow = event.element().up('tr');
  var fields    = parentRow.select('input, select');

  // check that needed fields are filled
  var theData         = {};
  var unfilled        = false;
  var neededFields    = ['priority', 'id', 'type', 'hostname'];
  var optionalFields  = ['port', 'username', 'password', 'accounts-file', 'web-viewer'];

  fields.each(function(field) {
    if (neededFields.indexOf(field.readAttribute('name')) != -1) {
      // check if filled
      if (field.value.empty()) {
        field.addClassName('failure');
        unfilled = true;

      } else {
        field.removeClassName('failure');
        theData[field.readAttribute('name')] = field.value;
      }

    } else {
      // process optional fields
      if (optionalFields.indexOf(field.readAttribute('name')) != -1) {
        if (field.type == 'checkbox') {
          theData[field.readAttribute('name')] = field.checked;
        } else if (field.type == 'text') {
          theData[field.readAttribute('name')] = field.value;
        }
      }
    }
  });

  // if all needed filled, save data
  if (!unfilled) {
    new Ajax.Request(BASE_URL + '/get/repositories.php', {
      method: 'post',
      parameters: {
        repository: null,
        data:       Object.toQueryString(theData),
        dataType:   'new-repo'
      },
      onSuccess: function(transport) {
        var result = transport.headerJSON;

        if ((typeof result.success != 'undefined') && result.success) {
          var repo = parentRow.select('input[name="id"]').first().value;

          // change save button to delete repository button
          var accountButton = parentRow.select('div.repository-status').first();

          accountButton.addClassName('active');
          accountButton.writeAttribute('title', strings.button_repo);
          accountButton.writeAttribute('onclick', "deleteRepository('" + repo + "');");

          // change id of row
          parentRow.writeAttribute('id', 'row-' + repo);

          // add actions to fields
          var onChange = "saveChange('" + repo + "', event);";

          fields.each(function(field) {
            field.writeAttribute('onchange', onChange);
          });

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
}


function saveChange(repo, event) {
  if ((typeof repo == 'undefined') || (typeof event == 'undefined')) {
    return false;
  }

  var element     = event.element();

  // get new data
  var theDataType = element.readAttribute('name');
  var theData     = element.value;

  // send off data
  new Ajax.Request(BASE_URL + '/get/repositories.php', {
    method: 'post',
    parameters: {
      repository: repo, 
      data:       theData,
      dataType:   theDataType
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {       
        // show success
        element.addClassName('success');
        
        // if "id" (which is also the database identifier!) changes, update method calls and DOM
        if (theDataType == 'id') {
        	var repo       = element.value;
        	var parentRow  = element.up('tr');
        	var fields     = parentRow.select('input, select');

          // update delete repository button
          var repositoryButton = parentRow.select('div.repository-status').first();
          repositoryButton.writeAttribute('id', "active-" + repo);
          repositoryButton.writeAttribute('onclick', "deleteRepository('" + repo + "');");
          
          // change id of row
          parentRow.writeAttribute('id', 'row-' + repo);

          // add actions to fields
          var onChange = "saveChange('" + repo + "', event);";

          fields.each(function(field) {
            field.writeAttribute('onchange', onChange);
          });
        }
      }
    }
  });
}


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