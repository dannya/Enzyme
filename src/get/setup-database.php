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


// only allow database to be created if it Enzyme has not yet been configured!
$databaseExists = Db::connect();

if ($databaseExists) {
  App::returnHeaderJson(true, array('configured' => true));
}


// get SQL files
$sql  = App::getFiles(BASE_DIR . '/sql/', 'sql');
$data = App::getFiles(BASE_DIR . '/sql/data/', 'sql');

if (!$sql || !$data) {
  App::returnHeaderJson(true, array('files' => false));
}


// create and select database
$create = Db::create();

if (!$create) {
  App::returnHeaderJson(true, array('create' => false));
}


$json['success'] = true;

// create each database from loaded file
foreach ($sql as $file) {
  $success = Db::sql(file_get_contents($file), false, true);

  // extract table name
  $table = preg_split('/[\/\\\.]/', $file, null, PREG_SPLIT_NO_EMPTY);
  $table = $table[count($table) - 2];

  // display success
  if ($success) {
    $output[]         = '<i>' . sprintf(_('Successfully created table "%s"'), $table) . '</i>';
  } else {
    $output[]         = '<b>' . sprintf(_('Error creating table "%s"'), $table) . '</b>';
    $json['success']  = false;
  }
}


// import data from loaded file
foreach ($data as $file) {
  $success = Db::sql(file_get_contents($file), false, true);

  // extract table name
  $table = preg_split('/[\/\\\.]/', $file, null, PREG_SPLIT_NO_EMPTY);
  $table = $table[count($table) - 2];

  // display success
  if ($success) {
    $output[]         = '<i>' . sprintf(_('Successfully imported data into table "%s"'), $table) . '</i>';
  } else {
    $output[]         = '<b>' . sprintf(_('Error importing data into table "%s"'), $table) . '</b>';
    $json['success']  = false;
  }
}


// set output
$json['output'] = implode('<br />', $output);


// clear possible settings cache
Cache::delete('settings');


// report success
App::returnHeaderJson();

?>