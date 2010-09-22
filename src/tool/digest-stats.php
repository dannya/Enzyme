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


// look in directory for files
if ($_REQUEST['type'] == 'issue') {
  $dirName = EXISTING_ISSUES;

} else if ($_REQUEST['type'] == 'archive') {
  $dirName = EXISTING_ARCHIVE;

} else {
  echo _('Invalid import type (needs to be "issue" or "archive")');
  exit;
}


$target   = 'statistics.txt';


// get sorted dir structure for iterating
$dirs = App::getDirs($dirName);


// iterate
foreach ($dirs as $dir) {
  // load file
  @$file = file($dir . '/' . $target);

  if (!$file) {
    echo Ui::displayMsg(sprintf(_('File %s could not be found...'), $path . '/' . $target), 'error');
    continue;
  }

  // extract date
  $theDate        = explode('/', $dir);
  $stats['date']  = array_pop($theDate);

  // iterate through lines, extracting elements
  $order    = 0;
  $numLines = count($file);
  $skip     = false;

  for ($i = 0; $i < $numLines; $i++) {
    $line = trim($file[$i]);

    if (empty($line)) {
      continue;
    }

    if ($line[0] == '[') {
      $section = trim($line, '][');

      if ($section == 'digest') {
        // check if digest already exists in database
        $stats_query  = mysql_query('SELECT digest FROM digest_stats
                                     WHERE date = \'' . $stats['date'] . '\'
                                     LIMIT 1') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

        if (mysql_num_rows($stats_query) == 1) {
          Ui::displayMsg(sprintf(_('Skipped %s'), $stats['date']), 'msg_skip');

          $skip = true;
          break;
        }

      } else if ($section == 'statistics_period') {
        $tmp = explode(':', trim($file[++$i]));

        $stats['revision_start']  = $tmp[0];
        $stats['revision_end']    = $tmp[1];

      } else if ($section == 'total_commits') {
        $stats['total_commits'] = trim($file[++$i]);
      } else if ($section == 'total_lines') {
        $stats['total_lines'] = trim($file[++$i]);
      } else if ($section == 'new_files') {
        $stats['new_files'] = trim($file[++$i]);
      } else if ($section == 'total_files') {
        $stats['total_files'] = trim($file[++$i]);
      } else if ($section == 'active_developers') {
        $stats['active_developers'] = trim($file[++$i]);
      } else if ($section == 'open_bugs') {
        $stats['open_bugs'] = trim($file[++$i]);
      } else if ($section == 'open_wishes') {
        $stats['open_wishes'] = trim($file[++$i]);
      } else if ($section == 'bugs_opened') {
        $stats['bugs_opened'] = trim($file[++$i]);
      } else if ($section == 'bugs_closed') {
        $stats['bugs_closed'] = trim($file[++$i]);
      } else if ($section == 'wishes_opened') {
        $stats['wishes_opened'] = trim($file[++$i]);
      } else if ($section == 'wishes_closed') {
        $stats['wishes_closed'] = trim($file[++$i]);

      } else if ($section == 'i18n') {
        // extract i18n sections
        unset($tmp);

        ++$i;

        $tmp['date']        = $stats['date'];
        $tmp['identifier']  = trim($file[++$i]);
        $tmp['value']       = trim($file[++$i]);

        // insert into database
        Db::insert('digest_stats_i18n', $tmp, true);

      } else if ($section == 'bugfixer') {
        // extract bugfixer sections
        unset($tmp);

        ++$i;

        $tmp['date']        = $stats['date'];
        $tmp['identifier']  = trim($file[++$i]);
        $tmp['value']       = trim($file[++$i]);

        // insert into database
        Db::insert('digest_stats_bugfixers', $tmp, true);

      } else if (($section == 'buzz_program') || ($section == 'buzz_person')) {
        // extract buzz sections
        unset($tmp);

        $tmp['date'] = $stats['date'];

        if ($section == 'buzz_program') {
          $tmp['type']        = 'program';
          $tmp['identifier']  = trim($file[++$i]);
          ++$i;
          $tmp['value']       = trim($file[++$i]);

        } else if ($section == 'buzz_person') {
          $tmp['type']        = 'person';
          ++$i;
          $tmp['identifier']  = trim($file[++$i]);
          $tmp['value']       = trim($file[++$i]);
        }

        // insert into database
        Db::insert('digest_stats_buzz', $tmp, true);

      } else if ($section == 'module') {
        // extract module sections
        unset($tmp);

        $tmp['date']        = $stats['date'];
        $tmp['identifier']  = trim($file[++$i]);
        $tmp['value']       = trim($file[++$i]);

        // insert into database
        Db::insert('digest_stats_modules', $tmp, true);

      } else if ($section == 'developer') {
        // extract developer sections
        unset($tmp);

        // don't parse name
        ++$i;

        $tmp['date']            = $stats['date'];
        $tmp['identifier']      = trim($file[++$i]);
        $tmp['num_commits']     = trim($file[++$i]);
        $tmp['num_lines']       = trim($file[++$i]);

        // insert into database
        Db::insert('digest_stats_developers', $tmp, true);

      } else if ($section == 'sex') {
        unset($raw);

        while (substr_count($file[$i + 1], '[') == 0) {
          $line = explode(': ', trim($file[++$i]));

          $raw[] = array('date'       => $stats['date'],
                         'type'       => 'gender',
                         'identifier' => trim(reset($line), ')('),
                         'value'      => end($line));
        }

        // insert into database
        Db::insert('digest_stats_extended', $raw, true);

      } else if ($section == 'ages') {
        unset($raw);

        while (substr_count($file[$i + 1], '[') == 0) {
          $line = explode(': ', trim($file[++$i]));

          // convert identifier to new format
          $identifier = trim(reset($line), ')(');

          if ($identifier == 'under18') {
            $identifier = '-18';
          } else if ($identifier == '18to24') {
            $identifier = '18-25';
          } else if ($identifier == '25to34') {
            $identifier = '25-35';
          } else if ($identifier == '35to44') {
            $identifier = '35-45';
          } else if ($identifier == '45to54') {
            $identifier = '45-55';
          } else if ($identifier == '55to64') {
            $identifier = '55-65';
          } else if ($identifier == '65to74') {
            $identifier = '65-75';
          } else if ($identifier == '75to84') {
            $identifier = '75-85';
          } else if ($identifier != 'unknown') {
            $identifier = '85-';
          }

          $raw[] = array('date'       => $stats['date'],
                         'type'       => 'age',
                         'identifier' => $identifier,
                         'value'      => end($line));
        }

        // insert into database
        Db::insert('digest_stats_extended', $raw, true);

      } else if ($section == 'countries') {
        unset($raw);

        while (substr_count($file[$i + 1], '[') == 0) {
          $line = explode(': ', trim($file[++$i]));

          $raw[] = array('date'       => $stats['date'],
                         'type'       => 'country',
                         'identifier' => trim(reset($line), ')('),
                         'value'      => end($line));
        }

        // insert into database
        Db::insert('digest_stats_extended', $raw, true);

      } else if ($section == 'motivation') {
        unset($raw);

        while (substr_count($file[$i + 1], '[') == 0) {
          $line = explode(': ', trim($file[++$i]));

          $raw[] = array('date'       => $stats['date'],
                         'type'       => 'motivation',
                         'identifier' => trim(reset($line), ')('),
                         'value'      => end($line));
        }

        // insert into database
        Db::insert('digest_stats_extended', $raw, true);

      } else if ($section == 'colours') {
        unset($raw);

        while (substr_count($file[$i + 1], '[') == 0) {
          $line   = explode(': ', trim($file[++$i]));
          $value  = end($line);

          // don't add item if value is blank
          if (empty($value)) {
            continue;
          }

          $raw[] = array('date'       => $stats['date'],
                         'type'       => 'colour',
                         'identifier' => trim(reset($line), ')('),
                         'value'      => $value);
        }

        // insert into database
        Db::insert('digest_stats_extended', $raw, true);
      }
    }
  }


  // insert into database?
  if (!$skip) {
    Db::insert('digest_stats', $stats, true);

    // report success
    Ui::displayMsg(sprintf(_('Processed %s'), $stats['date']));
  }
}


// draw html page end
echo Ui::drawHtmlPageEnd();

?>