<?php

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


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');


// ensure needed params are set
if (empty($_REQUEST['date']) || !isset($_REQUEST['state'])) {
  App::returnHeaderJson(true, array('success' => false));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  if (isset($_REQUEST['iframe'])) {
    // warn about failure
    echo '<script type="text/javascript">
            alert("' . _('Cannot find your user session. Login to Enzyme and try again.') . '");
          </script>';
    exit;

  } else {
    App::returnHeaderJson(true, array('success' => false));
  }
}


// determine new state
if ($_REQUEST['state'] == 'true') {
  $newState = true;
  $json['newState'] = 'true';

} else if ($_REQUEST['state'] == 'false') {
  $newState = false;
  $json['newState'] = 'false';
}


// change published state
$json['success'] = Digest::setPublishedState($_REQUEST['date'], $newState);


// clear issues list caches
Cache::delete(array('base' => DIGEST_APP_ID,
                    'id'   => array('issue_latest',
                                    'issue_latest_unpublished',
                                    'issue_earliest',
                                    'archive_latest',
                                    'archive_latest_unpublished',
                                    'archive_earliest')));


// report success
if (isset($_REQUEST['iframe'])) {
  // insert "success" element which can be recognised by observing scripts
  echo '<span id="success">&nbsp;</span>';
  exit;

} else {
  App::returnHeaderJson();
}

?>