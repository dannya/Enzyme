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
if (!isset($_REQUEST['data'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// set valid fields
$validFields = array('email', 'firstname', 'lastname', 'language');


// change data
parse_str($_REQUEST['data'], $data);

foreach ($data as $key => $value) {
  if (in_array($key, $validFields)) {
    $user->data[$key] = $value;
  }
}


// save changes
$json['success'] = $user->save();


// report success
App::returnHeaderJson();

?>