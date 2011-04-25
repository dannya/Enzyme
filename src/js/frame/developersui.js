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


// observe scroll of developers list view so we can keep headers in view
document.observe('dom:loaded', function() {
  if ($('developers-container')) {
		Event.observe($('developers-container'), 'scroll', function() {
			$('developers-headers').setStyle({
				top: $('developers-container').scrollTop + 'px'
			});
		});
  }
});



function interactSearch(event) {
	// sanity check
	if (!$('interact-field') || !$('interact-op') || !$('interact-value')) {
		return false;
	}

  // send off data
  new Ajax.Request(BASE_URL + '/get/developer-data.php', {
    method: 'post',
    parameters: { 
      field:    $('interact-field').value,
      operator: $('interact-op').value,
      value:    $('interact-value').value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // insert returned rows into table
        $('developers-body').insert(transport.responseText);

        // hide prompt
        $('developers-prompt').hide();
        
        // show table
        $('developers-headers').show();
        $('developers').show();
        
        // scroll to top of developers container
        $('developers-container').scrollTop = 0;
      }
    }
  });

	return false;
}