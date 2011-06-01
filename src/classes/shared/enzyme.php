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
                        WHERE revision IN ("' . implode('","', array_keys($revisions)) . '")') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

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
                 'koffice'            => _('Office'),
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
                 'optimize'   => _('Optimization'),
                 'security'   => _('Security'),
                 'other'      => _('Other'));

    // add a blank spacer entry at the start?
    if ($spacer) {
      array_unshift($buf, '');
    }

    return $buf;
  }


  public static function getFilterTargets() {
    $buf = array('path'         => _('Path'),
                 'repository'   => _('Repository'));

    return $buf;
  }


  public static function loadSettings($cacheIfEmpty = true, $getKey = false) {
    // load settings (from cache if possible)
    $existingSettings = Cache::load('settings');
    if (!$existingSettings) {
      // load from db and reindex
      $existingSettings = Db::reindex(Db::load('settings', false), 'setting');

      if ($cacheIfEmpty) {
        // cache settings
        Cache::save('settings', $existingSettings);
      }
    }


    // escape and return if no settings found
    if (!$existingSettings) {
      return false;
    }


    // set defaults if unset
    if (empty($existingSettings['HELP_URL']['value'])) {
      $existingSettings['HELP_URL']['value']        = 'https://github.com/dannyakakong/Enzyme/wiki';
      $existingSettings['HELP_CONTAINER']['value']  = 'div#wiki-content';
    }


    // return settings
    if ($getKey) {
      // return specified setting key
      if (isset($existingSettings[$getKey])) {
        return $existingSettings[$getKey];
      } else {
        return false;
      }

    } else {
      // return all settings
      return $existingSettings;
    }
  }


  public static function getAvailableSettings() {
    // define each setting
    $tmp['PROJECT_NAME']        = array('title'   => _('Project Name'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => 'My Project');
    $tmp['ADMIN_EMAIL']         = array('title'   => _('Admin Email'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => 'admin@example.com');
    $tmp['ENZYME_URL']          = array('title'   => _('Enzyme URL'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => 'http://enzyme.commit-digest.org/');
    $tmp['DIGEST_URL']          = array('title'   => _('Digest URL'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => 'http://commit-digest.org/');
    $tmp['HELP_URL']            = array('title'   => _('Help URL'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => 'https://github.com/dannyakakong/Enzyme/wiki');
    $tmp['HELP_CONTAINER']      = array('title'   => _('Help Container Element'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => 'div#wiki-content');
    $tmp['SMTP']                = array('title'   => _('SMTP Mail Server'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => 'smtp.example.com');
    $tmp['SHOW_INSERT']         = array('title'   => _('Show Insert'),
                                        'valid'   => array('0'  => _('No'),
                                                           '1'  => _('Yes')),
                                        'default' => '1',
                                        'example' => null);
    $tmp['DATA_TERMS_VERSION']  = array('title'   => _('Data Terms Version'),
                                        'valid'   => null,
                                        'default' => 0.1,
                                        'example' => null);
    $tmp['SURVEY_ACTIVE']       = array('title'   => _('Survey Active'),
                                        'valid'   => array('0'  => _('No'),
                                                           '1'  => _('Yes')),
                                        'default' => '0',
                                        'example' => null);

    $tmp['GENERATE_MAPS']       = array('title'   => _('Map Generation Service URL'),
                                        'valid'   => null,
                                        'default' => 'http://grafin.enzyme-project.org/index.php',
                                        'example' => 'http://grafin.enzyme-project.org/index.php');
    $tmp['RECENT_COMMITS']      = array('title'   => _('Recent Commits RSS URL'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => 'http://cia.vc/stats/project/KDE/.rss?ver=2&medium=plaintext&limit=10');
    $tmp['WEBBUG']              = array('title'   => _('Web Bug Tracker'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => null);
    $tmp['WEBBUG_XML']          = array('title'   => _('Web Bug Tracker XML'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => null);
    $tmp['I18N_STATS']          = array('title'   => _('I18n Stats URL'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => null);
    $tmp['I18N_TEAMS']          = array('title'   => _('I18n Teams URL'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => null);
    $tmp['BUG_STATS']           = array('title'   => _('Bug Stats URL'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => null);

    $tmp['DEFAULT_TIMEZONE']    = array('title'   => _('Default Timezone'),
                                        'valid'   => array('Europe/London' => 'Europe/London'),
                                        'default' => 'Europe/London',
                                        'example' => null);
    $tmp['DEFAULT_LANGUAGE']    = array('title'   => _('Default Language'),
                                        'valid'   => Digest::getLanguages(),
                                        'default' => 'en_US',
                                        'example' => null);

    $tmp['ENABLE_LEGACY']       = array('title'   => _('Enable Legacy Import'),
                                        'valid'   => array('0'  => _('No'),
                                                           '1'  => _('Yes')),
                                        'default' => '0',
                                        'example' => null);
    $tmp['EXISTING_ISSUES']     = array('title'   => _('Existing Issues'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => null);
    $tmp['EXISTING_ARCHIVE']    = array('title'   => _('Existing Archive'),
                                        'valid'   => null,
                                        'default' => null,
                                        'example' => null);
    $tmp['EXISTING_DATA']       = array('title'   => _('Existing Data'),
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
                                             'HELP_URL'               => $tmp['HELP_URL'],
                                             'HELP_CONTAINER'         => $tmp['HELP_CONTAINER'],
                                             'SMTP'                   => $tmp['SMTP'],
                                             'SHOW_INSERT'            => $tmp['SHOW_INSERT'],
                                             'DATA_TERMS_VERSION'     => $tmp['DATA_TERMS_VERSION'],
                                             'SURVEY_ACTIVE'          => $tmp['SURVEY_ACTIVE']));

    $settings[] = array('title'     => _('Data Locations'),
                        'settings'  => array('GENERATE_MAPS'          => $tmp['GENERATE_MAPS'],
                                             'RECENT_COMMITS'         => $tmp['RECENT_COMMITS'],
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


  public static function getAllJobs() {
    // define available jobs / i18n strings
    $possible['admin']          = array('string'      => _('Admin'),
                                        'title'       => _('Enzyme Administrator'),
                                        'description' => _('Enzyme Administrators can manage all aspects of the system, including various import tools and user accounts.'));

    $possible['editor']         = array('string'      => _('Editor'),
                                        'title'       => _('Editor'),
                                        'description' => _('Editors finalise selected commits, format them for presentation, and write the weekly synopsis.'));

    $possible['feature-editor'] = array('string'      => _('Feature Editor'),
                                        'title'       => _('Feature Editor'),
                                        'description' => _('Feature Editors contact people working on interesting projects and assist them in writing original pieces which are presented in the introduction of each Commit-Digest.'));

    $possible['reviewer']       = array('string'      => _('Reviewer'),
                                        'title'       => _('Commit Reviewer'),
                                        'description' => _('Commit Reviewers look at all the recent commits, selecting those which are significant and interesting enough to be included into the weekly Commit-Digest.'));

    $possible['classifier']     = array('string'      => _('Classifier'),
                                        'title'       => _('Commit Classifier'),
                                        'description' => _('Commit Classifiers sort the selected commits into areas (which is partly automated), and by type (such as bug fix, feature, etc).'));

    $possible['translator']     = array('string'      => _('Translator'),
                                        'title'       => _('Translator'),
                                        'description' => _('Translators increase the reach of the Commit-Digest and the work done across the project by making the weekly Commit-Digests available in the native language of people around the world.'));

    return $possible;
  }


  public static function getAvailableJobs() {
    // get strings of all jobs, and list of available roles
    $possible   = self::getAllJobs();
    $available  = self::getAvailableJobsList();

    // link up available roles with i18n'd strings
    $jobs = array();

    foreach ($possible as $theJob => $null) {
      // iterate through all 'possible' (we could just iterate through all 'available')
      // to keep role hierarchy intact (admin > editor ... translator)
      if (in_array($theJob, $available)) {
        $jobs[$theJob] = $possible[$theJob];
      }
    }

    return $jobs;
  }


  public static function getAvailableJobsList() {
    // load "available jobs" setting
    $available = self::loadSettings(true, 'AVAILABLE_JOBS');

    // split into array
    $availableJobs = App::splitCommaList($available['value']);

    return $availableJobs;
  }


  public static function formatMsg($msg, $htmlLiteral = false) {
    if ($htmlLiteral) {
      $msg = htmlspecialchars($msg, ENT_NOQUOTES, 'UTF-8', false);

      return str_ireplace(array('<br>', "\n"), array('<br />', '<br />'), $msg);

    } else {
      return str_ireplace(array('<br>', "\n", '&'), array('<br />', '<br />', '&amp;'), $msg);
    }
  }


  public static function getProcessedRevisions($type = null, $exclude = null, $classifiedBy = null,
                                               $limit = ' LIMIT 100', $getCount = false) {
    $filter             = ' WHERE 1';
    $revisions          = null;
    $existingRevisions  = null;

    // exclude accounts?
    if ($exclude) {
      if ($exclude === true) {
        // exclude accounts on system exclusion list
        $exclude = self::excludedAccounts();
      }

      $filter .= ' AND commits.developer NOT IN ("' . implode('","', $exclude) . '")';
    }


    // only get revisions reviewed by sepcified username?
    if ($classifiedBy) {
      if (is_array($classifiedBy)) {
        $filter .= ' AND commits_reviewed.reviewer IN ("' . implode('","', $classifiedBy) . '")';
      } else {
        $filter .= ' AND commits_reviewed.reviewer = ' . Db::quote($classifiedBy);
      }
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

      // do join to get commit developers
      $filter .= ' AND commits_reviewed.revision = commits.revision';

    } else {
      $fields   = '*';
      $table    = 'commits';
    }


    // get count instead?
    if ($getCount) {
      $fields = 'COUNT(commits.revision) as count';
      $limit  = null;
    }


    // execute query
    $selectQuery  = 'SELECT ' . $fields . ' FROM ' . $table . $filter . ' ORDER BY date ASC' . $limit;
    $q            = mysql_query($selectQuery) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));


    // get and return data
    if ($getCount) {
      $row = mysql_fetch_assoc($q);

      return $row['count'];

    } else {
      while ($row = mysql_fetch_assoc($q)) {
        $existingRevisions[$row['revision']] = $row;
      }

      return $existingRevisions;
    }


  }


  public static function getProcessedRevisionsList() {
    $existingRevisions = array();

    // search
    $query = 'SELECT revision FROM commits';
    $q     = mysql_query($query) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    // reindex
    while ($row = mysql_fetch_assoc($q)) {
      $existingRevisions[$row['revision']] = $row['revision'];
    }

    return $existingRevisions;
  }


  public static function getDevelopers($revisions = null, $index = 'account') {
    $filter       = null;
    $accountData  = array();

    // filter by developers present in provided revision data?
    if ($revisions) {
      foreach ($revisions as $revision) {
        $developers[$revision['developer']] = $revision['developer'];
      }

      $filter = ' WHERE account IN (\'' . implode('\', \'', $developers) . '\')';
    }


    // load from db
    $q = mysql_query('SELECT * FROM developers' . $filter) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $accountData[$row[$index]] = $row;
    }

    return $accountData;
  }


  public static function getClassifications($indexById = false) {
    $items  = Db::load('commit_path_filters', false, null, '*', false);

    // put into desired data structure
    $classifications  = array();

    foreach ($items as $item) {
      if ($indexById) {
        $classifications[$item['id']]   = $item;
      } else {
        $classifications[$item['matched']] = $item;
      }
    }

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
                      'via svnmerge',
                      'Merge SVN',
                      'Backport r',
                      'forwardport');

    return $ignored;
  }


  public static function getDeveloperInfo($type, $key, $index = 'account') {
    global $developers;

    $string = null;

    // if developers are not available, load
    if (!isset($developers[$index])) {
      $developers[$index] = self::getDevelopers(null, $index);
    }

    // if field is available, return
    if (!empty($developers[$index][$key][$type])) {
      $string = $developers[$index][$key][$type];
    }

    return $string;
  }


  public static function getPeopleInfo($filter = false, $sort = false) {
    // load people, reindex by account
    $people = Db::reindex(Db::load('developers', $filter, null, '*', false), 'account');

    // sort?
    if ($sort) {
      ksort($people);
    }

    return $people;
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


  public static function loadLinks($lowercase = false, $indexBy = 'name') {
    // lowercase key names?
    if ($lowercase) {
      $lowercase = 'strtolower';
    }

    // load from db
    return Db::reindex(Db::load('links', false), $indexBy, $lowercase, false);
  }


  public static function insertRevisions($start = null, $end = null, $showErrors = true) {
    // ensure script doesn't reach execution limits (set to just under an hour)
    set_time_limit(3500);
    ini_set('memory_limit', '256M');

    // show cmd errors?
    if ($showErrors) {
      $showErrors = ' 2>&1';
    } else {
      $showErrors = null;
    }


    // load list of defined repositories
    $repos = Connector::getRepositories();

    foreach ($repos as $repo) {
      // don't process if repository is not enabled
      if (empty($repo['enabled']) || ($repo['enabled'] == 'N')) {
        continue;
      }

      // setup repo
      if ($repo['type'] == 'svn') {
        $repository = new Svn($repo);
        $repository->setupInsertRevisions($start, $end, $showErrors);

      } else if ($repo['type'] == 'imap') {
        $repository = new Imap($repo);
        $repository->setupInsertRevisions();
      }

      // display name (type) of repository insert
      Ui::displayMsg(sprintf(_('Retrieving from %s (%s)...'), $repo['id'], $repo['type']), 'msg_title');

      // do insert
      $repository->insertRevisions();
    }


    // display summary
    if (isset($repository)) {
      echo Ui::processSummary($repository->summary, true);
    }

    // clear bugs list cache
    Cache::delete('bugs');
  }


  public static function generateStatsFromSvn($start, $end, $repoId) {
    // ensure script doesn't reach execution limits (set to just under an hour)
    set_time_limit(3500);
    ini_set('memory_limit', '256M');

    // allow start and end to be passed in any order
    if ($start < $end) {
      $boundaries['start']  = $start;
      $boundaries['end']    = $end;

    } else {
      $boundaries['start']  = $end;
      $boundaries['end']    = $start;
    }


    // load repository
    $repo = Connector::getRepository($repoId);


    // get start and end revision numbers
    foreach ($boundaries as $boundary => $value) {
      if (is_numeric($value)) {
        // assume this is a revision number
        $revision[$boundary] = $value;

      } else {
        // assume this is a date
        $cmd    = 'svn log --non-interactive --xml -v -r {' . $value . '} ' . $repo['hostname'];
        $data   = shell_exec(escapeshellcmd($cmd));
        $data   = simplexml_load_string($data);

        $revision[$boundary] = (int)$data->logentry->attributes()->revision;
      }
    }


    // get revision information
    Ui::displayMsg(_('Getting revision data...'));

    $cmd    = 'svn log --non-interactive --xml -v -r ' . $revision['start'] . ':' . $revision['end'] . ' ' .
              $repo['hostname'];
    $data   = shell_exec(escapeshellcmd($cmd));
    $data   = simplexml_load_string(utf8_encode($data));


    // initialise totals
    $stats                      = array();
    $stats['totalFiles']        = 0;
    $stats['totalCommits']      = 0;
    $stats['excludedCommits']   = 0;
    $stats['excludedAccounts']  = self::excludedAccounts();


    // process and store data
    Ui::displayMsg(_('Parsing revision data...'));

    foreach ($data as $entry) {
      ++$stats['totalCommits'];

      // get commit developer, cast to string
      $tmpDeveloper = (string)$entry->author;

      // skip if an excluded account!
      if (in_array($tmpDeveloper, $stats['excludedAccounts'])) {
        ++$stats['excludedCommits'];
        continue;
      }

      // set data into useful data structure
      if (!isset($stats['person'][$tmpDeveloper]['commits'])) {
        // initialise counters
        $stats['person'][$tmpDeveloper]['commits']  = 0;
        $stats['person'][$tmpDeveloper]['files']    = 0;
      }

      // increment commit counter
      ++$stats['person'][$tmpDeveloper]['commits'];

      // increment files counter
      $numFiles             = count($entry->paths->path);
      $stats['totalFiles'] += $numFiles;

      $stats['person'][$tmpDeveloper]['files'] += $numFiles;


      // extract module
      $basepath = self::getBasePath($entry->paths->path, 2);


      // increment module counter
      if (!isset($stats['module'][$basepath])) {
        $stats['module'][$basepath] = 1;
      } else {
        ++$stats['module'][$basepath];
      }
    }


    // process data into extended statistics
    self::processExtendedStats($boundaries['end'], $stats, $revision);
  }


  public static function generateStatsFromDb($start, $end) {
    // ensure script doesn't reach execution limits
    set_time_limit(3500);
    ini_set('memory_limit', '256M');

    // allow start and end to be passed in any order
    if ($start < $end) {
      $boundaries['start']  = $start;
      $boundaries['end']    = $end;

    } else {
      $boundaries['start']  = $end;
      $boundaries['end']    = $start;
    }


    // ensure enough data has been collected to survey provided date period
    $enoughData  = Db::load('commits',
                            array('date' => array('type'  => 'gt',
                                                  'value' => date('Y-m-d H:i:s', strtotime($boundaries['end'] . ' 1 day')))),
                            1,
                            'date',
                            false,
                            'date ASC');

    if (!$enoughData) {
      Ui::displayMsg(sprintf(_('Commit data past %s has not been collected yet.'), $boundaries['end']));
      return false;
    }


    // get revision information (from database)
    Ui::displayMsg(_('Getting revision data...'));

    $data  = Db::load('commits',
                      array('date' => array('type' => 'range',
                                            'args' => array($boundaries['start'], $boundaries['end']))),
                      null,
                      '*',
                      true,
                      'date ASC');


    // sanity check
    if (!$data) {
      echo _('Not enough data to generate statistics.');
      return false;
    }


    // set revision boundaries
    $tmp                = end($data);
    $revision['end']    = $tmp['revision'];

    $tmp                = reset($data);
    $revision['start']  = $tmp['revision'];


    // get all files linked to commits within data range
    $revisionsList      = array();

    foreach ($data as $entry) {
      $revisionsList[]  = $entry['revision'];
    }

    $commitFiles        = Db::reindex(Db::load('commit_files', array('revision' => $revisionsList)),
                                      'revision',
                                      false,
                                      false);


    // initialise totals
    $stats                      = array();
    $stats['totalFiles']        = 0;
    $stats['totalCommits']      = 0;
    $stats['excludedCommits']   = 0;
    $stats['excludedAccounts']  = self::excludedAccounts();


    Ui::displayMsg(_('Parsing revision data...'));

    // process main data
    foreach ($data as $entry) {
      ++$stats['totalCommits'];

      // skip if an excluded account!
      if (in_array($entry['developer'], $stats['excludedAccounts'])) {
        ++$stats['excludedCommits'];
        continue;
      }

      // set data into useful data structure
      if (!isset($stats['person'][$entry['developer']]['commits'])) {
        // initialise counters
        $stats['person'][$entry['developer']]['commits']  = 0;
        $stats['person'][$entry['developer']]['files']    = 0;
      }

      // increment commit counter
      ++$stats['person'][$entry['developer']]['commits'];

      // increment files counter
      $numFiles             = count($commitFiles[$entry['revision']]);
      $stats['totalFiles'] += $numFiles;

      $stats['person'][$entry['developer']]['files'] += $numFiles;


      // extract module
      $basepath = self::getBasePath($entry['basepath'], 2);


      // increment module counter
      if (!isset($stats['module'][$basepath])) {
        $stats['module'][$basepath] = 1;
      } else {
        ++$stats['module'][$basepath];
      }
    }


    // process data into extended statistics
    self::processExtendedStats($boundaries['end'], $stats, $revision);
  }


  public static function deleteStats($date) {
    // set delete filter to provided date
    $filter   = array('date' => $date);

    // perform delete on neccessary tables
    $tables   = array('digest_stats',
                      'digest_stats_bugfixers',
                      'digest_stats_buzz',
                      'digest_stats_developers',
                      'digest_stats_extended',
                      'digest_stats_i18n',
                      'digest_stats_modules');

    foreach ($tables as $table) {
      Db::delete($table, $filter);
    }

    return true;
  }


  public static function processExtendedStats($date, array $stats, array $revision) {
    // process / insert people stats
    foreach ($stats['person'] as $person => $data) {
      $insert[] = array('date'         => $date,
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

      $insert[] = array('date'         => $date,
                        'identifier'   => $module,
                        'value'        => $data);
    }


    // insert into database
    Db::insert('digest_stats_modules', $insert, true);
    Ui::displayMsg(_('Inserted module stats'));
    unset($insert);


    // load people data to derive extended statistics (country, stats, age, etc)
    $peopleData = self::getPeopleInfo(array('account' => array_keys($stats['person'])));


    // get extended statistics
    Ui::displayMsg(_('Extracting extended statistics...'));

    $keys                               = array('gender', 'dob', 'country', 'motivation', 'colour');
    $percentagePerCommit                = 100 / ($stats['totalCommits'] - $stats['excludedCommits']);

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

        $insert[] = array('date'        => $date,
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

          $i18n[] = array('date'        => $date,
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

          $bugs[] = array('date'        => $date,
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
    $stats['general']['date']               = $date;
    $stats['general']['revision_start']     = $revision['start'];
    $stats['general']['revision_end']       = $revision['end'];
    $stats['general']['total_commits']      = $stats['totalCommits'];
    $stats['general']['total_files']        = $stats['totalFiles'];
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


  public static function getParticipationStats($sort = true, $refresh = false) {
    // check for data in cache
    $existingStats = Cache::load('participation-stats');

    if ($existingStats && !$refresh) {
      return $existingStats;
    }


    // set week date boundaries
    $start  = date('Y-m-d H:i:s', strtotime('Today - 1 week'));
    $end    = date('Y-m-d H:i:s');


    // get number of reviewed / classified (total)
    $tmp    = Db::sql('SELECT * FROM commits_reviewed', true);

    foreach ($tmp as $item) {
      // reviewed
      if (!empty($item['reviewer'])) {
        // initialise values
        if (!isset($stats[$item['reviewer']])) {
          $stats[$item['reviewer']]['reviewed']['total']          = 0;
          $stats[$item['reviewer']]['reviewed']['week']           = 0;
          $stats[$item['reviewer']]['selected']['total']          = 0;
          $stats[$item['reviewer']]['selected']['week']           = 0;
          $stats[$item['reviewer']]['selectedPercent']['total']   = 0;
          $stats[$item['reviewer']]['selectedPercent']['week']    = 0;
          $stats[$item['reviewer']]['classified']['total']        = 0;
          $stats[$item['reviewer']]['classified']['week']         = 0;
        }

        // increment
        if (!isset($stats[$item['reviewer']]['reviewed']['total'])) {
          $stats[$item['reviewer']]['reviewed']['total'] = 1;
        } else {
          ++$stats[$item['reviewer']]['reviewed']['total'];
        }

        // also record selections
        if (!empty($item['marked'])) {
          if (!isset($stats[$item['reviewer']]['selected']['total'])) {
            $stats[$item['reviewer']]['selected']['total'] = 1;
          } else {
            ++$stats[$item['reviewer']]['selected']['total'];
          }
        }
      }


      // classified
      if (!empty($item['classifier'])) {
        // initialise values
        if (!isset($stats[$item['classifier']])) {
          $stats[$item['classifier']]['reviewed']['total']          = 0;
          $stats[$item['classifier']]['reviewed']['week']           = 0;
          $stats[$item['classifier']]['selected']['total']          = 0;
          $stats[$item['classifier']]['selected']['week']           = 0;
          $stats[$item['classifier']]['selectedPercent']['total']   = 0;
          $stats[$item['classifier']]['selectedPercent']['week']    = 0;
          $stats[$item['classifier']]['classified']['total']        = 0;
          $stats[$item['classifier']]['classified']['week']         = 0;
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

    if ($tmp) {
      foreach ($tmp as $item) {
        // reviewed
        if (!isset($stats[$item['reviewer']]['reviewed']['week'])) {
          $stats[$item['reviewer']]['reviewed']['week'] = 1;
        } else {
          ++$stats[$item['reviewer']]['reviewed']['week'];
        }
      }


      // get number of selected (week)
      $tmp   = Db::sql('SELECT * FROM commits_reviewed
                        WHERE marked = 1
                        AND reviewed > "' . $start . '"
                        AND reviewed <= "' . $end . '"', true);

      foreach ($tmp as $item) {
        // selected
        if (!isset($stats[$item['reviewer']]['selected']['week'])) {
          $stats[$item['reviewer']]['selected']['week'] = 1;
        } else {
          ++$stats[$item['reviewer']]['selected']['week'];
        }
      }


      // calculate selected percentages
      foreach ($stats as &$item) {
        if ($item['reviewed']['week']) {
          $item['selectedPercent']['week']  = (($item['selected']['week'] / $item['reviewed']['week']) * 100);
        }

        if ($item['reviewed']['total']) {
          $item['selectedPercent']['total'] = (($item['selected']['total'] / $item['reviewed']['total']) * 100);
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

        } else if (is_array($stats[$item['classifier']]['classified'])) {
          ++$stats[$item['classifier']]['classified']['week'];
        }
      }


      // sort?
      if ($sort && $stats) {
        uasort($stats, array('Enzyme', 'sortParticipationStats'));
      }


      // save data in cache
      Cache::save('participation-stats', $stats, false, 3600);

    } else {
      // no stats found
      $stats = array();
    }


    return $stats;
  }


  public static function sortParticipationStats($a, $b) {
    if (isset($a['classified']['week']) && isset($a['classified']['week'])) {
      // use both reviewed and classified weekly values
      // (weight classify 3 times greater than review)
      return ($a['reviewed']['week'] + ($a['classified']['week'] * 3)) < ($b['reviewed']['week'] + ($b['classified']['week'] * 3));

    } else {
      // only use reviewed values
      return $a['reviewed']['week'] < $b['reviewed']['week'];
    }
  }


  public static function processCommitMsg($revision, $msg) {
    // remove email addresses
    $msg = preg_replace('/[ ]?[<]?(CCMAIL)?[: ]*[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}[>]?/', null, $msg);

    // extract bugs
    do {
      preg_match('/(BUG|BUGS|CCBUG|FEATURE)[:]?[=]?[ ]?[#]?[0-9]{4,6}/', $msg, $matches);

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
    $pathSeparator = '/';

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
            $tmpPaths[$key] = explode($pathSeparator, $value);
          }
        }
      }

    } else if (is_array($tmpPaths)) {
      // array
      if (!$tmpPaths) {
        // empty
        return '';

      } else if (count($tmpPaths) == 1) {
        // return first element
        return array_pop($tmpPaths);

      } else {
        foreach ($tmpPaths as $key => $value) {
          if (!is_array($value)) {
            $tmpPaths[$key] = explode($pathSeparator, $value);
          }
        }
      }

    } else if (is_string($tmpPaths) && $depth) {
      // only a single path represented by a string, and needs to be stripped down
      $tmpPaths = explode($pathSeparator, trim($tmpPaths, $pathSeparator));

      return $pathSeparator . implode($pathSeparator, array_slice($tmpPaths, 0, $depth));

    } else {
      trigger_error(sprintf(_('Invalid data type in %s'), 'getBasePath'));
      return;
    }


    // process
    $basepath  = '';
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
        $basepath .= $pathSeparator . $last;
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


  public static function getDotBlurb($date) {
    // load digest
    $digest = Digest::loadDigest($_REQUEST['date']);

    // split synopsis into bullet list
    if (!empty($digest['synopsis'])) {
      $digest['synopsis'] = preg_split('/\.\s+/', $digest['synopsis']);

      // combine blurb text
      $buf = 'In <a href="' . DIGEST_URL . '/issues/' . $date . '/">this week\'s ' . PROJECT_NAME . '</a>:

              <ul>';

      foreach ($digest['synopsis'] as $item) {
        $buf  .= '<li>' . $item . '</li>' . "\n";
      }

      $buf  .= '</ul>

                <a href="' . DIGEST_URL . '/issues/' . $date . '/">Read the rest of the Digest here</a>.';

      return $buf;

    } else {
      // no digest / synopsis found
      return false;
    }
  }


  public static function getFreeFeatureArticleNum($date) {
    $features = Db::load('digest_intro_sections', array('date' => $date), 1, 'number', true, 'number DESC');

    if (!empty($features['number'])) {
      return $features['number'] + 1;

    } else {
      return 1;
    }
  }


  public static function displayRevision($type, $id, $data, &$developers, &$user = null, &$classifications = null) {
    // show date and buttons?
    if ($type == 'review') {
      $date = '<div class="date">' .
                 $data['date'] .
              '</div>
               <div class="buttons">
                 <div class="yes" onclick="actionSelect(event);">&nbsp;</div>
                 <div class="no" onclick="actionNext(event);">&nbsp;</div>
               </div>';
    } else {
      $date = null;
    }


    // set path
    $data['basepath'] = Enzyme::drawBasePath($data['basepath']);


    // show bugs (as icons) if available
    if (isset($data['bug'])) {
      if ($type == 'review') {
        $bugs = Digest::drawBugs($data, 'bugs');

      } else if ($type == 'classify') {
        $bugs = '<div class="bugs">';

        foreach ($data['bug'] as $bug) {
          $bugs  .= '<div onclick="window.open(\'' . WEBBUG . $bug['bug'] . '\');" title="' . sprintf(_('Bug %d: %s'), $bug['bug'], App::truncate(htmlentities($bug['title']), 90, true)) . '">
                       &nbsp;
                     </div>';
        }

        $bugs  .= '</div>';
      }

    } else {
      $bugs = null;
    }


    // set item class
    if ($user && ($user->data['interface'] == 'mouse')) {
      $itemClass = 'mouse';
    } else {
      $itemClass = 'keyboard';
    }


    // show repository name? (for Git commits)
    $repository = null;

    if (!empty($data['format']) && ($data['format'] == 'git')) {
      // Git
      if (!empty($data['repository'])) {
        $repository = self::formatRepositoryName($data['repository']);
      }

      $revisionLink  = '<i id="r::' . $data['revision'] . '" class="revision">' .
                          Digest::getShortGitRevision($data['revision']) .
                       '</i>';

    } else {
      // SVN
      $revisionLink  = '<a id="r::' . $data['revision'] . '" class="revision" href="' . WEBSVN . '?view=revision&amp;revision=' . $data['revision'] . '" target="_blank" tabindex="0">' .
                          $data['revision'] .
                       '</a>';
    }


    // draw commit
    $buf = '<div id="' . $id . '" class="item normal ' . $type . ' ' . $itemClass . '">
              <div class="commit-title">' .
                sprintf(_('Commit %s by %s (%s)'),
                  $revisionLink,
                  '<span>' . Enzyme::getDeveloperInfo('name', $data['developer']) . '</span>',
                  '<span>' . $data['developer'] . '</span>') .
           '    <br />' .
                $repository . Enzyme::drawBasePath($data['basepath']) .
                $date .
           '  </div>
              <div class="commit-msg">
                <span>' .
                  Enzyme::formatMsg($data['msg'], true) .
           '    </span>' .
                $bugs .
           '  </div>';


    // add classification input fields?
    if ($type == 'classify') {
      // search for basepath in common area classifications, so we can prefill value
      if ($classifications) {
        foreach ($classifications as $filter) {
          if ((($filter['target'] == 'path') && (strpos($data['basepath'], $filter['matched']) !== false)) ||
              (($filter['target'] == 'repository') && (strpos($data['repository'], $filter['matched']) !== false))) {

            $data['area'] = $filter['area'];

            break;
          }
        }
      }

      // show values as blank if set as 0
      if ($data['area'] == 0) {
        $data['area'] = null;
      }
      if ($data['type'] == 0) {
        $data['type'] = null;
      }


      // show remove button? (if user is admin, or reviewed this commit)
      if ($user && ($user->hasPermission(array('editor')) || ($data['reviewer'] == $user->data['username']))) {
        $removeButton  = '<div onclick="removeCommit(' . Digest::quoteRevision($data['revision']) . ', callbackRemoveCommit);" title="' . _('Unselect this commit?') . '" class="remove">
                            &nbsp;
                          </div>';
      } else {
        $removeButton  = null;
      }


      // use mouse-oriented or keyboard-oriented interface?
      if ($user && ($user->data['interface'] == 'mouse')) {
        // mouse
        $areas = array_values(Enzyme::getAreas(true));
        $types = array_values(Enzyme::getTypes(true));

        $buf  .= '<div class="commit-panel">
                    <div class="commit-blame' . (($data['reviewer'] == $user->data['username']) ? ' me' : '') . '">' .
                      sprintf(_('Reviewed by %s'), $data['reviewer']) .
                 '  </div>' .

                    $removeButton .

                 '  <div class="commit-classify mouse">
                      <div>
                        <label>Area</label>' .
                        Ui::htmlSelector($id . '-area', $areas, $data['area'], 'setCurrentItem(\'' . $id . '\');') .
                 '    </div>
                      <div>
                        <label>Type</label>' .
                        Ui::htmlSelector($id . '-type', $types, $data['type'], 'setCurrentItem(\'' . $id . '\');') .
                 '    </div>
                    </div>
                  </div>';

      } else {
        // keyboard
        $buf  .= '<div class="commit-classify keyboard">
                    <label>' .
                      _('Area') . ' <input id="' . $id . '-area" type="text" onblur="setCurrentItem(\'' . $id . '\');" onfocus="scrollItem(\'' . $id . '\');" value="' . $data['area'] . '" />
                  </label>
                    <label>' .
                      _('Type') . ' <input id="' . $id . '-type" type="text" onblur="setCurrentItem(\'' . $id . '\');" onfocus="scrollItem(\'' . $id . '\');" value="' . $data['type'] . '" />
                  </label>
                </div>';
    }
    }

    $buf .=  '</div>';

    return $buf;
  }


  public static function statusArea($type, $user = null) {
    // determine interface elements
    if ($type == 'classify') {
      // get total number of commits available to classify
      $total   = Enzyme::getProcessedRevisions('marked', null, null, null, true);

      $display = sprintf(_('%s commits classified (%s displayed, %s total)'),
                         '<span id="commit-counter">0</span>',
                         '<span id="commit-displayed">0</span>',
                         '<span id="commit-total">' . $total . '</span>');

      // interface selector
      $interface = array('mouse'    => _('Mouse'),
                         'keyboard' => _('Keyboard'));

      $interfaceSelector = '<div id="interface-selector">';

      foreach ($interface as $key => $value) {
        if ($user && ($user->data['interface'] == $key)) {
          $selected = ' checked="checked"';
        } else {
          $selected = null;
        }

        $interfaceSelector  .= '<label title="' . $value . '" class="' . $key . '">
                                  <input id="interface-' . $key . '" name="interface" value="' . $key . '" type="radio" onclick="changeInterface(\'' . $key . '\');"' . $selected . ' /> <i>&nbsp;</i>
                                </label>';
      }


      // allow users to only see commits they have reviewed
      if (isset($user->data['classify_user_filter']) && ($user->data['classify_user_filter'] == 'Y')) {
        $userFilterChecked = ' checked="checked"';
      } else {
        $userFilterChecked = null;
      }

      $interfaceSelector  .= '  <label id="classify-user-filter" title="' . _('Only show commits I reviewed') . '">
                                  <input type="checkbox" onchange="setClassifyUserFilter(event);"' . $userFilterChecked . ' /> <i>&nbsp;</i>
                                </label>
                              </div>';


      // buttons
      $buttons = '<input id="review-save" type="button" onclick="save(\'' . $type . '\', this);" value="' . _('Save') . '" title="' . _('Save') . '" />
                  <input id="review-cancel" class="cancel" type="button" onclick="if (confirm(strings.confirm_dataloss)) { location.reload(true); } return false;" value="' . _('Cancel') . '" title="' . _('Cancel') . '" />';


    } else if ($type == 'review') {
      // get total number of commits available to review
      $total   = Enzyme::getProcessedRevisions('unreviewed', true, null, null, true);

      $display = sprintf('<span class="bold">' . _('Selected %s of %s commits reviewed (%s displayed, %s total)'),
                         '<span id="commit-selected">0</span></span>',
                         '<span id="commit-counter">0</span>',
                         '<span id="commit-displayed">0</span>',
                         '<span id="commit-total">' . $total . '</span>');

      $interfaceSelector = null;
      $buttons = '<input id="review-save" type="button" disabled="disabled" onclick="save(\'' . $type . '\', this);" value="' . _('Save') . '" title="' . _('Save') . '" />
                  <input id="review-cancel" class="cancel" type="button" onclick="if (confirm(strings.confirm_dataloss)) { location.reload(true); } return false;" value="' . _('Cancel') . '" title="' . _('Cancel') . '" />';
    }


    // draw
    $buf = '<div id="status-area">
              <div id="status-area-text">' .
                $display .
                '<input type="button" style="visibility:hidden;" />
              </div>' .
              $interfaceSelector .
           '  <div id="status-area-actions">
                <div id="status-area-info" style="display:none;">&nbsp;</div>
                <img id="status-area-spinner" style="display:none;" src="' . BASE_URL . '/img/spinner-dark-small.gif" alt="" />' .
                $buttons .
             '</div>
            </div>';

    return $buf;
  }


  public static function formatRepositoryName($repositoryName) {
    return '[' . $repositoryName . '] ';
  }
}

?>