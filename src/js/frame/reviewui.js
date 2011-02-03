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


// setup 'marked commits' and 'read commits' array
var markedCommits   = [];
var readCommits     = [];

// setup selectable items
var itemCounter     = 0;
var itemClass       = 'commit-item';

var commitCounter   = 0;



// define functions
function actionPrev(event) {
  selectItem('prev');

  // decrement viewed counter
  if (commitCounter > 1) {
    --commitCounter;
  }

  Event.stop(event);
}


function actionNext(event) {
  newItem = selectItem('next');

  // increment viewed counter
  ++commitCounter;

  // update display?
  if ($('commit-counter') &&
      (commitCounter > $('commit-counter').innerHTML) &&
      (commitCounter <= $('commit-displayed').innerHTML)) {

    $('commit-counter').update(commitCounter);

    // add commit to read array
    revision = newItem.down('.revision').readAttribute('id').sub('r::', '', 1);

    if (readCommits.indexOf(revision) == -1) {
      readCommits.push(revision);
    }
  }

  buttonState('review-save', 'enabled');

  Event.stop(event);
}


function actionSelect(event) {
  if (event.type == 'click') {
    // set clicked item as currentItem
    itemCounter = parseInt(event.element().up('div.item').readAttribute('id').split('-')[2]);
  }

  markCommit();

  // update display
  if ($('commit-selected')) {
    $('commit-selected').update(markedCommits.size());
  }

  buttonState('review-save', 'enabled');

  if (event.type == 'click') {
    // advance to next commit
    actionNext(event); 
  }

  Event.stop(event);
}



// define keyboard shortcuts
Hotkey.add(['LEFT'], function(event) { actionPrev(event); }, 1);
Hotkey.add(['RIGHT'], function(event) { actionNext(event); }, 2);
Hotkey.add([' '], function(event) { actionSelect(event); }, 3);



// onload...
document.observe('dom:loaded', function() {
  // write counter total
  if ($('commit-displayed')) {
    $('commit-displayed').update($$('div.item').size());
  }
});