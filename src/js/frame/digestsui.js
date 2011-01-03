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


var bulkRevisions = [];

function bulkSelect(theRevision, event) {
  if (typeof theRevision == 'undefined') {
    return false;
  }

  if (bulkRevisions.indexOf(theRevision) == -1) {
    // add to bulk revisions
    bulkRevisions.push(theRevision);

  } else {
    // remove from bulk revisions
    bulkRevisions = bulkRevisions.without(theRevision);
  }
}


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
    changeItemId('', '');

    // add new row to page
    $('sections').insert({ bottom: '<div id="intro-section-new" class="section" style="display:none;">' + newRow + '</div>' });
  }
}


function changeItemId(oldId, newId) {
	if ((typeof oldId == 'undefined') || (typeof newId == 'undefined')) {
    return false;
  }

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


function insertFromFeatures(theDate) {
  if (typeof theDate == 'undefined') {
    return false;
  }

  // load in lightbox
  myLightWindow.activateWindow({
    href:    BASE_URL + '/get/feature-articles.php?date=' + theDate, 
    title:   strings.feature_articles,
    width:   500,
    height:  500
  });
  
  return false;
}


function expandFeature(theDate, itemNum) {
  if ((typeof theDate == 'undefined') || (typeof itemNum == 'undefined') ||
      !$('feature_' + theDate + '_' + itemNum)) {

    return false;
  }

  $('feature_' + theDate + '_' + itemNum).toggleClassName('featureExpand');
}


function insertFeature(theDate, itemNum, targetDate) {
  if ((typeof theDate == 'undefined') || (typeof itemNum == 'undefined') || (typeof targetDate == 'undefined') ||
      !$('feature_' + theDate + '_' + itemNum)) {

    return false;
  }
  
  
  // set params
  var params = {
    date:   theDate,
    number: itemNum,
    values: 'status=selected'
  };

  if (theDate != targetDate) {
    params.values += '&date=' + targetDate;
  }


  // set date to this, and state to selected
  new Ajax.Request(BASE_URL + '/get/change-feature.php', {
    method: 'post',
    parameters: params,
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
			  // close lightbox, reload page to show inserted article
			  myLightWindow.deactivate();
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


function changeSectionNumber(event, theDate, itemNum) {
  if ((typeof event == 'undefined') || (typeof theDate == 'undefined') || (typeof itemNum == 'undefined')) {
    return false;
  }

  var newItemNum = prompt(strings.change_section_num, itemNum);
  
  // save?
  if (newItemNum && (newItemNum != itemNum)) {  
	  new Ajax.Request(BASE_URL + '/get/change-feature.php', {
	    method: 'post',
	    parameters: {
	      date:    theDate,
	      number:  itemNum,
	      values:  'number=' + newItemNum
      },
	    onSuccess: function(transport) {
	      var result = transport.headerJSON;

	      if ((typeof result.success != 'undefined') && result.success) {
          // change all onclick actions
          if ($('section-counter-' + itemNum)) {
            $('section-counter-' + itemNum).writeAttribute('onclick', "changeSectionNumber(event, '" + theDate + "', " + newItemNum + ");"); 
          }
          if ($('save-introduction-' + itemNum)) {
            $('save-introduction-' + itemNum).writeAttribute('onclick', "saveSection('" + theDate + "', 'introduction', " + newItemNum + ");"); 
          }

			    // rename all section elements
			    if ($('intro-section-' + itemNum)) {
			    	$('intro-section-' + itemNum).id = 'intro-section-' + newItemNum; 
          }

			    // write new number into box
			    event.element().update(newItemNum);

	      } else {
	        // failure (chosen number probably already in use)
		      new Effect.Highlight(event.element(), {
		        startcolor: '#d40000',
		        duration:   2
		      });
	      }
	    }
	  });
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
    theValues.status = 'selected';
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


  // change multiple commits?
  if (((theContext == 'area') || (theContext == 'type')) && 
      (bulkRevisions.indexOf(theRevision) != -1)) {

    var changeRevisions = bulkRevisions.toJSON();

  } else {
    // convert to JSON
    var changeRevisions = '[' + theRevision + ']';
  }


  // send off change
  new Ajax.Request(BASE_URL + '/get/change-value.php', {
    method: 'post',
    parameters: {
      'context':  theContext,
      'revision': changeRevisions,
      'value':    $(theContext + '-' + theRevision).value
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // iterate
        changeRevisions.evalJSON(true).each(function(item) {
        	// add success styling to element
          $(theContext + '-' + item).addClassName('success');

          if ((theContext == 'area') || (theContext == 'type')) {
	          // set select box to matching item
	          $(theContext + '-' + item).selectedIndex = $(theContext + '-' + theRevision).selectedIndex;

	          // uncheck bulk action box if checked
	          if ($('bulk-' + item) && $('bulk-' + item).checked) {
	            $('bulk-' + item).checked = false;
	          }

		        // remove from bulk revisions list
		        bulkRevisions = bulkRevisions.without(item);
          }
        });
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

  // remove multiple commits?
  if (bulkRevisions.indexOf(theRevision) != -1) {
  	var removeRevisions = bulkRevisions.toJSON();
  } else {
  	// convert to JSON
  	var removeRevisions = '[' + theRevision + ']';
  }

  new Ajax.Request(BASE_URL + '/get/change-value.php', {
    method: 'post',
    parameters: {
      'context':  'remove',
      'revision': removeRevisions,
      'value':    true
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      // remove unneeded subheaders
      if ((typeof result.success != 'undefined') && result.success) {
        // iterate
      	removeRevisions.evalJSON(true).each(function(item) {
	        if ($('commit-' + item)) {
	          var subheader = $('commit-' + item).previous('h3');
	
	          // remove commit from page
	          $('commit-' + item).remove();
	
	          // check if subheader has more items, if not, also remove
	          if (!subheader.next() || (subheader.next().tagName != 'DIV')) {
	            subheader.remove();
	          }
	        }
      	});


      }
      
      // fix total display
      if ($('num-commits')) {
      	$('num-commits').update($('num-commits').innerHTML.replace(/[0-9]/g, $('content').select('div.commit').size()));
      }
    }
  }); 
}