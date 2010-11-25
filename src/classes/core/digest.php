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


  public static function getLastIssueDate($timewarp = null, $getValid = true) {
    // use a specific timewarp value (6 months, etc)?
    if ($timewarp) {
      $timewarp = '-' . $timewarp;
    }

    // get date
    $date = date('Y-m-d', strtotime($timewarp . ' last sunday'));

    // only get a valid date?
    if ($getValid) {
      // load list of issues
      $issues = Cache::loadSave('issue_latest', 'Digest::loadDigests', array('issue', 'latest', false));
      $key    = self::findIssueDate($date, $issues);

      if ($key === false) {
        $key = reset($issues);
        return $key['date'];

      } else {
        return $key;
      }

    } else {
      return $date;
    }
  }


  public static function findIssueDate($date, $issues) {
    $numIssues = count($issues);

    for ($i = 0; $i < $numIssues; $i++) {
      if ($issues[$i]['date'] == $date) {
        return $i;
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


  public static function loadDigests($type, $sort = 'latest', $onlyPublished = true, $limit = null) {
    // determine sorting
    if (($sort == 'earliest') || ($sort == 'ASC')) {
      $sort = ' ASC';
    } else if (($sort == 'latest') || ($sort == 'DESC')) {
      $sort = ' DESC';
    } else {
      return false;
    }

    // limit results?
    if ($limit) {
      $limit = ' LIMIT ' . $limit;
    }

    // only get published?
    if ($onlyPublished) {
      $published = ' AND published = 1 ';
    } else {
      $published = null;;
    }

    // get data
    $q = mysql_query('SELECT * FROM digests
                      WHERE type = "' . $type . '"' .
                      $published .
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
    $q = mysql_query('SELECT number, type, author, intro, body
                      FROM digest_intro_sections
                      WHERE date = \'' . $date . '\'') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

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


    // load digest issue video references
    $q = mysql_query('SELECT number, name, file, youtube
                      FROM digest_intro_videos
                      WHERE date = \'' . $date . '\'') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    while ($row = mysql_fetch_assoc($q)) {
      // get filesize and filetype
      $row['type'] = 'AVI';
      $row['size'] = '2048';

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
      $q = mysql_query('SELECT * FROM authors
                        WHERE account IN ("' . implode('","', $people) . '")') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

      while ($row = mysql_fetch_assoc($q)) {
        $digest['stats']['people'][$row['account']] = $row;
      }
    }


    // load commits
    $q = mysql_query('SELECT * FROM commits
                      LEFT JOIN commits_reviewed
                      ON commits.revision = commits_reviewed.revision
                      LEFT JOIN authors
                      ON commits.author = authors.account
                      WHERE date > \'' . date('Y-m-d', strtotime($date . ' - 7 days')) . '\'
                      AND date <= \'' . $date . '\'
                      AND type IS NOT NULL
                      AND area IS NOT NULL
                      ORDER BY type, area') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    $totalContributions = 0;

    while ($row = mysql_fetch_assoc($q)) {
      if ($row['revision']) {
        $digest['commits'][$row['revision']] = $row;
        $people[] = $row['author'];

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
                        WHERE revision IN (' . implode(',', array_keys($digest['commits'])) . ')
                        ORDER BY operation') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

      while ($row = mysql_fetch_assoc($q)) {
        $digest['commits'][$row['revision']]['diff'][] = $row;
      }


      // load commit bugs
      $q = mysql_query('SELECT * FROM commit_bugs
                        WHERE revision IN (' . implode(',', array_keys($digest['commits'])) . ')') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

      while ($row = mysql_fetch_assoc($q)) {
        $digest['commits'][$row['revision']]['bug'][] = $row;
      }
    }


    return $digest;
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
                 'fr_FR'  => _('Français (French)'),
                 'es_ES'  => _('Español (Spanish)'),
                 'it_IT'  => _('Italiano (Italian)'),
                 'pl_PL'  => _('Polski (Polish)'),
                 'pt_BR'  => _('Português Brasileiro (Brazilian Portuguese)'));

    return array('nl_NL'  => _('Nederlands (Dutch)'),
                 'pt_PT'  => _('Português (Portuguese)'),
                 'sv_SE'  => _('Svenska (Swedish)'));
  }


  public static function drawCommit($commit, $issueDate, $showDiffs = true) {
    // if not showing diffs, use different class to provide correct padding
    if (!$showDiffs) {
      $class = 'b';
    } else {
      $class = 'b b-p';
    }

    $buf = '<div class="commit">
              <span class="intro">' .
                sprintf(_('%s committed changes in %s:'),
                '<a class="n" href="http://cia.vc/stats/author/' . $commit['author'] . '/">' . $commit['name'] . '</a>',
                Enzyme::drawBasePath($commit['basepath'])) .
           '  </span>

              <div class="details">
                <p class="msg">' .
                  Enzyme::formatMsg($commit['msg']) .
           '    </p>';

    // show diff / bug box?
    if ($showDiffs || isset($commit['bug'])) {
      $buf .=  '<div class="info">' .
                  self::drawBugs($commit, $class);

      // show diffs?
      if ($showDiffs) {
        $buf .=  '  <span class="d">' .
                      self::drawDiffs($commit, $issueDate) .
                 '  </span>
                    <a class="r n" href="' . BASE_URL . '/issues/' . $issueDate . '/moreinfo/' . $commit['revision'] . '/">' .
                      sprintf(_('Revision %d'), $commit['revision']) .
                 '  </a>';
      }

      $buf .=  '</div>';
    }

    $buf .=  '  </div>
              </div>';

    return $buf;
  }


  public static function drawBugs($commit, $class = 'b') {
    if (isset($commit['bug'])) {
      $commitDate = strtotime($commit['date']);

      $icon       = '<div>&nbsp;</div>';
      $buf        = '<div class="' . $class . '">';

      foreach ($commit['bug'] as $bug) {
        // work out time to fix (in days)
        $fixTime = floor((($commitDate + 3600) - strtotime($bug['date'])) / 86400);

        $buf .= '<div class="bug">
                   <a class="n" href="' . WEBBUG . $bug['bug'] . '" target="_blank">' . sprintf(_('Bug %d: %s'), $bug['bug'], App::truncate(htmlentities($bug['title']), 90, true)) . '</a>

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

    // create links
    for ($i = 0; $i < $limit; $i++) {
      $buf[] = '<a class="n" href="' . WEBSVN . $commit['diff'][$i]['path'] . '?r1=' . ($commit['diff'][$i]['revision'] - 1) . '&amp;r2=' . $commit['diff'][$i]['revision'] . '">' . ($i + 1) . '</a>';
    }

    // join string
    $str = sprintf(_('Diffs: %s'), implode(', ', $buf));

    if ($numDiffs > self::$numDiffs) {
      $str .= ' <a class="n" href="' . BASE_URL . '/issues/' . $issueDate . '/moreinfo/' . $commit['revision'] . '/#changes">' . sprintf(_('(+ %d more)'), ($numDiffs - self::$numDiffs)) . '</a>';
    }

    return $str;
  }


  public static function getPermissions() {
    $permissions = array('admin'       => array('string' => 'A',
                                                'title'  => _('Admin')),
                         'editor'      => array('string' => 'E',
                                                'title'  => _('Editor')),
                         'reviewer'    => array('string' => 'R',
                                                'title'  => _('Reviewer')),
                         'classifier'  => array('string' => 'C',
                                                'title'  => _('Classifier')),
                         'translator'  => array('string' => 'T',
                                                'title'  => _('Translator')));

    return $permissions;
  }


  public static function getCountries() {
    $countries = Cache::load('countries');

    if (empty($countries)) {
      // get countries from database
      $countries = Db::reindex(Db::load('countries', false), 'code');

      // cache
      Cache::save('countries', $countries);
    }

    return $countries;
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
            $tmpPermissions = preg_split('/[\s,]+/', $item['permissions']);

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


  private static function sortContributors($a, $b) {
    return $a['value'] > $b['value'];
  }
}

?>