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


// keep scroll at bottom
document.observe('dom:loaded', function() {
  if ($('result')) {
    var checkScrollRepeat = window.setInterval(checkScroll, 500);
  }
});


function parseAuthors(event) {
  Event.stop(event);

  if (!$('show-skipped') || !$('result')) {
    return false;
  }

  // change result iframe URL to start insert process (and show results!)
  $('result').src = BASE_URL + '/tool/parse-authors.php?show_skipped=' + $('show-skipped').checked;
}