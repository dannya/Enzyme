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


include($_SERVER['DOCUMENT_ROOT'] . '/autoload.php');


// ensure needed params are set
if (!isset($_REQUEST['context']) || !isset($_REQUEST['revision']) || !isset($_REQUEST['value'])) {
  App::returnHeaderJson(true, array('missing' => true));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('login' => false));
}


// check for sanity
if (($_REQUEST['context'] != 'msg') &&
    ($_REQUEST['context'] != 'type') &&
    ($_REQUEST['context'] != 'area') &&
    ($_REQUEST['context'] != 'remove')) {

  App::returnHeaderJson(true, array('error' => true));
}


// ensure user has privileges
if ((($_REQUEST['context'] == 'msg') || ($_REQUEST['context'] == 'remove')) &&
    (!$user->hasPermission('admin') && !$user->hasPermission('editor'))) {

  App::returnHeaderJson(true, array('permission' => false));
}


// save change
if ($_REQUEST['context'] == 'msg') {
  $filter = array('revision' => $_REQUEST['revision']);
  $values = array($_REQUEST['context'] => $_REQUEST['value']);

  $json['success'] = Db::save('commits', $filter, $values);

} else if ($_REQUEST['context'] == 'remove') {
  $filter = array('revision' => $_REQUEST['revision']);
  $values = array('marked'      => 0,
                  'type'        => null,
                  'area'        => null,
                  'classifier'  => null,
                  'classified'  => null);

  $json['success'] = Db::save('commits_reviewed', $filter, $values);

} else {
  $filter = array('revision' => $_REQUEST['revision']);
  $values = array($_REQUEST['context'] => intval($_REQUEST['value']));

  $json['success'] = Db::save('commits_reviewed', $filter, $values);
}


// report success
App::returnHeaderJson();

?>