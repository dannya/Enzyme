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


// setup regular AJAX updating for panels
var panelUpdateInterval = 180000;

document.observe('dom:loaded', function() {
  $$('div.container').each(function(panel) {
    if ($(panel.id)) {
      setInterval('panelRefresh(\'' + panel.id + '\')', panelUpdateInterval);
    }
  });
});


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


function panelRefresh(thePanel) {
  // clear cache, refresh frame
  if ((typeof thePanel == 'undefined') || !$(thePanel)) {
    return false;
  }

  // get panel content
  new Ajax.Request(BASE_URL + '/get/panel.php', {
    method: 'post',
    parameters: {
      panel: thePanel
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // success
        $(thePanel).update(transport.responseText);
        
        // highlight container
	      new Effect.Highlight($(thePanel), {
	        startcolor: '#d0f1c0',
	        duration: 0.5
	      });
      }
    }
  });
}