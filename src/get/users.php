<?php

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


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.inc');


// ensure needed params are set
if (!isset($_REQUEST['username']) || !isset($_REQUEST['data']) || !isset($_REQUEST['dataType'])) {
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
if ($_REQUEST['dataType'] == 'new-user') {
  // process data into format for database saving
  parse_str($_REQUEST['data'], $tmpData);

  // extract valid fields
  $validFields = array('username', 'email', 'firstname', 'lastname', 'permission-admin',
                       'permission-editor', 'permission-reviewer', 'permission-classifier', 'paths');

  foreach ($tmpData as $key => $value) {
    if (in_array($key, $validFields)) {
      if (strpos($key, 'permission-') !== false) {
        // compile permissions
        if ($value == "true") {
          $permissions[] = str_replace('permission-', null, $key);
        }

      } else {
        $data[$key] = $value;
      }
    }
  }

  // condense permissions into single db field
  $data['permissions'] = implode(', ', $permissions);

  // set account as active
  $data['active'] = true;

  // insert new user record
  $json['success'] = Db::insert('users', $data);

} else if (strpos($_REQUEST['dataType'], 'permission-') !== false) {
  // extract permission
  $permission = str_replace('permission-', null, $_REQUEST['dataType']);

  // load target user
  $user->load($_REQUEST['username']);

  // add permission
  $json['success'] = $user->changePermission($permission, $_REQUEST['data']);

} else {
  $allowedTypes = array('active', 'username', 'email', 'firstname', 'lastname', 'paths');

  if (in_array($_REQUEST['dataType'], $allowedTypes)) {
    $filter = array('username' => $_REQUEST['username']);
    $values = array($_REQUEST['dataType'] => $_REQUEST['data']);

    // save data
    $json['success'] = Db::save('users', $filter, $values);

  } else {
    $json['success'] = false;
  }
}


// report success
App::returnHeaderJson();

?>