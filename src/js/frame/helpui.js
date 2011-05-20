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


function helpHome() {
  // go back to help home page
  if ($('help-content')) {
  	$('help-content').src = BASE_URL + '/get/help.php';
  }

  return false;
}


function helpRefresh() {
  // clear cache, refresh frame
  if ($('help-content')) {
    $('help-content').src = $('help-content').contentWindow.location.href + '&refresh=1';
  }

  return false;
}


function helpBack() {
  // go back one page in the history
  history.back(-1);

  return false;
}