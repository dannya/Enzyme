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


// check authentication
$user = new User();

if (empty($user->auth)) {
  echo _('Must be logged in!');
  exit;
}


set_time_limit(0);
ob_start();


// draw html page start
echo Ui::drawHtmlPageStart(null, array('/css/common.css'), array('/js/prototype.js'));


// load accounts
$accounts = Db::reindex(Db::load('authors', false), 'name');


// load data
$filename = 'filetypes.txt';

if (!is_file(EXISTING_DATA . '/' . $filename)) {
  Ui::displayMsg(sprintf(_('Cannot find %s'), EXISTING_DATA . '/' . $filename), 'error');
  exit;
}

$data = @file(EXISTING_DATA . '/' . $filename);


// process data
foreach ($data as $row) {
  $row = rtrim($row);

  if (empty($row)) {
    // blank line, skip
    continue;

  } else if ($row[0] == '[') {
    // get filetype header
    $type = trim($row, '][');
    continue;
  }

  // split data
  $row = explode('#####', $row);

  // assign data
  $filetype['extension']  = $row[0];
  $filetype['name']       = $row[1];
  $filetype['type']       = $type;

  // insert into DB
  Db::insert('filetypes', $filetype, true);

  // report success
  Ui::displayMsg(sprintf('Inserted %s', $filetype['extension']));
}


// draw html page end
echo Ui::drawHtmlPageEnd();

?>