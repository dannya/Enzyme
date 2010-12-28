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


function addIntroSection() {
  if ($('sections') && $('intro-section-new')) {
	  // hide prompt
	  if ($('sections-prompt')) {
	    $('sections-prompt').hide();
	  }

    // clone new row, so we can keep adding new rows after this one
    var newRow = $('intro-section-new').innerHTML;

    // count number of sections
    var newCounter = $('sections').select('div.section').size();

    // change id's and actions of new (visible) row
    $('save-introduction-new').writeAttribute('onclick', $('save-introduction-new').readAttribute('onclick').sub('new', newCounter));
    $('save-introduction-new').id  = $('save-introduction-new').id.sub('-new', '-' + newCounter);
    
    $('button-message-new').writeAttribute('onclick', $('button-message-new').readAttribute('onclick').sub('new', newCounter));
    $('button-message-new').id     = $('button-message-new').id.sub('-new', '-' + newCounter);

    $('button-comment-new').writeAttribute('onclick', $('button-comment-new').readAttribute('onclick').sub('new', newCounter));
    $('button-comment-new').id     = $('button-comment-new').id.sub('-new', '-' + newCounter);

    $('intro-new').id              = $('intro-new').id.sub('-new', '-' + newCounter);
    $('body-new').id               = $('body-new').id.sub('-new', '-' + newCounter);

    $('section-counter-new').update(newCounter);
    $('section-counter-new').id    = $('section-counter-new').id.sub('-new', '-' + newCounter);
    
    // make new row visible
    $('intro-section-new').id      = $('intro-section-new').id.sub('-new', '-' + newCounter);
    $('intro-section-' + newCounter).show();

    // add new row to page
    $('sections').insert({ bottom: '<div id="intro-section-new" class="section" style="display:none;">' + newRow + '</div>' });
  }
}


function addDigestLinks(theDate, contentBox) {
	if ((typeof theDate == 'undefined') || 
	    (typeof contentBox == 'undefined') || !$(contentBox)) {

		return false;
	}
	

  // send off content
  new Ajax.Request(BASE_URL + '/get/add-links.php', {
    method: 'post',
    parameters: {
    	date: theDate,
      data: $(contentBox).value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        showIndicator('new-digest', 'indicator-success');
        
        // put linked content back into content box
        $(contentBox).value = result.data;

      } else {
        showIndicator('new-digest', 'indicator-failure');
      }
    }
  });
}


function insertFeature(theDate) {
	alert('boo');
}


function peopleReferences(theDate) {
	if (typeof theDate == 'undefined') {
	  return false;
	}

	// load in lightbox
	myLightWindow.activateWindow({
	  href:    BASE_URL + '/get/people-references.php?date=' + theDate, 
	  title:   strings.people_references,
	  width:   500,
	  height:  500
	});
	
	return false;
}


function dotBlurb(theDate) {
  if (typeof theDate == 'undefined') {
    return false;
  }

  // load in lightbox
  myLightWindow.activateWindow({
    href:    BASE_URL + '/get/dot-blurb.php?date=' + theDate, 
    title:   strings.dot_blurb,
    width:   500,
    height:  500
  });

  return false;
}


function addPerson() {
	alert('Not yet implemented!');
}


function createNewDigest() {
  if ($('new-digest')) {
    // collect data
    var theData = {};
    var theDate = null;
    var skip    = false;

    $('new-digest').select('input, select').each(function(item) {
      if (item.value.empty()) {
        item.addClassName('failure');

        skip = true;
        throw $break;
      }

      if (item.id == 'info-date') {
        theDate          = item.value;
      } else {
        theData[item.id] = item.value;
      }
    });


    // create new issue
    if (!skip) {
      new Ajax.Request(BASE_URL + '/get/section.php', {
        method: 'post',
        parameters: { 
          date:    theDate,
          context: 'new-digest',
          values:  Object.toQueryString(theData)
        },
        onSuccess: function(transport) {
          var result = transport.headerJSON;
    
          if ((typeof result.success != 'undefined') && result.success) {
            showIndicator('new-digest', 'indicator-success');

            // wait for user to acknowledge change, then redirect
            window.setTimeout(function() {
            	                  top.location = BASE_URL + '/digests/' + theDate + '/'; 
                              }, 1000);

          } else {
            showIndicator('new-digest', 'indicator-failure');
          }
        }
      });
    }
  }
}


function insertSection(event, theDate, theContext, number) {
  var success = saveSection(theDate, theContext, number, true);

  // change onclick action
  if (success) {
  	var button = event.element().up('div.save-introduction');

  	button.writeAttribute('onclick', "saveSection('" + theDate + "', '" + theContext + "', " + number + ");");
  }
}


function saveSection(theDate, theContext, number, theInsert) {
  if ((typeof theDate == 'undefined') || (typeof theContext == 'undefined')) {
    return false;
  }
  
  // insert, or save?
  if ((typeof theInsert != 'undefined') && theInsert) {
  	var theInsert = true;
  } else {
  	var theInsert = false;
  }


  // set data
  var theValues = {};

  if (theContext == 'info') {
    theValues.id         = $('info-id').value;
    theValues.date       = $('info-date').value;
    theValues.type       = $('info-type').value;
    theValues.language   = $('info-language').value;
    theValues.editor     = $('info-editor').value;
    theValues.published  = $('info-published').value;

  } else if (theContext == 'synopsis') {
    theValues.synopsis = $('synopsis').value;

  } else if (theContext == 'introduction') {
    if (typeof number == 'undefined') {
      return false;
    }

    theValues.number = number;
    theValues.intro  = $('intro-' + number).value;

    if ($('button-comment-' + number).hasClassName('selected')) {
      theValues.type = 'comment';
    } else {
      theValues.type = 'message';
    }

    if ($('body-' + number)) {
      theValues.body = $('body-' + number).value;
    }
  }


  // send off changes
  new Ajax.Request(BASE_URL + '/get/section.php', {
    method: 'post',
    parameters: { 
      date:    theDate,
      context: theContext,
      values:  Object.toQueryString(theValues),
      insert:  theInsert
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        showIndicator(theContext, 'indicator-success');
      } else {
        showIndicator(theContext, 'indicator-failure');
      }

      return true;
    },
    onFailure: function() {
    	return false;
    },
  });

  return true;
}


