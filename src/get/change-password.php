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
if (!isset($_REQUEST['user']) || !isset($_REQUEST['old_password']) || !isset($_REQUEST['new_password'])) {
  App::returnHeaderJson(true, array('missing' => true));
} else {
  $_REQUEST['user'] = trim($_REQUEST['user']);
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// check request is for user currently logged in!
if ($_REQUEST['user'] != $user->data['username']) {
  App::returnHeaderJson(true, array('error' => true));
}


// if repeat password provided, check that it matches new password!
if (isset($_REQUEST['repeat_password']) &&
    ($_REQUEST['new_password'] != $_REQUEST['repeat_password'])) {

  App::returnHeaderJson(true, array('error' => true));
}


// change password
$json['success'] = $user->changePassword($_REQUEST['old_password'], $_REQUEST['new_password']);


// report success
App::returnHeaderJson();

?>