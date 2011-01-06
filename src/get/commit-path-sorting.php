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
if (!isset($_REQUEST['id']) || !isset($_REQUEST['paths']) || !isset($_REQUEST['areas'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// ensure user has privileges
if (!$user->hasPermission('admin')) {
  App::returnHeaderJson(true, array('permission' => false));
}


// save changes
$numItems = count($_REQUEST['id']);

if ($numItems > 0) {
  $json['success'] = false;

  for ($i = 0; $i < $numItems; $i++) {
    $values[] = array('id'    => $_REQUEST['id'][$i],
                      'path'  => $_REQUEST['paths'][$i],
                      'area'  => $_REQUEST['areas'][$i]);
  }

  if (isset($values)) {
    $json['success'] = Db::saveMulti('commit_path_filters', $values);
  }

} else {
  // no items found
  $json['success'] = false;
}


// report success
App::returnHeaderJson();

?>