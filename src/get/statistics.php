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


$insert = false;
$delete = false;


// ensure needed params are set
if (empty($_REQUEST['context'])) {
  exit;

} else {
  if ($_REQUEST['context'] == 'insert') {
    $insert = true;

    if (!isset($_REQUEST['start'])) {
      echo _('Please enter the start date!');
      exit;
    }
    if (!isset($_REQUEST['end'])) {
      echo _('Please enter the end date!');
      exit;
    }

  } else if ($_REQUEST['context'] == 'delete') {
    $delete = true;

    if (!isset($_REQUEST['date'])) {
      App::returnHeaderJson(true, array('missing' => true));
    }
  }
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  echo _('Must be logged in!');
  exit;
}


// check permissions
if (!$user->hasPermission('editor')) {
  if ($insert) {
    echo sprintf(_('You need to have the permission "%s" to view this section'), 'editor');

  } else if ($delete) {
    App::returnHeaderJson(true, array('permission' => false));
  }

  exit;
}


ob_start();


if ($insert) {
  // draw html page start
  echo Ui::drawHtmlPageStart(null, array('/css/common.css'), array('/js/prototype.js'));


  // do insert
  Enzyme::generateStatsFromDb($_REQUEST['start'], $_REQUEST['end']);
  //Enzyme::generateStatsFromSvn($_REQUEST['start'], $_REQUEST['end'], 'kde-svn');


  // draw html page end
  echo Ui::drawHtmlPageEnd();

} else if ($delete) {
  $json['success'] = Enzyme::deleteStats($_REQUEST['date']);

  // report success
  App::returnHeaderJson();
}

?>