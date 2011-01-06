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


set_time_limit(0);
ob_start();


// draw html page start
echo Ui::drawHtmlPageStart(null, array('/css/common.css'), array('/js/prototype.js'));


// look in directory for files
if ($_REQUEST['type'] == 'issue') {
  $issueType  = 'issue';
  $author     = 'dannya';
  $dirName    = EXISTING_ISSUES;

} else if ($_REQUEST['type'] == 'archive') {
  $issueType  = 'archive';
  $author     = 'dkite';
  $dirName    = EXISTING_ARCHIVE;

} else {
  echo _('Invalid import type (needs to be "issue" or "archive")');
  exit;
}


$target = 'introduction.txt';


// get next valid ID (auto-increment doesn't work on our db server!)
$id = Db::getNextId('digests');


// get sorted dir structure for iterating
$dirs = App::getDirs($dirName);


// iterate
foreach ($dirs as $dir) {
  // load file
  @$file = file($dir . '/' . $target);

  // iterate through lines, extracting elements
  $order    = 0;
  $numLines = count($file);

  for ($i = 0; $i < $numLines; $i++) {
    $line = trim($file[$i]);

    if (empty($line)) {
      continue;
    }

    if ($line[0] == '[') {
      $section = trim($line, '][');

      if ($section == 'digest') {
        $digest['date']       = trim($file[++$i]);
        $digest['type']       = $issueType;
        $digest['version']    = 1;
        $digest['published']  = 1;

      } else if ($section == 'translation') {
        $digest['language'] = trim($file[++$i]);

        if ($digest['language'] == 'en') {
          $digest['language'] = 'en_US';
        }

      } else if ($section == 'synopsis') {
        $digest['synopsis'] = Enzyme::formatMsg(trim($file[++$i]));

      } else if (($section == 'message') || ($section == 'comment')) {
        // extract intro sections
        unset($tmp);

        $tmp['date']    = $digest['date'];
        $tmp['number']  = ++$order;
        $tmp['type']    = $section;
        $tmp['author']  = $author;
        $tmp['intro']   = Enzyme::formatMsg(trim($file[++$i]));

        if (($section == 'message')) {
          $tmp['body']  = Enzyme::formatMsg(trim($file[++$i]));
        }

        // insert into database
        Db::insert('digest_intro_sections', $tmp, true);

      } else if ($section == 'people_references') {
        $personOrder = 0;

        while (!empty($file[$i + 1]) && (trim($file[$i + 1]) != '')) {
          $person['date']     = $digest['date'];
          $person['number']   = ++$personOrder;
          $person['name']     = trim($file[++$i]);
          $person['account']  = trim($file[++$i]);

          Db::insert('digest_intro_people', $person, true);
        }

      } else if ($section == 'video_references') {
        $videoOrder = 0;

        while (!empty($file[$i + 1]) && (trim($file[$i + 1]) != '')) {
          $video['date']      = $digest['date'];
          $video['number']    = ++$videoOrder;
          $video['name']      = trim($file[++$i]);
          $video['file']      = trim($file[++$i]);
          $video['youtube']   = trim($file[++$i]);
          ++$i;

          // insert into database
          Db::insert('digest_intro_videos', $video, true);
        }
      }
    }
  }


  // insert into database
  $digest['id']     = ++$id;
  $digest['author'] = $author;

  Db::insert('digests', $digest, true);

  // report success
  Ui::displayMsg(sprintf(_('Processed %s'), $digest['date']), 'msg_skip');
}


// draw html page end
echo Ui::drawHtmlPageEnd();

?>