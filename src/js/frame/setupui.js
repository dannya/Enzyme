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
        location.reload(true);

      } else {
        // error
        alert('Failed to save settings');      
      }
    }
  });
}