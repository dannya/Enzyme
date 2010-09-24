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


function changePassword(theUser) {
	if (!$('data-oldpassword') || $('data-oldpassword').value.empty() || 
	    !$('data-newpassword') || $('data-newpassword').value.empty()) {

	  return false;
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
      	if (typeof strings.change_password_success != 'undefined') {
          alert(strings.change_password_success);
      	} else {
          alert('Your password has been changed');
      	}

      } else {
      	// failure
        if (typeof strings.change_password_failure != 'undefined') {
          alert(strings.change_password_failure);
        } else {
          alert('Error: Password not changed');
        }
      }
    }
  });
}