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


// get sorted dir structure for iterating
$dirs = App::getDirs(EXISTING_ISSUES);


// iterate
foreach ($dirs as $dir) {
  // load files
  @$file    = file($dir . '/selections.txt');
  @$diff    = file($dir . '/diffs.txt');

  // extract date
  $theDate  = explode('/', $dir);
  $theDate  = array_pop($theDate);

  // iterate through lines, extracting elements
  $j        = 0;
  $numLines = count($file);

  for ($i = 0; $i < $numLines; $i++) {
    $line = trim($file[$i]);

    if (empty($line)) {
      continue;
    }

    if ($line[0] == '[') {
      // extract data
      $revision            = trim($file[++$i]);
      $raw['basepath']     = trim($file[++$i]);
      $raw['area']         = trim($file[++$i]);
      $raw['type']         = trim($file[++$i]);
      $raw['developer']    = trim($file[$i += 2]);

      // insert into commits
      $data['revision']    = $revision;
      $data['date']        = $theDate;
      $data['developer']   = $raw['developer'];
      $data['basepath']    = $raw['basepath'];
      $data['msg']         = trim(Enzyme::formatMsg(trim($file[++$i])));

      Db::insert('commits', $data, true);
      unset($data);


      // look for diff details (commit_files)
      if ($diff) {
        if ($found = array_search($revision . "\n", $diff)) {
          $tmp['basepath'] = trim($diff[++$found]);

          $paths           = trim($diff[++$found]);
          $tmp['files']    = explode(':', $paths);

          if (!empty($paths) && (strpos($paths, ' (') !== false)) {
            // remove invalid parts of paths (spaces, etc)
            foreach ($tmp['files'] as $key => &$thePath) {
              if (strpos($thePath, '/') === false) {
                // remove if not a valid path
                unset($tmp['files'][$key]);

              } else {
                // separate path from (...) content
                $thePath = explode(' (', $thePath);
                $thePath = reset($thePath);
              }
            }
          }

          // pick out diffs by type (modified, added, ...)
          $tmp['m']        = array_flip(explode(':', trim($diff[++$found])));
          $tmp['a']        = array_flip(explode(':', trim($diff[++$found])));
          $tmp['d']        = array_flip(explode(':', trim($diff[++$found])));
          $tmp['visual']   = array_flip(explode(':', trim($diff[++$found])));


          // get bugs?
          if (isset($diff[++$found])) {
            $tmpBugs = trim($diff[$found]);

            if (!empty($tmpBugs) && !is_numeric($tmpBugs) && ($tmpBugs != '[diffs]')) {
              $bugs = explode('::', rtrim($tmpBugs, ':'));

              foreach ($bugs as $theBug) {
                $theBug = explode('@@', $theBug);

                $bug['revision'] = $revision;
                $bug['bug']      = reset($theBug);

                // get extra bug data
                Enzyme::getBugData(&$bug, true);

                // add bug to database
                Db::insert('commit_bugs', $bug, true);
              }
            }
          }


          // compile and store data in database
          $data['revision']      = $revision;

          foreach ($tmp['files'] as $num => $path) {
            // match counter to stored format
            ++$num;

            if (isset($tmp['a'][$num])) {
              $data['operation'] = 'A';
            } else if (isset($tmp['m'][$num])) {
              $data['operation'] = 'M';
            } else if (isset($tmp['d'][$num])) {
              $data['operation'] = 'D';
            }

            $data['path'] = $tmp['basepath'] . trim($path);

            // insert
            Db::insert('commit_files', $data, true);
          }

          unset($data, $tmp);
        }

      } else {
        // fetch details
      }


      // add to commits_reviewed
      $commit['revision']     = $revision;
      $commit['reviewed']     = $theDate;
      $commit['classified']   = $theDate;
      $commit['area']         = convertArea($raw['area']);
      $commit['type']         = convertType($raw['type']);
      $commit['reviewer']     = 'dannya';
      $commit['classifier']   = 'dannya';

      Db::insert('commits_reviewed', $commit, true);
      unset($commit);
    }
  }


  // display progress
  if ($file) {
    Ui::displayMsg(sprintf(_('Processed %s'), $theDate));
  }
}


// clear bugs list cache
Cache::delete('bugs');


// notify that script has finished output
Ui::setProcessFinished();


// draw html page end
echo Ui::drawHtmlPageEnd();




// helper functions
function convertArea($area) {
  $data = array('accessibility'      => 1,
                'development tools'  => 2,
                'educational'        => 3,
                'graphics'           => 4,
                'kde-base'           => 5,
                'kde-pim'            => 6,
                'office'             => 7,
                'konqueror'          => 8,
                'multimedia'         => 9,
                'networking tools'   => 10,
                'user interface'     => 11,
                'utilities'          => 12,
                'games'              => 13,
                'other'              => 14);

  return $data[$area];
}


function convertType($type) {
  $data = array('bug fixes'  => 1,
                'features'   => 2,
                'optimise'   => 3,
                'security'   => 4,
                'other'      => 5);

  return $data[$type];
}


?>