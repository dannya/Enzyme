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
if (!isset($_REQUEST['date']) || !isset($_REQUEST['number']) ||
    !isset($_REQUEST['dataType']) || !isset($_REQUEST['data'])) {

  App::returnHeaderJson(true, array('missing' => true));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// check for sanity
if (($_REQUEST['dataType'] != 'date') &&
    ($_REQUEST['dataType'] == 'name') &&
    ($_REQUEST['dataType'] == 'number') &&
    ($_REQUEST['dataType'] == 'youtube')) {

  App::returnHeaderJson(true, array('error' => true));
}


// ensure user has privileges
if (!$user->hasPermission(array('admin', 'reviewer', 'classifier'))) {
  App::returnHeaderJson(true, array('permission' => false));
}


// set filter for media record we want to change
$filter = array('date'    => $_REQUEST['date'],
                'number'  => $_REQUEST['number']);


// do specified change
if (($_REQUEST['dataType'] == 'name') ||
    ($_REQUEST['dataType'] == 'number') ||
    ($_REQUEST['dataType'] == 'youtube')) {

  if ($_REQUEST['dataType'] == 'number') {
    // ensure new number is free
    if (!is_numeric($_REQUEST['data']) || ($_REQUEST['data'] <= 0) || ($_REQUEST['data'] > 30) ||
        Db::load('digest_intro_media', array('date' => $_REQUEST['date'], 'number' => $_REQUEST['data'], 1))) {

      App::returnHeaderJson(true, array('error' => true));
    }
  }

  // set new values
  $values = array($_REQUEST['dataType'] => $_REQUEST['data']);

  // save change
  $json['success'] = Db::save('digest_intro_media', $filter, $values);


} else if ($_REQUEST['dataType'] == 'date') {
  // load media record from db
  $media = Db::load('digest_intro_media', $filter, 1);


  // change stored file location
  $oldFileLocation = $media['file'];
  $newFileLocation = str_replace('/' . $_REQUEST['date'] . '/',
                                 '/' . $_REQUEST['data'] . '/',
                                 $oldFileLocation);


  // move media file on filesystem
  if (is_file(DIGEST_BASE_DIR . $oldFileLocation) && is_dir(DIGEST_BASE_DIR . Media::getBasePath($newFileLocation, 2))) {
    $newBaseLocation = DIGEST_BASE_DIR . Media::getBasePath($newFileLocation, 1);

    if (!is_dir($newBaseLocation)) {
      // create media directory
      mkdir($newBaseLocation, 0777);
    }
    if (!is_writable($newBaseLocation)) {
      // make writable
      chmod($newBaseLocation, 0777);
    }

    // move file
    rename(DIGEST_BASE_DIR . $oldFileLocation, DIGEST_BASE_DIR . $newFileLocation);

  } else {
    // error, don't continue!
    App::returnHeaderJson(array('error' => true));
  }


  // change date
  $table  = 'digest_intro_media';
  $values = array('date'    => $_REQUEST['data'],
                  'file'    => $newFileLocation,
                  'number'  => Db::count($table, array('date' => $_REQUEST['data'])) + 1);


  // save change
  $json['success'] = Db::save($table, $filter, $values);
}


// report success
App::returnHeaderJson();

?>