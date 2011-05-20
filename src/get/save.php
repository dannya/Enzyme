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
if (!isset($_REQUEST['type'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// save data...
if ($_REQUEST['type'] == 'review') {
  // check needed params are set
  if (!isset($_REQUEST['read']) || !isset($_REQUEST['marked'])) {
    App::returnHeaderJson(true, array('missing' => true));
  }

  // save reviewed commits data
  $read     = json_decode($_POST['read']);
  $marked   = json_decode($_POST['marked']);

  foreach ($read as $revision) {
    $values[] = array('revision' => $revision,
                      'marked'   => in_array($revision, $marked),
                      'reviewer' => $user->data['username']);
  }


  // insert data
  if (isset($values)) {
    $json['saved']   = count($values);
    $json['success'] = Db::insert('commits_reviewed', $values, true);

  } else {
    $json['saved']   = 0;
    $json['success'] = false;
  }

  // get new total
  $json['total'] = Enzyme::getProcessedRevisions('unreviewed', true, null, null, true);

  // report success
  App::returnHeaderJson();


} else if ($_REQUEST['type'] == 'classify') {
  // check needed params are set
  if (!isset($_REQUEST['data'])) {
    App::returnHeaderJson(true, array('missing' => true));
  }


  // convert to array
  $data = json_decode($_REQUEST['data']);

  if (count($data) > 0) {
    // compile values for insersion
    foreach ($data as $item) {
      // set values
      $values[] = array('revision'    => $item->r,
                        'type'        => $item->t,
                        'area'        => $item->a,
                        'classifier'  => $user->data['username'],
                        'classified'  => 'NOW()');
    }


    // save data
    if (isset($values)) {
      $json['saved']   = count($values);
      $json['success'] = Db::saveMulti('commits_reviewed', $values);
    } else {
      $json['saved']   = 0;
      $json['success'] = false;
    }

  } else {
    $json['saved']   = 0;
    $json['success'] = true;
    $json['nodata']  = true;
  }

  // get new total
  $json['total'] = Enzyme::getProcessedRevisions('marked', null, null, null, true);

  // report success
  App::returnHeaderJson();
}

?>