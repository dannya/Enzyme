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


// load data
$filename = 'people_kde.txt';

if (!is_file(EXISTING_DATA . '/' . $filename)) {
  Ui::displayMsg(sprintf(_('Cannot find %s'), EXISTING_DATA . '/' . $filename), 'error');
  exit;
}

$data = @file(EXISTING_DATA . '/' . $filename);


// process data
$numLines = count($data);

for ($i = 0; $i < $numLines; $i++) {
  if (isset($data[$i]) && (rtrim($data[$i]) == '[person]')) {
    // assign data
    $person['account']    = rtrim($data[$i + 2]);
    $person['nickname']   = rtrim($data[$i + 3]);
    $person['dob']        = rtrim($data[$i + 5]);
    $person['gender']     = rtrim($data[$i + 6]);
    $person['continent']  = rtrim($data[$i + 7]);
    $person['country']    = rtrim($data[$i + 8]);
    $person['location']   = rtrim($data[$i + 9]);
    $person['latitude']   = rtrim($data[$i + 10]);
    $person['longitude']  = rtrim($data[$i + 11]);
    $person['motivation'] = rtrim($data[$i + 12]);
    $person['employer']   = rtrim($data[$i + 13]);
    $person['colour']     = rtrim($data[$i + 14]);

    // process data
    if ($person['gender'] == 'male') {
      $person['gender'] = 'm';
    } else if ($person['gender'] == 'female') {
      $person['gender'] = 'f';
    }

    if ($person['motivation'] == 'volunteer') {
      $person['motivation'] = 1;
    } else if ($person['motivation'] == 'commercial') {
      $person['motivation'] = 2;
    }

    // insert into DB
    Db::insert('people', $person, true);

    // report success
    Ui::displayMsg(sprintf(_('Inserted %s'), $person['account']));

    // increment to next person block
    unset($person);
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