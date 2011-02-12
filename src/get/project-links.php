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
if (!isset($_REQUEST['types']) || !isset($_REQUEST['names']) ||
    !isset($_REQUEST['links']) || !isset($_REQUEST['areas'])) {

  App::returnHeaderJson(true, array('missing' => true));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// ensure user has privileges
if (!$user->hasPermission(array('admin', 'reviewer', 'classifier'))) {
  App::returnHeaderJson(true, array('permission' => false));
}


// save changes
$numItems = count($_REQUEST['types']);

if ($numItems > 0) {
  $json['success'] = false;

  for ($i = 0; $i < $numItems; $i++) {
    $values[] = array('type'    => $_REQUEST['types'][$i],
                      'name'    => $_REQUEST['names'][$i],
                      'url'     => $_REQUEST['links'][$i],
                      'area'    => $_REQUEST['areas'][$i]);
  }

  if (isset($values)) {
    $json['success'] = Db::saveMulti('links', $values);
  }

} else {
  // no items found
  $json['success'] = false;
}


// report success
App::returnHeaderJson();

?>