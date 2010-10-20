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


class Enzyme {
  // define excluded SVN accounts
  public static function excludedAccounts() {
    return array('scripty');
  }


  public static function getBugData(&$bug, $displayMsg = false) {
    // put into correct format if only bug number provided
    if (!is_array($bug)) {
      $bug['bug'] = $bug;
    }

    // TODO: sanity check
    if ($bug['bug'] == 0) {
      print_r($bug);
      exit;
    }

    // get list of existing bugs, to check if we need to fetch data
    $existingBugs = Cache::loadSave('bugs', 'Enzyme::loadBugs');

    if (isset($bug['revision']) && isset($existingBugs[$bug['bug'] . '-' . $bug['revision']])) {
      // bug already exists in db
      $bug = $existingBugs[$bug['bug'] . '-' . $bug['revision']];

    } else {
      // get bug page
      $page = simplexml_load_string(file_get_contents(WEBBUG_XML . $bug['bug']));

      // extract data
      if ($page) {
        $bug['date']       = (string)$page->bug->creation_ts;
        $bug['title']      = (string)$page->bug->short_desc;
        $bug['product']    = (string)$page->bug->product;
        $bug['component']  = (string)$page->bug->component;
        $bug['votes']      = (string)$page->bug->votes;
        $bug['status']     = (string)$page->bug->bug_status;
        $bug['resolution'] = (string)$page->bug->resolution;
      }

      // display progress?
      if ($displayMsg) {
        echo Ui::displayMsg(sprintf(_('Fetched details for bug %d'), $bug['bug']), 'msg_fetch');
      }
    }

    return $bug;
  }


