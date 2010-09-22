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


function setCurrentItem(id) {
  if (!$(id) || !$(id + '-type') || !$(id + '-area')) {
    return null;
  }

  if (!$(id + '-type').value.empty() &&
      !$(id + '-area').value.empty()) {

    // both filled
    $(id).className = 'item marked';
    $(id).down('div.commit-classify').className = 'commit-classify filled';

  } else {
    // none filled
    $(id).className = 'item selected';
    $(id).down('div.commit-classify').className = 'commit-classify unfilled';
  }
  
  updateCounter();
}


function updateCounter() {
  // update counter display
  commitCounter = 0;

  $$('div.item').each(function(item) {
    if ($(item.id + '-type') && !$(item.id + '-type').value.empty() &&
        $(item.id + '-area') && !$(item.id + '-area').value.empty()) {

      // only increment counter when both 'type' and 'area' are filled
      commitCounter++;
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


// onload...
document.observe("dom:loaded", function() {
  // write counter total
  if ($("commit-total")) {
    $("commit-total").update($$("div.item").size());
  }
});