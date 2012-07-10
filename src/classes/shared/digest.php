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


class Digest {
  private static $numDiffs = 10;


  public static function replacePeopleReferences(&$digest, $text) {
    if (!isset($digest['people'])) {
      return $text;
    }

    foreach ($digest['people'] as $person) {
      $pattern = array('[person' . $person['number'] . ']',
                       '[person' . $person['number'] . '_short]');
      $replace = array($person['name'],
                       $person['short_name']);

      $text = str_replace($pattern, $replace, $text);
    }

    return $text;
  }


  public static function setPublishedState($date, $newState) {
    return Db::save('digests', array('date' => $date), array('published' => $newState));
  }


  public static function getLastIssueDate($timewarp = null, $getValid = true, $onlyPublished = false, $accurate = false) {
    // use a specific timewarp value (6 months, etc)?
    if ($timewarp) {
      $timewarp = '-' . $timewarp;
    }

    // get date
    $date = date('Y-m-d', strtotime($timewarp . ' last sunday'));

    // only get a valid date?
    if ($getValid) {
      // load list of issues
      if (!$onlyPublished) {
        $issues = Cache::loadSave(array('base'  => DIGEST_APP_ID,
                                        'id'    => 'issue_latest_unpublished'),
                                  'Digest::loadDigests',
                                  array('issue',
                                        'latest',
                                        false));
      } else {
        // only get published
        $issues = Cache::loadSave(array('base'  => DIGEST_APP_ID,
                                        'id'    => 'issue_latest'),
                                  'Digest::loadDigests',
                                  array('issue',
                                        'latest',
                                        true));
      }

      $key = self::findIssueDate($date, $issues, $accurate);

      if ($key === false) {
        $key = reset($issues);
        return $key['date'];

      } else {
        return $issues[$key]['date'];
      }

    } else {
      return $date;
    }
  }


  public static function findIssueDate($date, $issues, $keepLooking = false) {
    $numIssues = count($issues);

    for ($i = 0; $i < $numIssues; $i++) {
      if ($issues[$i]['date'] == $date) {
        return $i;
      }
    }

    // if asked to keep looking, iterate back through issues, looking for closest date to request
    if ($keepLooking) {
      $dateCompare = intval(str_replace('-', null, $date));

      foreach ($issues as $key => $issue) {
        if (intval(str_replace('-', null, $issue['date'])) <= $dateCompare) {
          return $key;
        }
      }
    }

    return false;
  }


  public static function formatName($name, $account = null) {
    return $name['firstname'] . ' ' . $name['lastname'];
  }


