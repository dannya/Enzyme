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
echo Ui::drawHtmlPageStart(null, array('/css/common.css'), array('/js/prototype.js'));


// load data
$filename = 'people_kde.txt';

if (!is_file(EXISTING_DATA . '/' . $filename)) {
  Ui::displayMsg(sprintf(_('Cannot find %s'), EXISTING_DATA . '/' . $filename), 'error');
  exit;
}

$data = @file(EXISTING_DATA . '/' . $filename);


// load existing developer records
$existing = Enzyme::getDevelopers();


// process data
$numLines = count($data);

for ($i = 0; $i < $numLines; $i++) {
  if (isset($data[$i]) && (rtrim($data[$i]) == '[person]')) {
    // assign data
    $developer               = array();
    $developer['account']    = rtrim($data[$i + 2]);
    $developer['nickname']   = rtrim($data[$i + 3]);
    $developer['dob']        = rtrim($data[$i + 5]);
    $developer['gender']     = rtrim($data[$i + 6]);
    $developer['continent']  = rtrim($data[$i + 7]);
    $developer['country']    = rtrim($data[$i + 8]);
    $developer['location']   = rtrim($data[$i + 9]);
    $developer['latitude']   = rtrim($data[$i + 10]);
    $developer['longitude']  = rtrim($data[$i + 11]);
    $developer['motivation'] = rtrim($data[$i + 12]);
    $developer['employer']   = rtrim($data[$i + 13]);
    $developer['colour']     = rtrim($data[$i + 14]);

    // also extract name / email?
    if (empty($existing[$developer['account']]['name'])) {
      $developer['name']     = rtrim($data[$i + 1]);
    }
    if (empty($existing[$developer['account']]['email'])) {
      $developer['email']    = rtrim($data[$i + 4]);
    }

    // set to null if empty
    foreach ($developer as &$value) {
      if (empty($value)) {
        $value = null;
      }
    }


    // insert into database (update if existing)
    if (!isset($existing[$developer['account']])) {
      // insert
      Db::insert('developers', $developer);

    } else {
      // update
      Db::save('developers', array('account' => $developer['account']), $developer);
    }

    Db::insert('developer_privacy', array('account' => $developer['account']), true);


    // report success
    Ui::displayMsg(sprintf(_('Inserted %s'), $developer['account']));

    // increment to next person block
    unset($developer, $privacy);
    $i += 15;

  } else {
    // could not find person block
    Ui::displayMsg(sprintf(_('Error detected on line %d'), $i), 'error');
    ++$i;
  }
}



// draw html page end
echo Ui::drawHtmlPageEnd();

?>