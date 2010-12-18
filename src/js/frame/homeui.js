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


function changelog(event) {
  if (typeof event == 'object') {
    Event.stop(event);
  }
  
  if (!$('changelog')) {
    return false;
  }
  
  if (!$('changelog').visible()) {
  	$('changelog').appear({ duration: 0.3 });
  } else {
  	$('changelog').fade({ duration: 0.3 });
  }
}


function panelRefresh(theId) {
  // clear cache, refresh frame
  if ((typeof theId == 'undefined') || !$('panel-' + theId)) {
    return false;
  }

  // get panel content
  new Ajax.Request(BASE_URL + '/get/panel.php', {
    method: 'post',
    parameters: {
      panel: theId
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // success
        $('panel-' + theId).update(transport.responseText);
        
        // highlight container
	      new Effect.Highlight($('panel-' + theId), {
	        startcolor: '#d0f1c0',
          endcolor: '#fff',
	        duration: 0.5
	      });
      }
    }
  });
}