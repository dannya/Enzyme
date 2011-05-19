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


// only allow user to be created if no users exist and settings are unset
$users    = Db::exists('users');
$settings = Db::exists('settings');

if ($users || $settings) {
  App::returnHeaderJson(true, array('exists' => true));
}


// extract valid fields
parse_str($_REQUEST['data'], $tmpData);

$validFields = array('username', 'email', 'firstname', 'lastname');

foreach ($tmpData as $key => $value) {
  if (in_array($key, $validFields)) {
    // ensure all fields are filled
    if (empty($value)) {
      App::returnHeaderJson(true, array('missing' => true));
    }

    $data[$key] = $value;
  }
}


// set as useful permissions (admin, editor, etc)
$data['permissions'] = 'admin, editor, feature-editor, classifier, reviewer';

// set hashed password
$data['password'] = Db::getHash(trim($tmpData['password']));

// set account as active
$data['active'] = true;


// insert new user record
$json['success'] = Db::insert('users', $data);


// login to new user account
$user = new User(false, $data['username'], $tmpData['password']);


// report success
App::returnHeaderJson();

?>