function setPublished(element, date, state) {
  if ((typeof element == 'undefined') || (typeof date == 'undefined') || (typeof state == 'undefined')) {
    return false;
  }

  // send change
  new Ajax.Request(BASE_URL + '/get/publish.php', {
    method: 'post',
    parameters: {
      'date':  date,
      'state': state
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if (((typeof result.success != 'undefined') && result.success) &&
          (typeof result.newState != 'undefined')) {

        // change class, title, and onclick of button
        if (state) {
          var class = 'indicator-success'; 
        } else {
          var class = 'indicator-failure';
        }

        element.writeAttribute('title', '');
        element.writeAttribute('class', class);
        element.writeAttribute('onclick', "setPublished(this, '" + date + "', " + !state +");");
      }
    }
  });
}


function changeItemType(theDate, theNumber, theType) {
  if ((typeof theDate == 'undefined') || 
      !$('button-message-' + theNumber) || !$('button-comment-' + theNumber)) {

    return false;
  }


  // collect data
  var theData = {};
  theData['number'] = theNumber;
  theData['type']   = theType;
  
  // blank message field when changing to comment
  if (theType == 'comment') {
    theData['body'] = '';	
  }

  // send off change
  new Ajax.Request(BASE_URL + '/get/section.php', {
    method: 'post',
    parameters: {
      date:    theDate,
      context: 'introduction',
      values:  Object.toQueryString(theData)
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
			  // hide / show textarea, change styling
			  if (theType == 'message') {
			    $('body-' + theNumber).show();
			    $('intro-' + theNumber).writeAttribute('class', 'intro-message');

			    $('button-message-' + theNumber).writeAttribute('class', 'selected');
			    $('button-comment-' + theNumber).writeAttribute('class', 'unselected');

			  } else if (theType == 'comment') {
			    $('body-' + theNumber).hide();
			    $('intro-' + theNumber).writeAttribute('class', 'intro-comment');

			    $('button-message-' + theNumber).writeAttribute('class', 'unselected');
			    $('button-comment-' + theNumber).writeAttribute('class', 'selected');
			  }
      }
    }
  });
}


function showIndicator(context, class) {
  if ((typeof context == 'undefined') || (typeof class == 'undefined')) {
    return false;
  }

  if ($('indicator-' + context)) {
    // change class
    $('indicator-' + context).writeAttribute('class', class);

    // show for x seconds, then hide
    new Effect.Appear('indicator-' + context, {
      duration:    0.2,
      afterFinish: function() {
        new Effect.Fade('indicator-' + context, {
          duration: 0.2,
          delay:    4
        });
      }
    });
  }
}


function generateStats(start, end) {
  if ((typeof start == 'undefined') || (typeof end == 'undefined')) {
    return false;
  }

  if ($('results')) {
    $('results').src = BASE_URL + '/get/statistics.php?start=' + start + '&end=' + end;
  }
  
  // change buttons
  if ($('generate-stats') && $('delete-stats')) {
    $('generate-stats').hide();
    $('delete-stats').show();
  }
}


function deleteStats(theDate) {
  if ((typeof theDate == 'undefined')) {
    return false;
  }

  // alert
  alert(theDate);
  return
  
  // change buttons
  if ($('generate-stats') && $('delete-stats')) {
  	$('delete-stats').hide();
    $('generate-stats').show();
  }
}


function changeValue(theContext, theRevision) {
  if ((typeof theContext == 'undefined') || (typeof theRevision == 'undefined') ||
      !$(theContext + '-' + theRevision)) {

    return false;
  }

  // reset success visuals
  $(theContext + '-' + theRevision).removeClassName('success');

  // send off change
  new Ajax.Request(BASE_URL + '/get/change-value.php', {
    method: 'post',
    parameters: {
      'context':  theContext,
      'revision': theRevision,
      'value':    $(theContext + '-' + theRevision).value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        $(theContext + '-' + theRevision).addClassName('success');
      }
    }
  });
}


function removeCommit(theRevision) {
  if (typeof theRevision == 'undefined') {
    return false;
  }
  
  // ask first!
  if (!confirm(strings.remove_commit)) {
  	return false;
  }

  new Ajax.Request(BASE_URL + '/get/change-value.php', {
    method: 'post',
    parameters: {
      'context':  'remove',
      'revision': theRevision,
      'value':    true
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      // remove unneeded subheaders
      if ((typeof result.success != 'undefined') && result.success) {
        if ($('commit-' + theRevision)) {
        	var subheader = $('commit-' + theRevision).previous('h3');

        	// remove commit from page
        	$('commit-' + theRevision).remove();

        	// check if subheader has more items, if not, also remove
        	if (!subheader.next() || (subheader.next().tagName != 'DIV')) {
        		subheader.remove();
        	}
        }
      }
      
      // fix total display
      if ($('num-commits')) {
      	$('num-commits').update($('num-commits').innerHTML.replace(/[0-9]/g, $('content').select('div.commit').size()));
      }
    }
  }); 
}