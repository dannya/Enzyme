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


function addUser() {
  if ($('users') && $('row-new-' + newRowCounter)) {
    // clone new row, so we can keep adding new rows after this one
    var newRow = $('row-new-' + newRowCounter).innerHTML;

    // pre-increment before usage (messes with minifier)
    ++newRowCounter;

    // add new row to page
    $('users').down('tbody').insert({ bottom: '<tr id="row-new-' + newRowCounter + '">' + newRow + '</tr>' });
    
    // scroll to and highlight new row
    if ($('row-new-' + newRowCounter)) {
    	$('row-new-' + newRowCounter).scrollTo();
    	
      new Effect.Highlight($('row-new-' + newRowCounter), {
        startcolor: '#d0f1c0',
        duration: 1
      });
    }
  }
}


function saveNewAccount(event) {
  if (typeof event == 'undefined') {
    return false;
  }

  // get data fields
  var parentRow = event.element().up('tr');
  var fields    = parentRow.select('input');

  // check that needed fields are filled
  var theData         = {};
  var unfilled        = false;
  var neededFields    = ['username', 'email', 'firstname', 'lastname'];
  var optionalFields  = ['permission-admin', 'permission-editor', 'permission-reviewer', 'permission-classifier', 'permission-translator', 'paths'];

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
    new Ajax.Request(BASE_URL + '/get/users.php', {
      method: 'post',
      parameters: {
        username: null, 
        data:     Object.toQueryString(theData),
        dataType: 'new-user'
      },
      onSuccess: function(transport) {
        var result = transport.headerJSON;

        if ((typeof result.success != 'undefined') && result.success) {
          var username = parentRow.select('input[name="username"]').first().value;

          // change save button to account active button
          var accountButton = parentRow.select('div.account-status').first();
          accountButton.addClassName('active');

          accountButton.writeAttribute('id', 'active-' + username);
          accountButton.writeAttribute('title', strings.button_user);
          accountButton.writeAttribute('onclick', "setAccountActive('" + username + "', false);");

          // change id of row
          parentRow.writeAttribute('id', 'row-' + username);

          // change id of paths input (needed to show / hide)
          var paths = parentRow.select('input[name="paths"]').first();

          if (paths) {
            paths.writeAttribute('id', 'paths-' + username);
          }

          // add id's to checkboxes
          parentRow.select('input[type="checkbox"]').each(function(item) {
            item.writeAttribute('id', item.readAttribute('name') + '-' + username);
          });

          // add actions to fields
          var onChange = "saveChange('" + username + "', event);";

          fields.each(function(field) {
            field.writeAttribute('onchange', onChange);
          });

          // manage paths
          managePaths(username, false);

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


function setAccountActive(user, state) {
  if ((typeof user == 'undefined') || (typeof state == 'undefined')) {
    return false;
  }

  // send off data
  new Ajax.Request(BASE_URL + '/get/users.php', {
    method: 'post',
    parameters: {
      username: user, 
      data:     state,
      dataType: 'active'
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // change button appearance, and action
        if ($('active-' + user)) {
          if (state == true) {
            var className  = 'active';
            var newState   = 'false';
          } else {
            var className  = 'inactive';
            var newState   = 'true';
          }

          $('active-' + user).writeAttribute('class', "account-status " + className);
          $('active-' + user).writeAttribute('onclick', "setAccountActive('" + user + "', " + newState + ");");
        }
      }
    }
  });
}


function saveChange(user, event) {
  if ((typeof user == 'undefined') || (typeof event == 'undefined')) {
    return false;
  }

  var element     = event.element();

  // get new data
  var elementType = element.readAttribute('type');  
  var theDataType = element.readAttribute('name');

  if (elementType == 'checkbox') {
    var theData = element.checked;
  } else {
    var theData = element.value;
  }
  
  
  // validate?
  if (theDataType == 'email') {
	  if (!validateEmail(theData)) {
	    element.addClassName('failure');
	    return false;

	  } else {
	  	element.removeClassName('failure');
	  }
  }


  // send off data
  new Ajax.Request(BASE_URL + '/get/users.php', {
    method: 'post',
    parameters: {
      username: user,
      data:     theData,
      dataType: theDataType
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        if ((theDataType == 'permission-reviewer') || (theDataType == 'permission-classifier')) {
          // show / hide
          managePaths(user, theData);
        }

        // show success
        element.addClassName('success');
        
        // if "username" (which is also the database identifier!) changes, update method calls and DOM
        if (theDataType == 'username') {
        	alert('s');
          var username   = element.value;
          var parentRow  = element.up('tr');
          var fields     = parentRow.select('input');
    
          // update account status button
          var accountButton = parentRow.select('div.account-status').first();
          accountButton.writeAttribute('id', "active-" + username);
          accountButton.writeAttribute('onclick', "setAccountActive('" + username + "', " + accountButton.hasClassName('inactive') + ");");

          // change id of row
          parentRow.writeAttribute('id', 'row-' + username);

          // change id of paths input (needed to show / hide)
          var paths = parentRow.select('input[name="paths"]').first();

          if (paths) {
            paths.writeAttribute('id', 'paths-' + username);
          }

          // add id's to checkboxes
          parentRow.select('input[type="checkbox"]').each(function(item) {
            item.writeAttribute('id', item.readAttribute('name') + '-' + username);
          });

          // add actions to fields
          var onChange = "saveChange('" + username + "', event);";

          fields.each(function(field) {
            field.writeAttribute('onchange', onChange);
          });
        }
      }
    }
  });
}


function managePaths(user, state) {
  if ((typeof user == 'undefined') || (typeof state == 'undefined')) {
    return false;
  }

  if ($('paths-' + user)) {
    if (state == true) {
      $('paths-' + user).show();

    } else {
      // if both classifier and reviewer are unchecked, hide paths
      if (($('permission-reviewer-' + user) && !$('permission-reviewer-' + user).checked) &&
          ($('permission-classifier-' + user) && !$('permission-classifier-' + user).checked)) {

        $('paths-' + user).hide();
      }
    }
  }
}


function manageApplication(context, number) {
	if ((typeof context == 'undefined') || (typeof number == 'undefined') ||
	    !$('application-' + number) || !$('username-' + number)) {

		return false;
	}


	// ensure username is provided when approving
	if ((context == 'approve') && $('username-' + number).value.empty()) {
	  $('username-' + number).addClassName('failure');
	  $('username-' + number).focus();

	  return false;

	} else {
		$('username-' + number).removeClassName('failure');
	}


	// check that email field is valid
  if ((context == 'approve') && !validateEmail($('email-' + number).value)) {
    $('email-' + number).addClassName('failure');
    $('email-' + number).focus();

    return false;

  } else {
    $('email-' + number).removeClassName('failure');
  }


  // ask for confirmation?
  if ((context == 'decline') && !confirm(strings.decline_application)) {
    return false;
  } 	


	// collect data
	var theData = {};

	$('application-' + number).select('input').each(function(field) {
    if (field.type == 'checkbox') {
      theData[field.readAttribute('name')] = field.checked;
    } else {
    	theData[field.readAttribute('name')] = field.value;
    }
	});


  // send off data
  new Ajax.Request(BASE_URL + '/get/users.php', {
    method: 'post',
    parameters: {
      username: null, 
      data:     Object.toQueryString(theData),
      dataType: context + '-application'
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
      	if (context == 'approve') {
	        // reload page to reflect application + user changes
	        location.reload(true);

      	} else if (context == 'decline') {
          if ($('application-' + number)) {
          	// remove element from the page
          	new Effect.SlideUp($('application-' + number).up('div.application'),
          	                   { duration: 0.3,
	          	                   afterFinish: function() {
	          	                   	 // ensure element is removed from the DOM
	          	                   	 Element.remove($('application-' + number).up('div.application'));
	          	                   	 
																	 // decrement applications counter
                                   var numApplications = $('applications').select('div.application').size();

																	 if ($('num-applications')) {
																		 $('num-applications').update(sprintf(strings.num_applications, numApplications));
																	 }

																	 // remove whole container if no applications left
																	 if ((numApplications < 1) && $('applications-container')) {
																	 	 Element.remove($('applications-container'));
																	 }
	          	                   }
          	                   });
          }
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


function availableJob(event, theJob) {
  if ((typeof event == 'undefined') || (typeof theJob == 'undefined')) {
    return false;
  }

  var element = event.element();

  // send off change
  new Ajax.Request(BASE_URL + '/get/available-jobs.php', {
    method: 'post',
    parameters: {
    	job:    theJob,
      active: element.checked
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // show success
        new Effect.Highlight(element.up('label'), {
        	startcolor: '#d0f1c0',
        	restorecolor: '#fff',
        	duration: 3
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