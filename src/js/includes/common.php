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


// globally-available script variables
var BASE_URL        = '<?php echo BASE_URL; ?>';
var scrollFinished  = false;


// define translatable strings
var strings  = {};

strings.failure                 = '<?php echo _("Error"); ?>';
strings.close                   = '<?php echo _("Close"); ?>';
strings.loading                 = '<?php echo _("Loading"); ?>';
strings.cancel                  = '<?php echo _("Cancel"); ?>';

strings.change_password_success = '<?php echo _("Your password has been changed"); ?>';
strings.change_password_failure = '<?php echo _("Error: Password not changed"); ?>';

strings.change_personal_success = '<?php echo _("Your information has been changed"); ?>';
strings.change_personal_failure = '<?php echo _("Error: Personal information not changed"); ?>';

strings.application_failure     = '<?php echo _("Error: Application failed"); ?>';

strings.settings_failure        = '<?php echo _("Failed to save settings"); ?>';

strings.reset_success           = '<?php echo _("Your password has been reset. Please check your registered email account for further instructions."); ?>';

strings.people_references       = '<?php echo _("People References"); ?>';
strings.feature_articles        = '<?php echo _("Available Feature Articles"); ?>';
strings.dot_blurb               = '<?php echo _("Dot Synopsis"); ?>';

strings.change_section_num      = '<?php echo _("What number should this section be?"); ?>';
strings.delete_section          = '<?php echo _("Are you sure you want to permanently delete this?"); ?>';

strings.num_commits_singular    = '<?php echo _("There is %d selection this week"); ?>';
strings.num_commits_plural      = '<?php echo _("There are %d selections this week"); ?>';

strings.num_filters_plural      = '<?php echo _("%d filters"); ?>';
strings.num_links_plural        = '<?php echo _("%d links"); ?>';

strings.remove_commit           = '<?php echo _("Are you sure you want to remove this commit?"); ?>';
strings.remove_commits          = '<?php echo _("Remove the %d selected commits?"); ?>';

strings.delete_stats            = '<?php echo _("Are you sure you want to delete these statistics?"); ?>';
strings.delete_idea             = '<?php echo _("Are you sure you want to delete this idea?"); ?>';

strings.num_applications        = '<?php echo _("%d applications"); ?>'
strings.decline_application     = '<?php echo _("Are you sure? An email will be sent notifying the applicant of this..."); ?>';

strings.generic_prompt          = '<?php echo _("Are you sure?"); ?>';

strings.button_user             = '<?php echo _("Make this user inactive?"); ?>';
strings.button_repo             = '<?php echo _("Delete repository?"); ?>';

strings.change_date             = '<?php echo _("Change the date?"); ?>';
strings.change_filename         = '<?php echo _("Change the filename?"); ?>';