  public static function getBugs(&$revisions) {
    if ($revisions) {
      // load from db
      $q = mysql_query('SELECT * FROM commit_bugs
                        WHERE revision IN (' . implode(',', array_keys($revisions)) . ')') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

      while ($row = mysql_fetch_assoc($q)) {
        $revisions[$row['revision']]['bug'][] = $row;
      }

      return $revisions;
    }
  }


  public static function getAreas($spacer = false) {
    $buf = array('accessibility'      => _('Accessibility'),
                 'development-tools'  => _('Development Tools'),
                 'educational'        => _('Educational'),
                 'graphics'           => _('Graphics'),
                 'kde-base'           => _('KDE Base'),
                 'kde-pim'            => _('KDE-PIM'),
                 'koffice'            => _('KOffice'),
                 'konqueror'          => _('Konqueror'),
                 'multimedia'         => _('Multimedia'),
                 'networking-tools'   => _('Networking Tools'),
                 'user-interface'     => _('User Interface'),
                 'utilities'          => _('Utilities'),
                 'games'              => _('Games'),
                 'other'              => _('Other'));

    // add a blank spacer entry at the start?
    if ($spacer) {
      array_unshift($buf, '');
    }

    return $buf;
  }


  public static function getTypes($spacer = false) {
    $buf = array('bug-fixes'  => _('Bug Fixes'),
                 'features'   => _('Features'),
                 'optimize'   => _('Optimize'),
                 'security'   => _('Security'),
                 'other'      => _('Other'));

    // add a blank spacer entry at the start?
    if ($spacer) {
      array_unshift($buf, '');
    }

    return $buf;
  }


  public static function getAvailableSettings() {
    // define each setting
    $tmp['PROJECT_NAME']      = array('title'   => _('Project Name'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => 'My Project');
    $tmp['ADMIN_EMAIL']       = array('title'   => _('Admin Email'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => 'admin@example.com');
    $tmp['ENZYME_URL']        = array('title'   => _('Enzyme URL'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => 'http://enzyme.commit-digest.org/');
    $tmp['DIGEST_URL']        = array('title'   => _('Digest URL'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => 'http://commit-digest.org/');
    $tmp['HELP_URL']          = array('title'   => _('Help URL'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => 'http://github.com/dannyakakong/Enzyme/wiki');
    $tmp['SMTP']              = array('title'   => _('SMTP Mail Server'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => 'smtp.example.com');

    $tmp['REPOSITORY_TYPE']   = array('title'   => _('Repository Type'),
                                      'valid'   => array('svn' => _('Subversion')),
                                      'default' => 'svn',
                                      'example' => null);
    $tmp['REPOSITORY']        = array('title'   => _('Repository URL'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => 'svn://anonsvn.kde.org/home/kde/');
    $tmp['ACCOUNTS_FILE']     = array('title'   => _('Accounts File'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => 'trunk/common/accounts.txt');

    $tmp['WEBSVN']            = array('title'   => _('Web Repository Viewer'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => null);
    $tmp['WEBBUG']            = array('title'   => _('Web Bug Tracker'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => null);
    $tmp['WEBBUG_XML']        = array('title'   => _('Web Bug Tracker XML'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => null);
    $tmp['I18N_STATS']        = array('title'   => _('I18n Stats URL'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => null);
    $tmp['I18N_TEAMS']        = array('title'   => _('I18n Teams URL'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => null);
    $tmp['BUG_STATS']         = array('title'   => _('Bug Stats URL'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => null);

    $tmp['DEFAULT_TIMEZONE']  = array('title'   => _('Default Timezone'),
                                      'valid'   => array('Europe/London' => 'Europe/London'),
                                      'default' => 'Europe/London',
                                      'example' => null);
    $tmp['DEFAULT_LANGUAGE']  = array('title'   => _('Default Language'),
                                      'valid'   => Digest::getLanguages(),
                                      'default' => null,
                                      'example' => null);

    $tmp['ENABLE_LEGACY']     = array('title'   => _('Enable Legacy Import'),
                                      'valid'   => array('0'  => _('No'),
                                                         '1'  => _('Yes')),
                                      'default' => '0',
                                      'example' => null);
    $tmp['EXISTING_ISSUES']   = array('title'   => _('Existing Issues'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => null);
    $tmp['EXISTING_ARCHIVE']  = array('title'   => _('Existing Archive'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => null);
    $tmp['EXISTING_DATA']     = array('title'   => _('Existing Data'),
                                      'valid'   => null,
                                      'default' => null,
                                      'example' => null);

    return $tmp;
  }


  public static function getGroupedSettings() {
    // get available settings
    $tmp = self::getAvailableSettings();

    // order (array index), and classify into groups
    $settings[] = array('title'     => _('Enzyme Settings'),
                        'settings'  => array('PROJECT_NAME'           => $tmp['PROJECT_NAME'],
                                             'ADMIN_EMAIL'            => $tmp['ADMIN_EMAIL'],
                                             'ENZYME_URL'             => $tmp['ENZYME_URL'],
                                             'DIGEST_URL'             => $tmp['DIGEST_URL'],
                                             'SMTP'                   => $tmp['SMTP']));

    $settings[] = array('title'     => _('Repository'),
                        'settings'  => array('REPOSITORY_TYPE'        => $tmp['REPOSITORY_TYPE'],
                                             'REPOSITORY'             => $tmp['REPOSITORY'],
                                             'ACCOUNTS_FILE'          => $tmp['ACCOUNTS_FILE']));

    $settings[] = array('title'     => _('Data Locations'),
                        'settings'  => array('WEBSVN'                 => $tmp['WEBSVN'],
                                             'WEBBUG'                 => $tmp['WEBBUG'],
                                             'WEBBUG_XML'             => $tmp['WEBBUG_XML'],
                                             'I18N_STATS'             => $tmp['I18N_STATS'],
                                             'I18N_TEAMS'             => $tmp['I18N_TEAMS'],
                                             'BUG_STATS'              => $tmp['BUG_STATS']));

    $settings[] = array('title'     => _('Display Defaults'),
                        'settings'  => array('DEFAULT_TIMEZONE'       => $tmp['DEFAULT_TIMEZONE'],
                                             'DEFAULT_LANGUAGE'       => $tmp['DEFAULT_LANGUAGE']));

    $settings[] = array('title'     => _('Legacy Import'),
                        'settings'  => array('ENABLE_LEGACY'          => $tmp['ENABLE_LEGACY'],
                                             'EXISTING_ISSUES'        => $tmp['EXISTING_ISSUES'],
                                             'EXISTING_ARCHIVE'       => $tmp['EXISTING_ARCHIVE'],
                                             'EXISTING_DATA'          => $tmp['EXISTING_DATA']));

    return $settings;
  }


  public static function getAvailableJobs() {
    // define available jobs / i18n strings
    $possible['reviewer']   = array('title'       => _('Commit Reviewer'),
                                    'description' => _('Commit Reviewers look at all the recent commits, selecting those which are significant and interesting enough to be included into the weekly Commit-Digest.'));

    $possible['classifier'] = array('title'       => _('Commit Classifier'),
                                    'description' => _('Commit Classifiers sort the selected commits into areas (which is partly automated), and by type (such as bug fix, feature, etc).'));

    $possible['editor']     = array('title'       => _('Feature Editor'),
                                    'description' => _('Feature Editors contact people working on interesting projects and assist them in writing original pieces which are presented in the introduction of each Commit-Digest.'));

    $possible['translator'] = array('title'       => _('Translator'),
                                    'description' => _('Translators increase the reach of the Commit-Digest and the work done across the project by making the weekly Commit-Digests (and the website interfaces) available in the native language of people around the world.'));


    // TODO: store this data in db, create management interface in Enzyme
    $available = array('reviewer', 'classifier', 'editor', 'translator');

    // link up available jobs with i18n'd strings
    $jobs = array();

    foreach ($available as $theJob) {
      $jobs[$theJob] = $possible[$theJob];
    }

    return $jobs;
  }


  public static function formatMsg($msg) {
    return str_ireplace(array('<br>', "\n", '&'), array('<br />', '<br />', '&amp;'), $msg);
  }


  public static function getProcessedRevisions($type = null, $start = null, $end = null,
                                               $exclude = null, $limit = ' LIMIT 100') {
    $filter             = ' WHERE 1';
    $revisions          = null;
    $existingRevisions  = null;

    // get specified range of revisions?
    if ($start && $end) {
      while ($start <= $end) {
        $revisions[] = $start++;
      }

      $filter .= ' AND commits.revision IN (' . implode(',', $revisions) . ')';
    }


    // exclude accounts?
    if ($exclude) {
      if ($exclude === true) {
        // exclude accounts on system exclusion list
        $exclude = self::excludedAccounts();
      }

      $filter .= ' AND commits.author NOT IN ("' . implode('","', $exclude) . '")';
    }


    // exclude paths?
    $ignorePaths = self::getIgnoredPaths();

    if ($ignorePaths) {
      foreach ($ignorePaths as $path) {
        $filter .= ' AND commits.basepath NOT LIKE ' . Db::quote('%' . $path . '%');
      }
    }


    // exclude message fragments? (ie. SVN_SILENT)
    $ignoreFragments = self::getIgnoredFragments();

    if ($ignoreFragments) {
      foreach ($ignoreFragments as $fragment) {
        $filter .= ' AND commits.msg NOT LIKE ' . Db::quote('%' . $fragment . '%');
      }
    }


    // don't get commits with no msg written
    $filter .= ' AND commits.msg != ""';


    // only get commits at specific review stage?
    if ($type == 'unreviewed') {
      $fields   = 'commits.*';
      $table    = 'commits LEFT JOIN commits_reviewed
                   ON commits.revision = commits_reviewed.revision';
      $filter  .= ' AND commits_reviewed.revision IS NULL';

    } else if (($type == 'reviewed') || ($type == 'unmarked') || ($type == 'marked')) {
      $fields   = 'commits_reviewed.*, commits.*';
      $table    = 'commits_reviewed, commits';

      if ($type == 'unmarked') {
        $filter .= ' AND commits_reviewed.marked = 0';
      } else if ($type == 'marked') {
        $filter .= ' AND commits_reviewed.marked = 1
                     AND (commits_reviewed.type IS NULL
                          OR commits_reviewed.area IS NULL
                          OR commits_reviewed.type = 0
                          OR commits_reviewed.area = 0)';
      }

      // do join to get commit authors
      $filter .= ' AND commits_reviewed.revision = commits.revision';

    } else {
      $fields   = '*';
      $table    = 'commits';
    }

    // execute query
    $selectQuery  = 'SELECT ' . $fields . ' FROM ' . $table . $filter . $limit;
    $q            = mysql_query($selectQuery) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $existingRevisions[$row['revision']] = $row;
    }

    return $existingRevisions;
  }


  public static function getProcessedRevisionsList($start, $end) {
    $revisions         = array();
    $existingRevisions = array();

    // compile array of revisions between start and end
    while ($start <= $end) {
      $revisions[] = $start++;
    }

    // search
    $query = 'SELECT revision FROM commits WHERE revision IN (' . implode(',', $revisions) . ')';
    $q     = mysql_query($query) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    // reindex
    while ($row = mysql_fetch_assoc($q)) {
      $existingRevisions[$row['revision']] = $row['revision'];
    }

    return $existingRevisions;
  }


  public static function getAuthors($revisions = null) {
    $filter       = null;
    $accountData  = array();

    // filter by authors present in provided revision data?
    if ($revisions) {
      foreach ($revisions as $revision) {
        $authors[$revision['author']] = $revision['author'];
      }

      $filter = ' WHERE account IN (\'' . implode('\', \'', $authors) . '\')';
    }


    // load from db
    $q = mysql_query('SELECT * FROM authors' . $filter) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $accountData[$row['account']] = $row;
    }

    return $accountData;
  }


  public static function getClassifications() {
    // TODO: store this data in db, create management interface in Enzyme
    $classifications  = array('/kdegraphics/'       => 4,
                              '/oxygen/'            => 11,
                              '/kdelibs/'           => 5,
                              '/multimedia/'        => 9,
                              '/kdemultimedia/'     => 9,
                              '/kdebindings/'       => 2,
                              '/kdesdk/'            => 2,
                              '/kdelibs/'           => 5,
                              '/graphics/'          => 4,
                              '/kdeutils/'          => 12,
                              '/kdepim/'            => 6,
                              '/koffice/'           => 7,
                              '/kdevelop/'          => 2,
                              '/kdevplatform/'      => 2,
                              '/kdeedu/'            => 3,
                              '/kopete/'            => 10,
                              '/kdebase/'           => 5,
                              '/kdeplasma-addons/'  => 5,
                              '/office/'            => 7,
                              '/utils/'             => 12,
                              '/base/'              => 5,
                              '/network/'           => 10,
                              '/pim/'               => 6,
                              '/kdegames/'          => 13,
                              '/games/'             => 13
    );

    return $classifications;
  }


  public static function getIgnoredPaths() {
    // TODO: store this data in db, create management interface in Enzyme
    $ignored  = array('/l10n-',
                      '/www/');

    return $ignored;
  }


  public static function getIgnoredFragments() {
    // TODO: store this data in db, create management interface in Enzyme
    $ignored  = array('SVN_SILENT',
                      'via svnmerge');

    return $ignored;
  }


  public static function getAuthorInfo($type, $key) {
    global $authors;

    $string = null;

    // if authors are not available, load
    if (!$authors) {
      $authors = self::getAuthors();
    }

    // if field is available, return
    if (!empty($authors[$key][$type])) {
      $string = $authors[$key][$type];
    }

    return $string;
  }


  public static function loadBugs() {
    $bugs = array();

    // load from db
    $q = mysql_query('SELECT * FROM commit_bugs') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $bugs[$row['bug'] . '-' . $row['revision']] = $row;
    }

    return $bugs;
  }


  public static function insertRevisions($start, $end, $showErrors = true) {
    // ensure script doesn't reach execution limits
    set_time_limit(0);
    ini_set('memory_limit', '256M');

    // show cmd errors?
    if (!$showErrors) {
      $showErrors = ' 2>&1';
    } else {
      $showErrors = null;
    }

    // allow start and end to be passed in any order
    if ($start < $end) {
      $boundaries['start']  = $start;
      $boundaries['end']    = $end;
    } else {
      $boundaries['start']  = $end;
      $boundaries['end']    = $start;
    }

    // get start and end revision numbers
    foreach ($boundaries as $boundary => $value) {
      if (is_numeric($value)) {
        // assume this is a revision number
        $revision[$boundary] = $value;

      } else {
        // assume this is a date
        $cmd    = 'svn log --xml -v -r {' . $value . '} ' . REPOSITORY;
        $data   = shell_exec(escapeshellcmd($cmd) . $showErrors);
        $data   = simplexml_load_string($data);

        $revision[$boundary] = (int)$data->logentry->attributes()->revision;
      }
    }


    // get list of processed commits so we don't fetch twice
    $processedRevisions = self::getProcessedRevisionsList($revision['start'], $revision['end']);


    // get revision information
    $cmd    = 'svn log --xml -v -r ' . $revision['start'] . ':' . $revision['end'] . ' ' .
              REPOSITORY;
    $data   = shell_exec(escapeshellcmd($cmd) . $showErrors);
    $data   = simplexml_load_string(utf8_encode($data));


    // initialise summary
    $summary['skipped']['title']    = 'Skipped: %d';
    $summary['skipped']['value']    = 0;
    $summary['processed']['title']  = 'Processed: %d';
    $summary['processed']['value']  = 0;


    // process and store data
    foreach ($data as $entry) {
      // set data into useful data structure
      unset($commit);

      // get commit revision
      $commit['revision']   = (int)$entry->attributes()->revision;


      // check if revision has already been processed
      if (isset($processedRevisions[$commit['revision']])) {
        if (COMMAND_LINE || !empty($_POST['show_skipped'])) {
          Ui::displayMsg(sprintf(_('Skipping revision %d'), $commit['revision']), 'msg_skip');
        }

        // increment summary counter
        ++$summary['skipped']['value'];

        continue;
      }


      // get additional commit data
      $commit['date']       = date('Y-m-d H:i:s', strtotime((string)$entry->date));
      $commit['author']     = (string)$entry->author;
      $commit['msg']        = self::processCommitMsg($commit['revision'], (string)$entry->msg);


      // insert commit files into database
      if (!empty($entry->paths->path[0])) {
        $tmpPaths               = array();
        $commitFile['revision'] = $commit['revision'];

        // hold in tmp variable to fix PHP memory issues
        $paths = $entry->paths->path;

        foreach ($paths as $path) {
          $commitFile['path']       = (string)$path;
          $commitFile['operation']  = (string)$path->attributes()->action;

          Db::insert('commit_files', $commitFile, true);

          // save data to enable base path calculation below
          $tmpPaths[] = $commitFile['path'];
        }

        // determine base commit path
        $commit['basepath'] = self::getBasePath($tmpPaths);
      }


      // insert commit into database
      Db::insert('commits', $commit, true);

      // report successful process/insertion
      Ui::displayMsg(sprintf(_('Processed revision %d'), $commit['revision']));

      // increment summary counter
      ++$summary['processed']['value'];
    }

    // display summary
    echo Ui::processSummary($summary, true);

    // clear bugs list cache
    Cache::delete('bugs');
  }


  public static function generateStats($start, $end) {
    // ensure script doesn't reach execution limits
    set_time_limit(0);
    ini_set('memory_limit', '256M');

    // allow start and end to be passed in any order
    if ($start < $end) {
      $boundaries['start']  = $start;
      $boundaries['end']    = $end;
    } else {
      $boundaries['start']  = $end;
      $boundaries['end']    = $start;
    }


    // get start and end revision numbers
    foreach ($boundaries as $boundary => $value) {
      if (is_numeric($value)) {
        // assume this is a revision number
        $revision[$boundary] = $value;

      } else {
        // assume this is a date
        $cmd    = 'svn log --xml -v -r {' . $value . '} ' . REPOSITORY;
        $data   = shell_exec(escapeshellcmd($cmd));
        $data   = simplexml_load_string($data);

        $revision[$boundary] = (int)$data->logentry->attributes()->revision;
      }
    }


    // get revision information
    Ui::displayMsg(_('Getting revision data...'));

    $cmd    = 'svn log --xml -v -r ' . $revision['start'] . ':' . $revision['end'] . ' ' .
              REPOSITORY;
    $data   = shell_exec(escapeshellcmd($cmd));
    $data   = simplexml_load_string(utf8_encode($data));


    // process and store data
    Ui::displayMsg(_('Parsing revision data...'));

    $totalFiles       = 0;
    $totalCommits     = $revision['end'] - $revision['start'];
    $excludedCommits  = 0;

    foreach ($data as $entry) {
      // skip if an excluded account!
      $excludedAccounts = self::excludedAccounts();

      if (in_array((string)$entry->author, $excludedAccounts)) {
        ++$excludedCommits;
        continue;
      }

      // set data into useful data structure
      if (!isset($stats['person'][(string)$entry->author]['commits'])) {
        // initialise counters
        $stats['person'][(string)$entry->author]['commits']  = 0;
        $stats['person'][(string)$entry->author]['files']    = 0;
      }

      // increment commit counter
      ++$stats['person'][(string)$entry->author]['commits'];

      // increment files counter
      $numFiles    = count($entry->paths->path);
      $totalFiles += $numFiles;

      $stats['person'][(string)$entry->author]['files'] += $numFiles;


      // extract module
      $basepath = self::getBasePath($entry->paths->path, 2);


      // increment module counter
      if (!isset($stats['module'][$basepath])) {
        $stats['module'][$basepath] = 1;
      } else {
        ++$stats['module'][$basepath];
      }
    }


    // process / insert people stats
    foreach ($stats['person'] as $person => $data) {
      $insert[] = array('date'         => $boundaries['end'],
                        'identifier'   => $person,
                        'num_commits'  => $data['commits'],
                        'num_files'    => $data['files']);
    }


    // insert into database
    Db::insert('digest_stats_developers', $insert, true);
    Ui::displayMsg(_('Inserted people stats'));
    unset($insert);


    // process / insert module stats
    foreach ($stats['module'] as $module => $data) {
      // exclude master basepaths (/trunk, /branches)
      if (substr_count($module, '/') < 2) {
        continue;
      }

      $insert[] = array('date'         => $boundaries['end'],
                        'identifier'   => $module,
                        'value'        => $data);
    }


    // insert into database
    Db::insert('digest_stats_modules', $insert, true);
    Ui::displayMsg(_('Inserted module stats'));
    unset($insert);


    // load people data to derive extended statistics (country, stats, age, etc)
    $peopleData = Db::reindex(Db::load('people', array('account' => array_keys($stats['person']))), 'account');


    // get extended statistics
    Ui::displayMsg(_('Extracting extended statistics...'));

    $keys                               = array('gender', 'dob', 'country', 'motivation', 'colour');
    $percentagePerCommit                = 100 / ($totalCommits - $excludedCommits);

    foreach ($stats['person'] as $person => $data) {
      // extract each field
      foreach ($keys as $key) {
        if (!isset($peopleData[$person][$key]) || ($peopleData[$person][$key] == '')) {
          if (!isset($stats['extended'][$key]['unknown'])) {
            // initialise unknown counter
            $stats['extended'][$key]['unknown']  = $percentagePerCommit * $data['commits'];
          } else {
            // increment unknown counter
            $stats['extended'][$key]['unknown'] += $percentagePerCommit * $data['commits'];
          }

        } else {
          if ($key == 'dob') {
            // set age range
            $age = (time() - strtotime($peopleData[$person][$key])) / 31556926;

            if ($age < 18) {
              $theKey = '-18';
            } else if ($age < 25) {
              $theKey = '18-25';
            } else if ($age < 35) {
              $theKey = '25-35';
            } else if ($age < 45) {
              $theKey = '35-45';
            } else if ($age < 55) {
              $theKey = '45-55';
            } else if ($age < 65) {
              $theKey = '55-65';
            } else if ($age < 75) {
              $theKey = '65-75';
            } else if ($age < 85) {
              $theKey = '75-85';
            } else {
              $theKey = '85-';
            }

          } else if ($key == 'motivation') {
            if ($peopleData[$person][$key] == 1) {
              $theKey = 'volunteer';
            } else if ($peopleData[$person][$key] == 2) {
              $theKey = 'commercial';
            } else {
              $theKey = 'unknown';
            }

          } else {
            $theKey = $peopleData[$person][$key];
          }

          if (!isset($stats['extended'][$key][$theKey])) {
            // initialise counter
            $stats['extended'][$key][$theKey]  = $percentagePerCommit * $data['commits'];
          } else {
            // increment counter
            $stats['extended'][$key][$theKey] += $percentagePerCommit * $data['commits'];
          }
        }
      }
    }


    // insert extended stats
    foreach ($stats['extended'] as $type => $data) {
      if ($type == 'dob') {
        $type = 'age';
      }

      foreach ($data as $key => $value) {
        // ensure no values are over 100%
        if ($value > 100) {
          $value = 100;
        }

        $insert[] = array('date'        => $boundaries['end'],
                          'type'        => $type,
                          'identifier'  => $key,
                          'value'       => round($value, 2));
      }
    }


    // insert into database
    Db::insert('digest_stats_extended', $insert, true);


    // get i18n stats
    if (I18N_STATS) {
      Ui::displayMsg(_('Getting internationalization (i18n) stats...'));

      $page = Dom::file_get_html(I18N_STATS);

      foreach ($page->find('table#topList tr') as $row) {
        if (isset($row->children[1]->children[0]->attr) && isset($row->children[3])) {
          $link = explode('/', trim(strip_tags($row->children[1]->children[0]->attr['href']), '/'));

          $i18n[] = array('date'        => $boundaries['end'],
                          'identifier'  => end($link),
                          'value'       => trim(str_replace('%', null, strip_tags($row->children[3]->innertext))));
        }
      }

      // remove header
      array_shift($i18n);

      // insert into database
      Db::insert('digest_stats_i18n', $i18n, true);
    }


    // get bug stats
    if (BUG_STATS) {
      Ui::displayMsg(_('Getting bug stats...'));

      $page   = Dom::file_get_html(BUG_STATS);
      $table  = $page->find('table', 1);

      // get known bugfixers
      $knownBugfixers = Db::reindex(Db::load('bugfixers', false), 'email');

      foreach ($table->find('tr') as $row) {
        if (isset($row->children[0]) && isset($row->children[1])) {
          // try and match identifier with known bugfixers
          $identifier = trim(strip_tags($row->children[0]->innertext));

          if (!empty($knownBugfixers[$identifier]['account'])) {
            $identifier = $knownBugfixers[$identifier]['account'];
          }

          $bugs[] = array('date'        => $boundaries['end'],
                          'identifier'  => $identifier,
                          'value'       => trim(strip_tags($row->children[1]->innertext)));
        }
      }

      // remove header
      array_shift($bugs);

      // insert into database
      Db::insert('digest_stats_bugfixers', $bugs, true);


      // extract overall numbers
      preg_match_all('/ [0-9]+ /', $page->find('div#main h3', 0)->innertext, $totalBugs);
      preg_match_all('/[0-9]+ /', $page->find('div#main h4', 0)->innertext, $weekBugs);

      $totalBugs  = reset($totalBugs);
      $weekBugs   = reset($weekBugs);
    }


    // get general stats
    $stats['general']['date']               = $boundaries['end'];
    $stats['general']['revision_start']     = $revision['start'];
    $stats['general']['revision_end']       = $revision['end'];
    $stats['general']['total_commits']      = $totalCommits;
    $stats['general']['total_files']        = $totalFiles;
    $stats['general']['active_developers']  = count($stats['person']);
    $stats['general']['open_bugs']          = $totalBugs[0];
    $stats['general']['open_wishes']        = $totalBugs[1];
    $stats['general']['bugs_opened']        = $weekBugs[0];
    $stats['general']['bugs_closed']        = $weekBugs[1];
    $stats['general']['wishes_opened']      = $weekBugs[3];
    $stats['general']['wishes_closed']      = $weekBugs[4];

    // insert into database
    Db::insert('digest_stats', $stats['general'], true);
    Ui::displayMsg(_('Inserted general stats'));
  }


  public static function getParticipationStats() {
    // set week date boundaries
    $start  = date('Y-m-d', strtotime('Today - 1 week'));
    $end    = date('Y-m-d');


    // get number of reviewed / classified (total)
    $tmp   = Db::sql('SELECT * FROM commits_reviewed', true);

    foreach ($tmp as $item) {
      // reviewed
      if (!empty($item['reviewer'])) {
        // initialise values
        if (!isset($stats[$item['reviewer']])) {
          $stats[$item['reviewer']]['reviewed']['total']    = 0;
          $stats[$item['reviewer']]['reviewed']['week']     = 0;
          $stats[$item['reviewer']]['classified']['total']  = 0;
          $stats[$item['reviewer']]['classified']['week']   = 0;
        }

        // increment
        if (!isset($stats[$item['reviewer']]['reviewed']['total'])) {
          $stats[$item['reviewer']]['reviewed']['total'] = 1;
        } else {
          ++$stats[$item['reviewer']]['reviewed']['total'];
        }
      }


      // classified
      if (!empty($item['classifier'])) {
        // initialise values
        if (!isset($stats[$item['classifier']])) {
          $stats[$item['classifier']]['reviewed']['total']    = 0;
          $stats[$item['classifier']]['reviewed']['week']     = 0;
          $stats[$item['classifier']]['classified']['total']  = 0;
          $stats[$item['classifier']]['classified']['week']   = 0;
        }

        // increment
        if (!isset($stats[$item['classifier']]['classified']['total'])) {
          $stats[$item['classifier']]['classified']['total'] = 1;
        } else {
          ++$stats[$item['classifier']]['classified']['total'];
        }
      }
    }


    // get number of reviewed (week)
    $tmp   = Db::sql('SELECT * FROM commits_reviewed
                      WHERE reviewed > "' . $start . '"
                      AND reviewed <= "' . $end . '"', true);

    foreach ($tmp as $item) {
      // reviewed
      if (!isset($stats[$item['reviewer']]['reviewed']['week'])) {
        $stats[$item['reviewer']]['reviewed']['week'] = 1;
      } else {
        ++$stats[$item['reviewer']]['reviewed']['week'];
      }
    }


    // get number of classified (week)
    $tmp   = Db::sql('SELECT * FROM commits_reviewed
                      WHERE classified IS NOT NULL
                      AND classified > "' . $start . '"
                      AND classified <= "' . $end . '"', true);

    foreach ($tmp as $item) {
      // classified
      if (!isset($stats[$item['classifier']]['classified']['week'])) {
        $stats[$item['classifier']]['classified']['week'] = 1;
      } else {
        ++$stats[$item['classifier']]['classified']['week'];
      }
    }


    return $stats;
  }


  public static function processCommitMsg($revision, $msg) {
    // remove email addresses
    $msg = preg_replace('/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/', null, $msg);


    // extract bugs
    do {
      preg_match('/(BUG|BUGS|CCBUG|FEATURE)[:]?[ ]?[0-9]{4,6}/', $msg, $matches);

      if (isset($matches[0])) {
        // remove bug from msg
        $msg = str_replace($matches[0], null, $msg);

        // extract bug number
        preg_match('/[0-9]{4,6}/', $matches[0], $tmp);

        // set data
        $bug['revision']  = $revision;
        $bug['bug']       = $tmp[0];

        // get extra bug data
        Enzyme::getBugData($bug, true);

        // add bug to database
        Db::insert('commit_bugs', $bug, true);
      }

    } while ($matches);

    return trim($msg);
  }


  public static function getBasePath($tmpPaths, $depth = null) {
    // if only one file provided, return input (otherwise we'll be locked in an infinite loop!)
    if ($tmpPaths instanceof SimpleXMLElement) {
      // SimpleXML data structure (eg. from SVN)
      if (!isset($tmpPaths[1])) {
        return strip_tags($tmpPaths->asXML());

      } else {
        $tmp = $tmpPaths;
        unset($tmpPaths);

        foreach ($tmp as $key => $value) {
          if (!is_array($value)) {
            $tmpPaths[$key] = explode('/', $value);
          }
        }
      }

    } else if (is_array($tmpPaths)) {
      // array
      if (count($tmpPaths) == 1) {
        return array_pop($tmpPaths);

      } else {
        foreach ($tmpPaths as $key => $value) {
          if (!is_array($value)) {
            $tmpPaths[$key] = explode('/', $value);
          }
        }
      }

    } else {
      trigger_error(sprintf(_('Invalid data type in %s'), 'getBasePath'));
      return;
    }


    // process
    $basepath  = null;
    $stop      = false;
    $i         = 1;

    while (!$stop) {
      $last = null;

      foreach ($tmpPaths as $path) {
        if (!$last) {
          // set comparison
          if (isset($path[$i])) {
            $last = $path[$i];
          } else {
            $stop = true;
            break;
          }

        } else {
          // compare paths
          if (!isset($path[$i]) || ($path[$i] != $last)) {
            // stop now
            $stop = true;
            break;
          }
        }
      }

      // add to common string?
      if (!$stop) {
        $basepath .= '/' . $last;
      }

      // stop at specified depth?
      if ($depth && ($i == $depth)) {
        $stop = true;
        break;
      }

      ++$i;
    }

    return $basepath;
  }


  public static function drawBasePath($basepath) {
    if (!empty($basepath)) {
      return $basepath;
    } else {
      return '/';
    }
  }
}

?>