  public static function getAuthorDetails($author) {
    $q = mysql_query('SELECT * FROM users
                      WHERE username = \'' . Db::sanitise($author) . '\'
                      LIMIT 1') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    return mysql_fetch_assoc($q);
  }


  public static function loadDigests($type, $sort = 'latest', $onlyPublished = true, $limit = null, $filter = null) {
    $table = 'digests';

    // determine sorting
    if (($sort == 'earliest') || ($sort == 'ASC')) {
      $sort = ' ASC';
    } else if (($sort == 'latest') || ($sort == 'DESC')) {
      $sort = ' DESC';
    } else {
      return false;
    }

    // use specified filter?
    if ($filter) {
      $filter = ' AND ' . Db::createFilter($table, $filter) . ' ';

    } else {
      // only get published?
      if ($onlyPublished) {
        $filter = ' AND published = 1 ';
      } else {
        $filter = null;
      }
    }

    // limit results?
    if ($limit) {
      $limit = ' LIMIT ' . intval($limit);
    }

    // get data
    $q = mysql_query('SELECT * FROM ' . $table . ' ' .
                     'WHERE type = "' . $type . '"' .
                      $filter .
                     'ORDER BY date' . $sort . $limit) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    $digests = array();

    while ($row = mysql_fetch_assoc($q)) {
      $digests[] = $row;
    }

    return $digests;
  }


  public static function loadDigest($date, &$digest = null) {
    // load synopsis
    $q   = mysql_query('SELECT * FROM digests
                        WHERE date = \'' . $date . '\'') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    $row = mysql_fetch_assoc($q);

    if (!$row) {
      return null;

    } else {
      // set issue data (we do this to preserve other data in passed in $digest)
      $digest['id']         = $row['id'];
      $digest['date']       = $row['date'];
      $digest['type']       = $row['type'];
      $digest['version']    = $row['version'];
      $digest['language']   = $row['language'];
      $digest['author']     = $row['author'];
      $digest['synopsis']   = $row['synopsis'];
      $digest['published']  = $row['published'];
      $digest['comments']   = $row['comments'];
    }


    // load digest issue sections
    $q = mysql_query('SELECT number, type, status, author, intro, body
                      FROM digest_intro_sections
                      WHERE date = \'' . $date . '\'
                      AND status = \'selected\'') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $digest['sections'][$row['number']] = $row;

      // record section authors to track contributors to this issue
      if ($row['type'] != 'comment') {
        if (!isset($digest['contributors'][$row['author']])) {
          $digest['contributors'][$row['author']] = array('type'   => 'editor',
                                                          'name'   => $row['author'],
                                                          'value'  => 1);
        } else {
          // increment value
          ++$digest['contributors'][$row['author']]['value'];
        }
      }
    }


    // numerically index contributors array and order by number of contributions
    if (isset($digest['contributors'])) {
      usort($digest['contributors'], array('Digest', 'sortContributors'));
    }


    // load digest issue people references
    $q = mysql_query('SELECT number, name, account
                      FROM digest_intro_people
                      WHERE date = \'' . $date . '\'') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      // derive short name
      $tmp = explode(' ', $row['name']);
      $row['short_name'] = array_shift($tmp);

      $digest['people'][$row['number']] = $row;
    }


