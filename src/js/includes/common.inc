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


var BASE_URL = '<?php echo BASE_URL; ?>';

var strings  = {};
strings.change_password_success = '<?php echo _('Your password has been changed') ?>';
strings.change_password_failure = '<?php echo _('Error: Password not changed') ?>';
strings.change_personal_success = '<?php echo _('Your information has been changed') ?>';
strings.change_personal_failure = '<?php echo _('Error: Personal information not changed') ?>';


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
        currentItem.className = 'item normal';
      }

      if (!newItem.hasClassName('marked')) {
        newItem.className = 'item selected';
      }

      return newItem;
    }
  }
}


function scrollItem(id) {
  // make sure current item is visible in browser viewport!
  var pos = $(id).cumulativeOffset();
  $('content').scrollTop = pos[1] - 56;
}


function markCommit() {
  // get currently-selected commit item
  currentItem   = $(itemClass + '-' + itemCounter);
  revision      = currentItem.down('span.revision').innerHTML;

  if (!currentItem.hasClassName('marked')) {
    // set commit as marked
    state = 'true';
    class = 'item marked';

    // add to marked commits array
    if (markedCommits.indexOf(revision) == -1) {
      markedCommits.push(revision);
    }

  } else {
    // unset commit as marked
    state = 'false';
    class = 'item selected';

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


function save(theType) {
  if (typeof theType == 'undefined') {
    return null;
  }

  if (theType == 'review') {
    var parameters = {
      type: theType,
      read: readCommits.toJSON(),
      marked: markedCommits.toJSON()
    };

  } else if (theType == 'classify') {
    // collect data
    var theData = [];

    $$('div.item').each(function(item) {
      if (!$(item.id + '-type').value.empty() || !$(item.id + '-area').value.empty()) {
        theData.push({ 'r':$(item).down('span.revision').innerHTML,
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

      if ((typeof result.success != 'undefined') && result.success) {
        // show success message
        information('success', sprintf('<?php echo _('Saved %d commits'); ?>', result.saved));

        // disable save button
        buttonState("review-save", "disabled");

        // refresh page to show updated data
        loadPage(theType);

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


function loadPage(thePage) {
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
  if ($('result') &&
      $('result').contentWindow.document.body &&
      (typeof $('result').contentWindow.document.body.descendants == 'function') &&
      $('result').contentWindow.document.body.descendants()) {

    $('result').contentWindow.document.body.descendants().last().scrollTo();
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