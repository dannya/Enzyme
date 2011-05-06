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


var context = 'classify';


// onload...
document.observe('dom:loaded', function() {
  // write counter total
  if ($('commit-displayed')) {
    $('commit-displayed').update($$('div.item').size());
  }
  
  // focus first box
  if ($('commit-item-1-area')) {
    $('commit-item-1-area').focus();
  }
});


function setCurrentItem(id) {
  if (!$(id) || !$(id + '-type') || !$(id + '-area')) {
    return null;
  }

  if (!$(id + '-type').value.empty() && ($(id + '-type').value != 0) &&
      !$(id + '-area').value.empty() && ($(id + '-area').value != 0)) {

    // both filled
    $(id).removeClassName('selected');
    $(id).addClassName('marked');

    var element = $(id).down('div.commit-classify');
    element.removeClassName('unfilled');
    element.addClassName('filled');

  } else {
    // none filled
    $(id).removeClassName('marked');
    $(id).addClassName('selected');

    var element = $(id).down('div.commit-classify');
    element.removeClassName('filled');
    element.addClassName('unfilled');
  }
  
  // update classifed number in statusbar
  updateCounter();
}


function updateCounter() {
  // update counter display
  commitCounter = 0;

  $$('div.item').each(function(item) {
    if ($(item.id + '-type') && !$(item.id + '-type').value.empty() && ($(item.id + '-type').value != 0) &&
        $(item.id + '-area') && !$(item.id + '-area').value.empty() && ($(item.id + '-area').value != 0)) {

      // only increment counter when both 'type' and 'area' are filled
      ++commitCounter;
    }
  });

  $('commit-counter').update(commitCounter);
}


function changeKey(theType) {
  if (typeof theType == 'undefined' || !$('classify-key-areas') || !$('classify-key-types')) {
    return false;
  }

  // set elements
  if (theType == 'areas') {
    var element1 = $('classify-key-types');
    var element2 = $('classify-key-areas');

  } else if (theType == 'types') {
    var element1 = $('classify-key-areas');
    var element2 = $('classify-key-types');
  }

  // do action
  if ($('classify-key-' + theType).visible()) {
    // hide all
    new Effect.Fade($('classify-key-' + theType), {
      duration:0.3
    });

  } else {
    // show selected
    element1.hide();
    
    new Effect.BlindDown(element2, {
      duration:0.3
    });
  }

  // change button
  $$('input.classify-key-button').each(function(button) {
    button.removeClassName('selected');
  });

  $('classify-key-button-' + theType).addClassName('selected');
}


function setClassifyUserFilter(event) { 
  // convert checkbox into actual setting
  if ((typeof event != 'undefined') && event.element().checked) {
  	var setFilter = 'Y';
  } else {
  	var setFilter = 'N';
  }

  // send off data
  new Ajax.Request(BASE_URL + '/get/change-personal.php', {
    method: 'post',
    parameters: { 
      data: 'classify_user_filter=' + setFilter
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


function callbackRemoveCommit(removeRevisions) {
  // sanity check
  if (typeof removeRevisions == 'undefined') {
    return false;
  }


  // iterate
  removeRevisions.evalJSON(true).each(function(item) {
    if ($('r::' + item).up('div.item')) {
      // remove commit from page
      Element.remove($('r::' + item).up('div.item'));
    }
  });


  // update page counter
  resetDisplays(parseInt($('commit-total').innerHTML) - 1);
}