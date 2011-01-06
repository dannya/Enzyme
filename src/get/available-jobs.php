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
if (!isset($_REQUEST['job']) || !isset($_REQUEST['active'])) {
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


// change setting
$availableJobs = Enzyme::getAvailableJobsList();

if ($_REQUEST['active'] == 'true') {
  // add to available jobs
  $availableJobs = App::addToCommaList($availableJobs, $_REQUEST['job']);

} else if ($_REQUEST['active'] == 'false') {
  // remove from available jobs
  $availableJobs = App::removeFromCommaList($availableJobs, $_REQUEST['job']);

} else {
  App::returnHeaderJson(true, array('error' => true));
}


// save change
$filter = array('setting' => 'AVAILABLE_JOBS');
$values = array('value' => App::combineCommaList($availableJobs));

$json['success'] = Db::save('settings', $filter, $values);


// clear settings cache
Cache::delete('settings');


// report success
App::returnHeaderJson();

?>