function sprintf() {
  if (!arguments || (arguments.length < 1) || !RegExp) {
    return;
  }

  var str = arguments[0];
  var re  = /([^%]*)%('.|0|\x20)?(-)?(\d+)?(\.\d+)?(%|b|c|d|u|f|o|s|x|X)(.*)/;
  var a = b = [], numSubstitutions = 0, numMatches = 0;

  while (a = re.exec(str)) {
    var leftpart    = a[1], pPad = a[2], pJustify = a[3], pMinLength = a[4];
    var pPrecision  = a[5], pType = a[6], rightPart = a[7];

    ++numMatches;

    if (pType == '%') {
      subst = '%';
    } else {
      ++numSubstitutions;

      if (numSubstitutions >= arguments.length) {
        // not enough args
        return str;
      }

      var param = arguments[numSubstitutions];
      var pad   = '';

      if (pPad && pPad.substr(0,1) == "'") {
        pad = leftpart.substr(1,1);
      } else if (pPad) {
        pad = pPad;
      }

      var justifyRight = true;
      if (pJustify && pJustify === "-") {
        justifyRight = false;
      }

      var minLength = -1;
      if (pMinLength) {
        minLength = parseInt(pMinLength);
      }

      var precision = -1;
      if (pPrecision && pType == 'f') {
        precision = parseInt(pPrecision.substring(1));
      }

      var subst = param;
      if (pType == 'b') {
        subst = parseInt(param).toString(2);
      } else if (pType == 'c') {
        subst = String.fromCharCode(parseInt(param));
      } else if (pType == 'd') {
        subst = parseInt(param) ? parseInt(param) : 0;
      } else if (pType == 'u') {
        subst = Math.abs(param);
      } else if (pType == 'f') {
        subst = (precision > -1) ? Math.round(parseFloat(param) * Math.pow(10, precision)) / Math.pow(10, precision): parseFloat(param);
      } else if (pType == 'o') {
        subst = parseInt(param).toString(8);
      } else if (pType == 's') {
        subst = param;
      } else if (pType == 'x') {
        subst = ('' + parseInt(param).toString(16)).toLowerCase();
      } else if (pType == 'X') {
        subst = ('' + parseInt(param).toString(16)).toUpperCase();
      }
    }

    str = leftpart + subst + rightPart;
  }

  return str;
}


function quote(element) {
	if (typeof element == 'undefined') {
		return false;
	}

	if (Object.isNumber(element)) {
		return element;
	} else {
		return '"' + element + '"';
	}
}


function selectItem(direction) {
  if ((typeof itemCounter != 'undefined') && (typeof itemClass != 'undefined')) {
    if ((direction == "prev") && $(itemClass + '-' + (itemCounter - 1))) {
      // go to previous item
      currentItem   = $(itemClass + '-' + itemCounter);
      newItem       = $(itemClass + '-' + (itemCounter - 1));

      if (itemCounter > 0) {
        --itemCounter;
      }

    } else if ((direction == "next") && $(itemClass + '-' + (itemCounter + 1))) {
      // go to next item
      if (itemCounter == 0) {
        selection = 1;
      } else {
        selection = itemCounter;
      }

      currentItem   = $(itemClass + '-' + selection);
      newItem       = $(itemClass + '-' + (itemCounter + 1));

      ++itemCounter;
    }
    
    if (typeof newItem != 'undefined') {
      // scroll to new item
      scrollItem(newItem);

      // set style of items?
      if (!currentItem.hasClassName('marked')) {
        currentItem.className = 'item normal read ' + context;
      }

      if (!newItem.hasClassName('marked')) {
        newItem.className = 'item selected ' + context;
      }

      return newItem;
    }
  }
}


function scrollItem(id, offset) {
	if (!$(id)) {
	  return false;
	}

	if (typeof offset == 'undefined') {
	 var offset = -56;
	}

  // make sure current item is visible in browser viewport!
  var pos = $(id).cumulativeOffset();
  $('content').scrollTop = pos[1] + offset;
}


function scrollToOffset(id, offset) {
  if (!$(id) || (typeof offset == 'undefined')) {
    return false;
  }

  // make sure current item is visible in browser viewport!
  var pos = $(id).cumulativeOffset();
  window.scrollTo(pos[0], pos[1] + offset);
}


function markCommit() {
  // get currently-selected commit item
  currentItem   = $(itemClass + '-' + itemCounter);
  revision      = currentItem.down('.revision').readAttribute('id').sub('r::', '', 1);

  if (!currentItem.hasClassName('marked')) {
    // set commit as marked
    state = 'true';
    class = 'item marked ' + context;

    // add to marked commits array
    if (markedCommits.indexOf(revision) == -1) {
      markedCommits.push(revision);
    }

  } else {
    // unset commit as marked
    state = 'false';
    class = 'item selected ' + context;

    // remove from marked commits array
    index = markedCommits.indexOf(revision);
    if (index != -1) {
      markedCommits.splice(index, 1);
    }
  }

  // set style
  currentItem.className = class;
}


function buttonState(id, state) {
  if ($(id) && (typeof state != 'undefined')) {
    if (state == 'enabled') {
      $(id).disabled = false;
    } else if (state == 'disabled') {
      $(id).disabled = true;
    }
  }
}


function save(theType, theButton) {
  if (typeof theType == 'undefined') {
    return null;
  }

  // show spinner, disable button?
  if ($('cancel')) {
    $('cancel').hide();
  }
  if ($('status-area-spinner')) {
    $('status-area-spinner').show();
  }
  if (typeof theButton == 'object') {
    theButton.disabled = true;
  }


  // collect data
  if (theType == 'review') {
    var parameters = {
      type:   theType,
      read:   readCommits.toJSON(),
      marked: markedCommits.toJSON()
    };

  } else if (theType == 'classify') {
    var theData = [];

    $$('div.item').each(function(item) {
      if ((!$(item.id + '-type').value.empty() && ($(item.id + '-type').value != 0)) || 
          (!$(item.id + '-area').value.empty() && ($(item.id + '-area').value != 0))) {

        theData.push({ 'r':$(item).down('.revision').readAttribute('id').sub('r::', '', 1),
                       't':$(item.id + '-type').value,
                       'a':$(item.id + '-area').value });
      }
    });

    var parameters = {
      type: theType,
      data: Object.toJSON(theData)
    };
  }


  // send off data
  new Ajax.Request(BASE_URL + '/get/save.php', {
    method: 'post',
    parameters: parameters,

    onSuccess: function(transport) {
      var result = transport.headerJSON;
      
		  // hide spinner, enable button?
		  if ($('status-area-spinner')) {
		    $('status-area-spinner').hide();
		  }
		  if ($('cancel')) {
		    $('cancel').show();
		  }
		  if (typeof theButton == 'object') {
		    theButton.disabled = false;
		  }

      if ((typeof result.success != 'undefined') && result.success) {
        // show success message
        if (result.saved == 1) {
          var successString = '<?php echo _('Saved %d commit'); ?>';
        } else {
        	var successString = '<?php echo _('Saved %d commits'); ?>';
        } 

        information('success', sprintf(successString, result.saved));

        // disable save button
        if (theType == 'review') {
          buttonState('review-save', 'disabled');
        }

        // refresh page to show updated data
        loadPage(theType, true);
        
        // reset counters
        readCommits   = [];
        markedCommits = [];
        itemCounter   = 0;
        commitCounter = 0;
        
        // reset displays
			  if ($('commit-selected')) {
			    $('commit-selected').update(markedCommits.size());
			  }
			  if ($('commit-displayed')) {
			    $('commit-displayed').update(Math.min($$('div.item').size(), result.total));
			  }
        if ($('commit-total')) {
          $('commit-total').update(result.total);
        }
			  $('commit-counter').update(commitCounter);

      } else {
        // show error message
        information('failure', '<?php echo _('Save unsuccessful'); ?>');
      }
    },

    onFailure: function() {
      // show error message
      information('failure', '<?php echo _('Save unsuccessful'); ?>');
    }
  });
}


function loadPage(thePage, scroll) {
  if (typeof thePage == 'undefined' || !$('content')) {
    return false;
  }

  new Ajax.Request(BASE_URL + '/get/page.php', {
    method: 'post',
    parameters: {
      page:thePage
    },
    onSuccess: function(transport) {
      $('content').update(transport.responseText);

      // scroll to top of page?
      if ((typeof scroll != 'undefined') && scroll) {
        $('content').scrollTop = 0;
      }
    }
  });
}


function information(theClass, message) {
  if ((typeof theClass == 'undefined') || !$('status-area') || !$('status-area-info')) {
    return false;
  }

  // set styling
  $('status-area-info').className = theClass;

  // change message text
  $('status-area-info').update(message);

  // show message
  new Effect.Appear('status-area-info', {
    duration:0.3
  });

  // hide after 5 seconds
  $('status-area-info').fade({ duration:0.3, delay:5 });
}


function checkScroll() {
  if ($('result') && $('result').contentWindow.document.body &&
      (typeof $('result').contentWindow.document.body.descendants == 'function') &&
      ($('result').contentWindow.document.body.descendants().size() > 0) &&
      !scrollFinished) {

    var last = $('result').contentWindow.document.body.descendants().last();
    
    // check whether we can finish observing scroll
    if (last.id != 'finished') {
      last.scrollTo();

    } else {
    	// found "finished" notification, but element is hidden, 
    	// so use previous element as scroll anchor
    	last.previous().scrollTo();
      scrollFinished = true;	
    }
  }
}


function inputPrompt(event) {
  var element = event.element();
  var tagname = element.tagName;

  if (event.type == 'focus') {
    // save initial value?
    if (element.hasClassName('prompt')) {
      if (!element.readAttribute('alt')) {
        if (tagname == 'TEXTAREA') {
          var text = element.innerHTML;
        } else {
          var text = element.value;
        }

        element.writeAttribute('alt', text);
      }

      element.value = '';
      element.removeClassName('prompt');
    }

  } else if (event.type == 'blur') {
    // switch back?
    if (element.value.empty() == true) {
      element.value = element.readAttribute('alt');
      element.addClassName('prompt');
    }
  }
}


function changeInterface(context) {
  if ((typeof context != 'string') ||
      ((context != 'mouse') && (context != 'keyboard'))) {

  	return false;
  }
  
  // warn of data loss
  if (!confirm('<?php echo _('Any unsaved changes will be lost. Continue?'); ?>')) {
    return false;
  }

  // send off data
  new Ajax.Request(BASE_URL + '/get/change-personal.php', {
    method: 'post',
    parameters: { 
      data: 'interface=' + context
    },
    onSuccess: function(transport) {
      var result = transport.headerJSON;

      if ((typeof result.success != 'undefined') && result.success) {
        // refresh page to show 
        location.reload(true);
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


function validateEmail(email) {
	if (typeof email != 'string') {
		return false;
	}

	var matches = email.match(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/);
	
	if (matches == null) {
		return false;
	} else {
		return true;
	}
}
