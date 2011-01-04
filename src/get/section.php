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
if (empty($_REQUEST['date']) || !isset($_REQUEST['context']) || empty($_REQUEST['values'])) {
  App::returnHeaderJson(true, array('success' => false));
}


// check authentication
$user = new User();

if (empty($user->auth)) {
  App::returnHeaderJson(true, array('success' => false));
}


// process data into format for database saving
parse_str($_REQUEST['values'], $values);

if ($_REQUEST['context'] == 'new-digest') {
  $table   = 'digests';
  $data    = array('date'      => $_REQUEST['date'],
                   'type'      => $values['info-type'],
                   'language'  => $values['info-language'],
                   'author'    => $values['info-editor']);

  // insert new digest
  $json['success'] = Db::insert($table, $data, true);

  // don't save later!
  $skip = true;

} else if ($_REQUEST['context'] == 'info') {
  $table   = 'digests';
  $filter  = array('date'      => $_REQUEST['date']);
  $data    = array('id'        => $values['id'],
                   'date'      => $values['date'],
                   'type'      => $values['type'],
                   'language'  => $values['language'],
                   'author'    => $values['editor'],
                   'published' => $values['published']);

} else if ($_REQUEST['context'] == 'synopsis') {
  $table   = 'digests';
  $filter  = array('date'      => $_REQUEST['date']);
  $data    = array('synopsis'  => $values['synopsis']);

} else if ($_REQUEST['context'] == 'introduction') {
  $table   = 'digest_intro_sections';
  $filter  = array('date'      => $_REQUEST['date'],
                   'number'    => $values['number']);
  $data    = array('type'      => $values['type']);

  // set optional values
  if (isset($values['status'])) {
    $data['status'] = $values['status'];
  }
  if (isset($values['intro'])) {
    $data['intro'] = $values['intro'];
  }
  if (isset($values['body'])) {
    $data['body'] = $values['body'];
  }

  if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'insert') {
      // insert, add additional fields
      $data['date']   = $_REQUEST['date'];
      $data['number'] = $values['number'];
      $data['author'] = $user->data['username'];

      // insert
      $json['success']  = Db::insert($table, $data);
      $skip             = true;

    } else if ($_REQUEST['action'] == 'delete') {
      $json['success']  = Db::delete($table, $filter);
      $skip             = true;
    }
  }

} else {
  // context not valid, exit
  App::returnHeaderJson(true, array('success' => false));
}


// save data
if (!isset($skip)) {
  $json['success'] = Db::save($table, $filter, $data);
}


// clear issues list caches
Cache::delete(array('issue_latest',
                    'issue_earliest',
                    'archive_latest',
                    'archive_earliest'), 'digest');


// report success
App::returnHeaderJson();

?>