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
if (!isset($_REQUEST['start'])) {
  echo _('Please enter the start date!');
  exit;
}
if (!isset($_REQUEST['end'])) {
  echo _('Please enter the end date!');
  exit;
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  echo _('Must be logged in!');
  exit;
}


ob_start();


// draw html page start
echo Ui::drawHtmlPageStart(null, array('/css/includes/common.css'), array('/js/prototype.js'));


// do insert
Enzyme::insertRevisions($_REQUEST['start'], $_REQUEST['end']);


// draw html page end
echo Ui::drawHtmlPageEnd();


?>