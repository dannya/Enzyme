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


function createNewIdea() {
	if ($('ideas-prompt')) {
		$('ideas-prompt').hide();
	}

	if ($('idea_new')) {
		if (!$('idea_new').visible()) {
			// show hidden row
	    $('idea_new').show();

		} else {
	    // highlight
	    new Effect.Highlight($('idea_new'), {
	      startcolor: '#d0f1c0',
	      duration: 0.5
	    });
		}

    // set input box as focused
    if ($('idea-intro-new')) {
		  $('idea-intro-new').focus();
    }
	}
}


function expandIdea(itemNum) {
  if ((typeof itemNum == 'undefined') || !$('idea_' + itemNum)) {
    return false;
  }

  $('idea_' + itemNum).toggleClassName('ideaExpand');
}


function saveIdea() {
  if (!$('idea-intro-new')) {
    return false;
  }

  // send off change
  new Ajax.Request(BASE_URL + '/get/change-feature.php', {
    method: 'post',
    parameters: {
      newItem: $('idea-intro-new').value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        location.reload(true);

      } else {
        // failure
        if (typeof strings.failure == 'string') {
          alert(strings.failure);
        } else {
          alert('Error');
        }
      }
    }
  });
}


function claimIdea(itemNum, theDate, theAuthor) {
  if (typeof itemNum == 'undefined') {
  	return false;
  }

  // change date, author, status
  var theValues     = {};

  theValues.date    = theDate;
  theValues.author  = theAuthor;
  theValues.status  = 'contacting';


  // send off change
  new Ajax.Request(BASE_URL + '/get/change-feature.php', {
    method: 'post',
    parameters: {
      date:     '0000-00-00',
      number:   itemNum,
      values:   Object.toQueryString(theValues)
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        location.reload(true);

      } else {
      	// failure
        if (typeof strings.failure == 'string') {
          alert(strings.failure);
        } else {
          alert('Error');
        }
      }
    }
  });
}


function deleteIdea(itemNum) {
  if (typeof itemNum == 'undefined' || !confirm(strings.delete_idea)) {
    return false;
  }


  // send off change
  new Ajax.Request(BASE_URL + '/get/change-feature.php', {
    method: 'post',
    parameters: {
    	dataType: 'delete',
      number:   itemNum
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // remove from page
        if ($('idea_' + itemNum)) {
        	$('idea_' + itemNum).remove();

        } else {
        	location.reload(true);
        }

      } else {
        // failure
        if (typeof strings.failure == 'string') {
          alert(strings.failure);
        } else {
          alert('Error');
        }
      }
    }
  });
}


function changeItem(theDate, itemNum, type) {
  if ((typeof theDate == 'undefined') || (typeof itemNum == 'undefined') || 
      (typeof type == 'undefined') || !$(type + '_' + theDate + '_' + itemNum) ||
      $(type + '_' + theDate + '_' + itemNum).value.empty()) {

  	return false;
  }

  // send off change
  new Ajax.Request(BASE_URL + '/get/change-feature.php', {
    method: 'post',
    parameters: {
      date:   theDate,
      number: itemNum,
      values: type + '=' + $(type + '_' + theDate + '_' + itemNum).value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        $(type + '_' + theDate + '_' + itemNum).addClassName('success');

        // if status changed back to 'idea', refresh page to update UI 
        if ((type == 'status') && ($(type + '_' + theDate + '_' + itemNum).value == 'idea')) {
          location.reload(true);
        }

      } else {
        // failure
        if (typeof strings.failure == 'string') {
          alert(strings.failure);
        } else {
          alert('Error');
        }
      }
    }
  });
}


function saveChanges(theDate, itemNum) {
  if ((typeof theDate == 'undefined') || (typeof itemNum == 'undefined') ||
      !$('intro_' + theDate + '_' + itemNum) || !$('body_' + theDate + '_' + itemNum)) {
    return false;
  }

  // set values
  var theValues     = {};

  theValues.type    = 'message';
  theValues.number  = itemNum;
  theValues.intro   = $('intro_' + theDate + '_' + itemNum).value;
  theValues.body    = $('body_' + theDate + '_' + itemNum).value;

  // send off changes
  new Ajax.Request(BASE_URL + '/get/section.php', {
    method: 'post',
    parameters: { 
      date:    theDate,
      context: 'introduction',
      values:  Object.toQueryString(theValues),
      insert:  false
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        showIndicator('feature-' + itemNum, 'indicator-success');

      } else {
        // failure
        if (typeof strings.failure == 'string') {
          alert(strings.failure);
        } else {
          alert('Error');
        }
      }

      return true;
    },
    onFailure: function() {
      return false;
    }
  });
}


function previewMedia(theDate, theNumber) {
  if ((typeof theDate != 'string') || (typeof theNumber != 'number')) {
    return false;
  }


  // if preview already in place, remove and switch buttons
  if (false && $('media_' + theDate).down('div').down('div.preview-container-' + theNumber)) {
    // remove preview
    $('media_' + theDate).down('div').down('div.preview-container-' + theNumber).remove();

    // switch buttons
    $('media_' + theDate + '_' + theNumber + '-preview').toggle();
    $('media_' + theDate + '_' + theNumber + '-close-preview').toggle();

    return;
  }
  
  
  // load in lightbox
  lightbox.activateWindow({
    href:    BASE_URL + '/get/preview-media.php?mode=lightbox&date=' + theDate + '&number=' + theNumber, 
    title:   'Preview',
    width:   620,
    height:  500
  });
}