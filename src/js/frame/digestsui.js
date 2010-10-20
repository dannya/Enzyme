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


function addIntroSection() {
  alert('add intro section');
}


function saveSection(theDate, theContext, number) {
  if ((typeof theDate == 'undefined') || (typeof theContext == 'undefined')) {
    return false;
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
      values:  Object.toQueryString(theValues)
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        showIndicator(theContext, 'indicator-success');
      } else {
        showIndicator(theContext, 'indicator-failure');
      }
    }
  });
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


function changeItemType(number, type) {
  if (!$('button-message-' + number) || !$('button-comment-' + number)) {
    return false;
  }
  
  if (type == 'message') {
    $('button-message-' + number).writeAttribute('class', 'selected');
    $('button-comment-' + number).writeAttribute('class', 'unselected');

  } else if (type == 'comment') {
    $('button-message-' + number).writeAttribute('class', 'unselected');
    $('button-comment-' + number).writeAttribute('class', 'selected');
  }
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