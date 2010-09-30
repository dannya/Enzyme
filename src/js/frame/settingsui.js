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


function saveChanges() {
	// collect data
	var theData = {};

	$('personal').select('input, select').each(function(item) {
		if (!item.disabled) {
			if (item.tagName == 'SELECT') {
				theData[item.name] = item.options[item.selectedIndex].value;
			} else {
				theData[item.name] = item.value;
			}
		}
	});


  // send off data
  new Ajax.Request(BASE_URL + '/get/change-personal.php', {
    method: 'post',
    parameters: { 
      data: Object.toQueryString(theData),
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // success
        if (typeof strings.change_personal_success == 'string') {
          alert(strings.change_personal_success);
        } else {
          alert('Your information has been changed');
        }
        
        // refresh if language has been changed
        if ((typeof result.languageChanged != 'undefined') && result.languageChanged) {
        	location.reload(true);
        }

      } else {
        // failure
        if (typeof strings.change_personal_failure == 'string') {
          alert(strings.change_personal_failure);
        } else {
          alert('Error: Personal information not changed');
        }
      }
    }
  });
}


function changePassword(theUser) {
	if (!$('data-oldpassword') || !$('data-newpassword')) {
	  return false;
	}
	
	// show error if field not filled
	if ($('data-oldpassword').value.empty()) {
		$('data-oldpassword').addClassName('failure');
	  return false;
	} else {
		$('data-oldpassword').removeClassName('failure');
	}

	if ($('data-newpassword').value.empty()) {
		$('data-newpassword').addClassName('failure');
	  return false;
	} else {
	  $('data-newpassword').removeClassName('failure');
	}
	

	// send off data
  new Ajax.Request(BASE_URL + '/get/change-password.php', {
    method: 'post',
    parameters: { 
      user:         theUser,
      old_password: $('data-oldpassword').value,
      new_password: $('data-newpassword').value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
      	// success
      	if (typeof strings.change_password_success == 'string') {
      		$('data-oldpassword').clear();
      		$('data-newpassword').clear();

          alert(strings.change_password_success);

      	} else {
          alert('Your password has been changed');
      	}

      } else {
      	// failure
        if (typeof strings.change_password_failure == 'string') {
          alert(strings.change_password_failure);
        } else {
          alert('Error: Password not changed');
        }
      }
    }
  });
}