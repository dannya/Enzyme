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


// setup 'marked commits' and 'read commits' array
var markedCommits   = [];
var readCommits     = [];

// setup selectable items
var itemCounter     = 0;
var itemClass       = 'commit-item';

var commitCounter   = 0;


// define keyboard shortcuts
Hotkey.add(['LEFT'], function(event) {
  // left key
  selectItem('prev');

  // decrement viewed counter
  if (commitCounter > 1) {
    --commitCounter;
  }

  Event.stop(event);
}, 1);

Hotkey.add(['RIGHT'], function(event) {
  // right key
  newItem = selectItem('next');

  // increment viewed counter
  ++commitCounter;

  // update display?
  if ($('commit-counter') &&
      (commitCounter > $('commit-counter').innerHTML) &&
      (commitCounter <= $('commit-total').innerHTML)) {

    $('commit-counter').innerHTML = commitCounter;

    // add commit to read array
    revision = newItem.down('span.revision').innerHTML;

    if (readCommits.indexOf(revision) == -1) {
      readCommits.push(revision);
    }
  }

  buttonState('review-save', 'enabled');

  Event.stop(event);
}, 2);

Hotkey.add([' '], function(event) {
  // SPACE key
  markCommit();

  // update display
  if ($('commit-selected')) {
    $('commit-selected').update(markedCommits.size());
  }

  buttonState('review-save', 'enabled');

  Event.stop(event);
}, 3);



// onload...
document.observe('dom:loaded', function() {
  // write counter total
  if ($('commit-total')) {
    $('commit-total').update($$('div.item').size());
  }
});