      // load digest issue image references
    $q = mysql_query('SELECT type, number, name, file, thumbnail
                      FROM digest_intro_media
                      WHERE type = \'image\'
                      AND date = \'' . $date . '\'') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $digest['image'][$row['number']] = $row;
    }


    // load digest issue video references
    $q = mysql_query('SELECT type, number, name, file, youtube
                      FROM digest_intro_media
                      WHERE type = \'video\'
                      AND date = \'' . $date . '\'') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $digest['video'][$row['number']] = $row;
    }


    // load general stats
    $q = mysql_query('SELECT * FROM digest_stats
                      WHERE date = \'' . $date . '\'
                      LIMIT 1') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $digest['stats']['general'] = $row;
    }


    // load module stats
    $q = mysql_query('SELECT * FROM digest_stats_modules
                      WHERE date = \'' . $date . '\'
                      ORDER BY value DESC
                      LIMIT 0, 10') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $digest['stats']['module'][$row['identifier']] = $row['value'];
    }


    // load developer stats
    $q = mysql_query('SELECT * FROM digest_stats_developers
                      WHERE date = \'' . $date . '\'
                      ORDER BY num_commits DESC
                      LIMIT 0, 10') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $digest['stats']['developer'][$row['identifier']] = $row;

      // record person so we can get name, etc later
      $people[$row['identifier']] = $row['identifier'];
    }


    // load i18n stats
    $q = mysql_query('SELECT * FROM digest_stats_i18n
                      LEFT JOIN languages
                      ON digest_stats_i18n.identifier = languages.code
                      WHERE date = \'' . $date . '\'
                      ORDER BY value DESC
                      LIMIT 0, 10') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $digest['stats']['i18n'][$row['identifier']] = $row;
    }


    // load bugfixers
    $q = mysql_query('SELECT * FROM digest_stats_bugfixers
                      LEFT JOIN bugfixers
                      ON digest_stats_bugfixers.identifier = bugfixers.email
                      WHERE date = \'' . $date . '\'
                      ORDER BY value DESC
                      LIMIT 0, 10') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      // set account from joined bugfixers table if possible
      if (!empty($row['account'])) {
        $digest['stats']['bugfixers'][$row['account']] = $row['value'];

        // record person so we can get name, etc later
        $people[$row['account']] = $row['account'];

      } else {
        // use name if available, else use email account
        if (!empty($row['name'])) {
          $identifier = $row['name'];

        } else {
          // could be name or account
          $identifier = $row['identifier'];

          // record person so we can get name, etc later
          $people[$row['identifier']] = $row['identifier'];
        }

        $digest['stats']['bugfixers'][$identifier] = $row['value'];
      }
    }


    // load buzz
    $q = mysql_query('SELECT * FROM digest_stats_buzz
                      WHERE date = \'' . $date . '\'
                      ORDER BY value DESC') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $digest['stats']['buzz'][$row['type']][] = $row;

      // record person so we can get name, etc later
      if ($row['type'] == 'person') {
        $people[$row['identifier']] = $row['identifier'];
      }
    }


    // load extended
    $q = mysql_query('SELECT * FROM digest_stats_extended
                      WHERE date = \'' . $date . '\'
                      ORDER BY value DESC') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      $digest['stats']['extended'][$row['type']][$row['identifier']] = $row['value'];
    }


    // load people?
    if (isset($people)) {
      $q = mysql_query('SELECT * FROM developers
                        WHERE account IN ("' . implode('","', $people) . '")') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

      while ($row = mysql_fetch_assoc($q)) {
        $digest['stats']['people'][$row['account']] = $row;
      }
    }


    // load commits
    $q = mysql_query('SELECT * FROM commits
                      LEFT JOIN commits_reviewed
                      ON commits.revision = commits_reviewed.revision
                      LEFT JOIN developers
                      ON commits.developer = developers.account
                      WHERE date > \'' . date('Y-m-d', strtotime($date . ' - 7 days')) . '\'
                      AND date <= \'' . $date . '\'
                      AND type IS NOT NULL
                      AND area IS NOT NULL
                      ORDER BY type, area') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    $totalContributions = 0;

    while ($row = mysql_fetch_assoc($q)) {
      if ($row['revision']) {
        $digest['commits'][$row['revision']] = $row;
        $people[] = $row['developer'];

        // record reviewer and classifier so we can calculate contributors to this issue
        if (!isset($contributors[$row['reviewer']])) {
          $contributors[$row['reviewer']] = 0;
        }
        if (!isset($contributors[$row['classifier']])) {
          $contributors[$row['classifier']] = 0;
        }

        $totalContributions += 2;
        ++$contributors[$row['reviewer']];
        ++$contributors[$row['classifier']];
      }
    }


    // sort contributors to this digest issue by extent of contribution,
    // (feature editors already added to top), exclude reviewers and
    // contributors with minimal contribution (< 1%)
    if (isset($contributors)) {
      arsort($contributors, SORT_NUMERIC);

      foreach ($contributors as $contributor => $value) {
        $percent = round((($value / $totalContributions) * 100), 1);

        if ($percent > 1) {
          $digest['contributors'][] = array('type'   => 'reviewer',
                                            'name'   => $contributor,
                                            'value'  => $percent);
        }
      }
    }


    // load commit files
    if (!empty($digest['commits'])) {
      $q = mysql_query('SELECT * FROM commit_files
                        WHERE revision IN ("' . implode('","', array_keys($digest['commits'])) . '")
                        ORDER BY operation') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

      while ($row = mysql_fetch_assoc($q)) {
        $digest['commits'][$row['revision']]['diff'][] = $row;
      }


      // load commit bugs
      $q = mysql_query('SELECT * FROM commit_bugs
                        WHERE revision IN ("' . implode('","', array_keys($digest['commits'])) . '")') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

      while ($row = mysql_fetch_assoc($q)) {
        $digest['commits'][$row['revision']]['bug'][] = $row;
      }
    }


    return $digest;
  }


  public static function loadDigestFeatures($date = null, $status = null) {
    if ($status) {
      // load specific status
      $filter = array('status' => $status);

    } else {
      // load all that aren't ideas or selected
      $filter = array('status' => array('type'  => '!=',
                                        'value' => array('idea', 'selected')));
    }

    // also filter by date?
    if ($date) {
      $filter['date'] = $date;
    }

    return Db::load('digest_intro_sections', $filter, null, '*', false);
  }


  public static function loadDigestMedia($date = null, $type = null, $order = 'date DESC') {
    if ($type) {
      // load specific media type
      $filter = array('type' => $type);

    } else {
      // load all media
      $filter = false;
    }

    // also filter by date?
    if ($date) {
      $filter['date'] = $date;
    }

    return Db::reindex(Db::load('digest_intro_media', $filter, null, '*', false, $order),
                       'date',
                       false,
                       false);
  }


  public static function getPeopleReferences($date) {
    // load digest issue people references
    $q = mysql_query('SELECT number, name, account
                      FROM digest_intro_people
                      WHERE date = \'' . $date . '\'') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    $digest = array();

    while ($row = mysql_fetch_assoc($q)) {
      // derive short name
      $tmp = explode(' ', $row['name']);
      $row['short_name'] = array_shift($tmp);

      $digest['people'][$row['number']] = $row;
    }

    return $digest;
  }


  public static function getTypes() {
    return array('issue'    => _('Issue'),
                 'archive'  => _('Archive'));
  }


  public static function getLanguages() {
    return array('en_US'  => _('English'),
                 'de_DE'  => _('Deutsch (German)'),
                 'fr_FR'  => _('FranÃ§ais (French)'),
                 'es_ES'  => _('EspaÃ±ol (Spanish)'),
                 'nl_NL'  => _('Nederlands (Dutch)'),
                 'it_IT'  => _('Italiano (Italian)'),
                 'ru_RU'  => _('Ð ÑƒÑ�Ñ�ÐºÐ¸Ð¹ Ñ�Ð·Ñ‹Ðº (Russian)'),
                 'pl_PL'  => _('Polski (Polish)'),
                 'pt_PT'  => _('PortuguÃªs (Portuguese)'),
                 'pt_BR'  => _('PortuguÃªs Brasileiro (Brazilian Portuguese)'),
                 'hu_HU'  => _('Magyar (Hungarian)'),
                 'uk_UA'  => _('Ukrainian (Ukrainian)'),
                 'cs_CZ'  => _('Czech (ÄŒeÅ¡tina)'),
                 'nds'    => _('Low Saxon (Low Saxon)'));

    // not yet ready for inclusion, here for translation purposes
    return array('sv_SE'  => _('Svenska (Swedish)'));
  }


  public static function getStatuses() {
    return array('idea'        => _('1. Idea'),
                 'contacting'  => _('2. Contacting'),
                 'more-info'   => _('3. More information needed'),
                 ''            => '--------',
                 'proofread'   => _('4. Needs proofreading'),
                 'ready'       => _('5. Ready for selection'),
                 'selected'    => _('6. Selected'));
  }


  public static function drawCommit($commit, $issueDate, $showDiffs = true) {
    // if not showing diffs, use different class to provide correct padding
    if (!$showDiffs) {
      $class = 'b';
    } else {
      $class = 'b b-p';
    }

    // draw
    $buf = '<div class="commit">
              <span class="intro">' .
                self::getCommitTitle($commit) .
           '  </span>

              <div class="details">
                <p class="msg">' .
                  Enzyme::formatMsg($commit['msg'], true) .
           '    </p>';

    // show diff / bug box?
    if ($showDiffs || isset($commit['bug'])) {
      $buf .=  '<div class="info">' .
                  self::drawBugs($commit, $class);

      // show diffs?
      if ($showDiffs) {
        // shorten revision string if Git
        if (empty($commit['format']) || ($commit['format'] == 'svn')) {
          $revision = $commit['revision'];

        } else if ($commit['format'] == 'git') {
          $revision = Digest::getShortGitRevision($commit['revision']);
        }

        $buf .=  '  <span class="d">' .
                      self::drawDiffs($commit, $issueDate) .
                 '  </span>
                    <a class="r n" href="' . BASE_URL . '/issues/' . $issueDate . '/moreinfo/' . $commit['revision'] . '/">' .
                      sprintf(_('Revision %s'), $revision) .
                 '  </a>';
      }

      $buf .=  '</div>';
    }

    $buf .=  '  </div>
              </div>';

    return $buf;
  }


  public static function getCommitTitle($commit) {
      if (empty($commit['format']) || ($commit['format'] == 'svn')) {
      $title  = sprintf(_('%s committed changes in %s:'),
                '<a class="n" href="http://cia.vc/stats/author/' . $commit['developer'] . '/" target="_blank">' . $commit['name'] . '</a>',
                Enzyme::drawBasePath($commit['basepath']));

    } else if ($commit['format'] == 'git') {
      // do we have the name of the committer?
      if (!empty($commit['name'])) {
        $committer = '<a class="n" href="http://cia.vc/stats/author/' . $commit['developer'] . '/" target="_blank">' . $commit['name'] . '</a>';
      } else {
        $committer = Ui::displayEmailAddress($commit['developer']);
      }

      // show name of repository?
      if (!empty($commit['repository'])) {
        $repository = Enzyme::formatRepositoryName($commit['repository']);
      } else {
        $repository = null;
      }

      $title  = sprintf(_('%s committed changes in %s:'),
                $committer,
                $repository . Enzyme::drawBasePath($commit['basepath']));
    }

    return $title;
  }


  public static function drawBugs($commit, $class = 'b') {
    if (isset($commit['bug'])) {
      $commitDate = strtotime($commit['date']);

      $icon       = '<div>&nbsp;</div>';
      $buf        = '<div class="' . $class . '">';

      foreach ($commit['bug'] as $bug) {
        // work out time to fix (in days)
        if (!empty($bug['date'])) {
          $fixTime = floor((($commitDate + 3600) - strtotime($bug['date'])) / 86400);

        } else {
          $fixTime = 0;

          // log the error
          Log::error('Invalid date for ' . $bug['bug']);
        }

        $buf .= '<div class="bug">
                   <a class="n" href="' . Enzyme::getSettingUrl(Config::getSetting('data', 'WEBBUG'), $bug['bug']) . '" target="_blank">' . sprintf(_('Bug %d: %s'), $bug['bug'], App::truncate(htmlentities($bug['title']), 90, true)) . '</a>

                   <div>' .
                     $icon .
                '    <span>' . sprintf(_('%d days'), $fixTime) . '</span>
                   </div>
                 </div>';

        // only draw icon once per section
        $icon = null;
      }

      $buf .= '</div>';

      return $buf;
    }
  }


  public static function quoteRevision($revision, $char = "'") {
    if (is_numeric($revision)) {
      return $revision;

    } else {
      return $char . $revision . $char;
    }
  }


  public static function drawDiffs($commit, $issueDate) {
    $buf       = null;
    $str       = null;

    if (!isset($commit['diff'])) {
      return false;
    }

    $numDiffs  = count($commit['diff']);

    // set draw limit
    if ($numDiffs < self::$numDiffs) {
      $limit = $numDiffs;
    } else {
      $limit = self::$numDiffs;
    }

    // draw items (create links?)
    for ($i = 0; $i < $limit; $i++) {
      if (empty($commit['format']) || ($commit['format'] == 'svn')) {
        // show links to the web repo viewer
        $buf[] = '<a class="n" href="' . Config::getSetting('data', 'WEBSVN') . $commit['diff'][$i]['path'] . '?r1=' . ($commit['diff'][$i]['revision'] - 1) . '&amp;r2=' . $commit['diff'][$i]['revision'] . '">' . ($i + 1) . '</a>';

      } else {
        // don't show links, we don't know the web repo viewer for Git links
        $buf[] = '<i title="' . $commit['diff'][$i]['path'] . '">' . ($i + 1) . '</i>';
      }
    }

    // join string
    $str = sprintf(_('Diffs: %s'), implode(', ', $buf));

    if ($numDiffs > self::$numDiffs) {
      $str .= ' <a class="n" href="' . BASE_URL . '/issues/' . $issueDate . '/moreinfo/' . $commit['revision'] . '/#changes">' . sprintf(_('(+ %d more)'), ($numDiffs - self::$numDiffs)) . '</a>';
    }

    return $str;
  }


  public static function getPermissions() {
    $permissions = array('admin'            => array('string' => 'A',
                                                     'title'  => _('Admin')),
                         'editor'           => array('string' => 'E',
                                                     'title'  => _('Editor')),
                         'feature-editor'   => array('string' => 'F',
                                                     'title'  => _('Feature editor')),
                         'reviewer'         => array('string' => 'R',
                                                     'title'  => _('Reviewer')),
                         'classifier'       => array('string' => 'C',
                                                     'title'  => _('Classifier')),
                         'translator'       => array('string' => 'T',
                                                     'title'  => _('Translator')));

    return $permissions;
  }


  public static function getCountries($type = 'full') {
    // return data in requested format
    if ($type == 'basic') {
      // basic
      $countries = Cache::load('countries_basic');

      if (empty($countries)) {
        // get countries from database
        $tmp = Db::load('countries', false, null, 'code, name');

        // reindex
        $countries = array();

        foreach ($tmp as $country) {
          $countries[$country['code']] = htmlspecialchars($country['name']);
        }

        // sort by country name
        asort($countries, SORT_LOCALE_STRING);

        // cache
        Cache::save('countries_basic', $countries);
      }

      return $countries;

    } else {
      // simple / full
      $countries = Cache::load('countries_' . $type);

      if (empty($countries)) {
        // get countries from database
        $countries = Db::reindex(Db::load('countries', false), 'code');

        // do extra processing?
        if ($type == 'simple') {
          // load basic list
          $tmp        = $countries;
          $countries  = self::getCountries('basic');

          // recreate based on basic list (as it will be correctly ordered by country name)
          foreach ($countries as $code => &$value) {
            $value = array('class'  => $tmp[$code]['continent'],
                           'value'  => htmlspecialchars($tmp[$code]['name']));
          }

          unset($tmp);
        }

        // cache
        Cache::save('countries_' . $type, $countries);
      }

      return $countries;
    }
  }


  public static function getUsersByPermission($permission = null, $group = false, $extended = false) {
    $data = Db::load('users', array('username' => true), null, '*', false, 'firstname');

    if (is_array($data)) {
      foreach ($data as $item) {
        if ($permission && (strpos($item['permissions'], $permission) === false)) {
          continue;
        }

        // create name string
        $string = App::getName($item);

        if (!$permission) {
          if ($group) {
            // group by permission
            $tmpPermissions = App::splitCommaList($item['permissions']);

            foreach ($tmpPermissions as $thePermission) {
              if ($extended) {
                $payload = array('string' => $string,
                                 'data'   => $item);
              } else {
                $payload = $string;
              }

              $users[trim($thePermission)][$item['username']] = $payload;
            }

          } else {
            // don't group by permission
            if ($extended) {
                $payload = array('string' => $string,
                                 'data'   => $item);
            } else {
              $payload = $string;
            }

            $users[$item['username']] = $payload;
          }

        } else {
          $users[$item['username']] = $string;
        }
      }

      return $users;
    }
  }


  public static function getNumUsers() {
    return Db::count('users', array('username' => true));
  }


  public static function getShortGitRevision($revision) {
    return App::truncate($revision, 7, true);
  }


  private static function sortContributors($a, $b) {
    return $a['value'] > $b['value'];
  }
}

?>