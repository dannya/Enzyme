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


function addMedia() {
}


function changeMediaDate(theDate, theNumber) {
  if ((typeof theDate != 'string') || (typeof theNumber != 'number')) {
    return false;
  }

  // get new date
  var newDate = prompt('Boo', theDate);

  // change date?
  if ((newDate != theDate) && (newDate.length == 10)) {
	  new Ajax.Request(BASE_URL + '/get/change-media.php', {
	    method: 'post',
	    parameters: {
	      date:      theDate,
	      number:    theNumber,
	      data:      newDate,
	      dataType:  'date'
	    },
	    onSuccess: function(transport) {
	      var result = transport.headerJSON;
	
	      if ((typeof result.success != 'undefined') && result.success) {
	        // reload page
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
}


function previewMedia(theDate, theNumber) {
	if ((typeof theDate != 'string') || (typeof theNumber != 'number')) {
		return false;
	}


  // if preview already in place, remove and switch buttons
	if ($('media_' + theDate).down('div').down('div.preview-container-' + theNumber)) {
		// remove preview
		$('media_' + theDate).down('div').down('div.preview-container-' + theNumber).remove();

    // switch buttons
		$('media_' + theDate + '_' + theNumber + '-preview').toggle();
		$('media_' + theDate + '_' + theNumber + '-close-preview').toggle();

		return;
	}


  // get preview HTML
  new Ajax.Request(BASE_URL + '/get/preview-media.php', {
    method: 'post',
    parameters: {
      date:   theDate,
      number: theNumber
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
      	// insert preview HTML into page
      	if ($('media_' + theDate)) {
      		$('media_' + theDate).down('div').insert({ bottom: '<div class="preview-container preview-container-' + theNumber + '">' + transport.responseText + '</div>' });

			    // switch buttons
			    $('media_' + theDate + '_' + theNumber + '-preview').toggle();
			    $('media_' + theDate + '_' + theNumber + '-close-preview').toggle();

      		// scroll to selected row
      		scrollToOffset('media_' + theDate, -36);
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


function saveChange(theDate, theNumber, event) {
  if ((typeof theDate != 'string') || (typeof theNumber != 'number') || (typeof event == 'undefined')) {
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
  new Ajax.Request(BASE_URL + '/get/change-media.php', {
    method: 'post',
    parameters: {
      date:     theDate,
      number:   theNumber,
      data:     theData,
      dataType: theDataType
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // show success
        element.addClassName('success');

      } else {
      	// show failure
      	element.addClassName('failure');
      }
    }
  });
}