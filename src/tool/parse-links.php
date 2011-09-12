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
  echo _('Must be logged in!');
  exit;
}


set_time_limit(3500);
ob_start();


// draw html page start
echo Ui::drawHtmlPageStart(null, array('/css/includes/common.css'), array('/js/prototype.js'));


// load data
$filename = 'links.txt';

if (!is_file(Config::getSetting('legacy', 'EXISTING_DATA') . '/' . $filename)) {
  Ui::displayMsg(sprintf(_('Cannot find %s'), Config::getSetting('legacy', 'EXISTING_DATA') . '/' . $filename), 'error');
  exit;
}

$data = @file(Config::getSetting('legacy', 'EXISTING_DATA') . '/' . $filename);


// process data
$numLines = count($data);

for ($i = 0; $i < $numLines; $i++) {
  if (isset($data[$i]) && ($data[$i][0] == '[')) {
    // get type header
    $type = trim(rtrim($data[$i]), '][');

    // extract data
    if ($type == 'program') {
      $link['type']  = $type;
      $link['name']  = rtrim($data[++$i]);
      $link['area']  = rtrim($data[++$i]);
      $link['url']   = rtrim($data[++$i]);

    } else {
      if ($type == 'external_link') {
        $type = 'external';
      }

      $link['type']  = $type;
      $link['name']  = rtrim($data[++$i]);
      $link['url']   = rtrim($data[++$i]);
    }

    // insert into DB
    Db::insert('links', $link, true);

    // report success
    Ui::displayMsg(sprintf(_('Inserted %s (%s)'), $link['name'], $link['type']));

    unset($link);
  }
}


// draw html page end
echo Ui::drawHtmlPageEnd();

?>