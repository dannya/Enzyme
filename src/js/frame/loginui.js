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


function cancelApply(event) {
  if (typeof event != 'undefined') {
    Event.stop(event);
  }

  alert('cancel');
}