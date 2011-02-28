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


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// ensure user has privileges
if (!$user->hasPermission(array('admin', 'reviewer', 'classifier'))) {
  App::returnHeaderJson(true, array('permission' => false));
}


// check context is valid
if ($_REQUEST['context'] == 'filter') {
  // ensure needed params are set
  if (empty($_REQUEST['id'])) {
    App::returnHeaderJson(true, array('missing' => true));
  }

  // delete filter
  $json['success'] = Db::delete('commit_path_filters', array('id' => $_REQUEST['id']));


} else if ($_REQUEST['context'] == 'link') {
  // ensure needed params are set
  if (empty($_REQUEST['id'])) {
    App::returnHeaderJson(true, array('missing' => true));
  }

  // delete link
  $json['success'] = Db::delete('links', array('name' => $_REQUEST['id']));


} else {
  // unknown context
  App::returnHeaderJson(true, array('error' => true));
}


// report success
App::returnHeaderJson();

?>