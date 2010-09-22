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


var newRowCounter = 0;


function addUser() {
  if ($('users') && $('row-new-' + newRowCounter)) {
    // clone new row, so we can keep adding new rows after this one
    var newRow = $('row-new-' + newRowCounter).innerHTML;

    $('users').down('tbody').insert({ bottom:'<tr id="row-new-' + ++newRowCounter + '">' + newRow + '</tr>' });
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
  var theData         = { };
  var unfilled        = false;
  var neededFields    = ['username', 'email', 'firstname', 'lastname'];
  var optionalFields  = ['permission-admin', 'permission-editor', 'permission-reviewer', 'permission-classifier', 'paths'];

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

          accountButton.writeAttribute('id', 'active-dkite');
          accountButton.writeAttribute('title', '');
          accountButton.writeAttribute('onclick', "setAccountActive('dkite', false);");

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