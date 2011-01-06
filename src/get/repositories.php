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
if (!isset($_REQUEST['repository']) || !isset($_REQUEST['data']) || !isset($_REQUEST['dataType'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// check permissions
if (!$user->hasPermission('admin')) {
  App::returnHeaderJson(true, array('permission' => false));
}


// process data
if ($_REQUEST['dataType'] == 'new-repo') {
  // process data into format for database saving
  parse_str($_REQUEST['data'], $tmpData);

  // extract valid fields
  $validFields  = array('priority', 'id', 'type', 'hostname', 'port',
                        'username', 'password', 'accounts-file', 'web-viewer');

  foreach ($tmpData as $key => $value) {
    if (in_array($key, $validFields)) {
      $data[$key] = $value;
    }
  }

  // insert new repo record
  $json['success'] = Db::insert('repositories', $data);


} else if ($_REQUEST['dataType'] == 'delete') {
  // delete repo record
  $json['success'] = Db::delete('repositories', array('id' => $_REQUEST['repository']));


} else {
  $allowedTypes = array('priority', 'id', 'type', 'hostname', 'port',
                        'username', 'password', 'accounts-file', 'web-viewer');

  if (in_array($_REQUEST['dataType'], $allowedTypes)) {
    $filter = array('id' => $_REQUEST['repository']);
    $values = array($_REQUEST['dataType'] => $_REQUEST['data']);

    // save data
    $json['success'] = Db::save('repositories', $filter, $values);

  } else {
    $json['success'] = false;
  }
}


// clear repositories cache
Cache::delete('repositories');


// report success
App::returnHeaderJson();

?>