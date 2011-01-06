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
if (!$user->hasPermission('admin') && !$user->hasPermission('feature-editor')) {
  App::returnHeaderJson(true, array('permission' => false));
}


if (isset($_REQUEST['newItem'])) {
  // insert new item
  $item = array('date'    => '0000-00-00',
                'number'  => Enzyme::getFreeFeatureArticleNum('0000-00-00'),
                'status'  => 'idea',
                'intro'   => $_REQUEST['newItem']);

  $json['success'] = Db::insert('digest_intro_sections', $item);

} else {
  // ensure needed params are set
  if (!isset($_REQUEST['date']) || !isset($_REQUEST['values']) || !isset($_REQUEST['number'])) {
    App::returnHeaderJson(true, array('missing' => true));
  }

  // save changes
  parse_str($_REQUEST['values'], $data);
  $validFields = array('number', 'author', 'date', 'status');


  // define item to be changed
  $filter = array('date'    => $_REQUEST['date'],
                  'number'  => $_REQUEST['number']);


  // define changes to be made
  foreach ($data as $key => $value) {
    // check for sanity
    if (!in_array($key, $validFields)) {
      App::returnHeaderJson(true, array('error' => true));
    }

    // set value
    $values[$key] = $value;

    if ($key == 'date') {
      // if new date set, set non-conflicting number too
      $values['number'] = Enzyme::getFreeFeatureArticleNum($values['date']);

    } else if (($key == 'status') && ($value == 'idea')) {
      // if setting status back to idea, reset author and date too
      $values['date']   = '0000-00-00';
      $values['author'] = null;
    }
  }


  // save change
  $json['success'] = Db::save('digest_intro_sections', $filter, $values, true);
}


// report success
App::returnHeaderJson();

?>