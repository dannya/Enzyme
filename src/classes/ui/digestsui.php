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


class DigestsUi extends BaseUi {
  public $id      = 'digests';

  private $data   = null;


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Digests');
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'editor')) {
      return $buf;
    }

    if (empty($_REQUEST['digest']) || ($_REQUEST['digest'] == '/digests/')) {
      // load list of existing digests
      $this->data = Digest::loadDigests('issue', 'latest', false);

      // draw menu UI
      return $this->drawMenu();

    } else {
      // load digest data
      $date       = explode('/', rtrim($_REQUEST['digest'], '/'));
      $date       = end($date);

      $this->data = Digest::loadDigest($date);

      // draw issue management UI
      return $this->drawManagement();
    }
  }


  public function getScript() {
    return array('/js/frame/digestsui.js');
  }


  public function getStyle() {
    return array('/css/digestsui.css');
  }


  private function drawMenu() {
    // draw "create a new digest" form
    $buf = '<h3>' .
              _('Create a New Digest') .
           '  <div>
                <div id="indicator-new-digest"><div>&nbsp;</div></div>
                <input type="button" onclick="createNewDigest();" value="' . _('Create new digest') . '" title="' . _('Create new digest') . '" />
              </div>
            </h3>

            <table id="new-digest">
              <tbody>
                <tr>
                  <td class="label">' . _('Date') . '</td>
                  <td class="value">
                    <input id="info-date" type="text" value="' . Digest::getLastIssueDate(null, false) . '" />
                  </td>
                </tr>
                <tr>
                  <td class="label">' . _('Type') . '</td>
                  <td class="value">' .
                    Ui::htmlSelector('info-type', Digest::getTypes()) .
           '      </td>
                </tr>
                <tr>
                  <td class="label">' . _('Language') . '</td>
                  <td class="value">' .
                    Ui::htmlSelector('info-language', Digest::getLanguages()) .
           '      </td>
                </tr>
                <tr>
                  <td class="label">' . _('Editor') . '</td>
                    <td class="value">' .
                      Ui::htmlSelector('info-editor', Digest::getUsersByPermission('editor')) .
           '        </td>
                </tr>
              </tbody>
            </table>';


    // show list of existing digests?
    if ($this->data) {
      // sort by year, split into two columns
      foreach ($this->data as $digest) {
        // is issue published?
        if (empty($digest['published'])) {
          $digest['published'] = 'true';
          $digest['class']     = 'indicator-failure';
          $digest['string']    = sprintf(_('Publish %s...'), $digest['date']);

        } else {
          $digest['published'] = 'false';
          $digest['class']     = 'indicator-success';
          $digest['string']    = sprintf(_('Unpublish %s...'), $digest['date']);
        }

        // unset unneeded elements
        unset($digest['synopsis']);

        // add into date-sorted array
        $digests[substr($digest['date'], 0, 4)][] = $digest;
      }


      // draw
      $buf  .= '<h3>' . _('Manage Digests') . '</h3>
                <p>' . _('Select a digest to manage...') . '</p>';

      foreach ($digests as $year => $theDigests) {
        // start new section
        $buf .=  '<h4>' . $year . '</h4>

                  <div class="digests-container">
                    <ul class="digests">';

        // draw list
        $counter    = 0;
        $numDigests = count($theDigests);

        foreach ($theDigests as $digest) {
          // draw new list
          if (($numDigests > 10) && (++$counter == round(($numDigests / 2) + 1))) {
            $buf .=  '</ul>
                      <ul class="digests digests-right">';
          }

          $buf .=  '<li>
                      <div class="' . $digest['class'] . '" onclick="setPublished(this, \'' . $digest['date'] . '\', ' . $digest['published'] . ');" title="' . $digest['string'] . '">
                        <div>&nbsp;</div>
                      </div>

                      <a href="' . BASE_URL . '/digests/' . $digest['date'] . '/">' .
                        sprintf(_('Issue %d: %s'), $digest['id'], Date::get('full', $digest['date'])) .
                   '  </a>
                    </li>';
        }

        $buf .=  '  </ul>
                  </div>';
      }
    }

    return $buf;
  }


  private function drawManagement() {
    // draw contributors
    $users         = Digest::getUsersByPermission();
    $contributors  = array();

    if (isset($this->data['contributors'])) {
      foreach ($this->data['contributors'] as $contributor => $data) {
        $contributors[] = $users[$data['name']];
      }
    }


    // draw row
    $buf = '<h2>' .
              _('Digest Information') .
           '  <div>
                <div id="indicator-info"><div>&nbsp;</div></div>
                <input id="save-info" type="button" value="' . _('Save changes') . '" title="' . _('Save changes') . '" onclick="saveSection(\'' . $this->data['date'] . '\', \'info\');" />
              </div>
            </h2>

            <table id="info">
              <tbody>
                <tr>
                  <td class="label">' . _('ID') . '</td>
                  <td class="value">
                    <input id="info-id" type="text" value="' . $this->data['id'] . '" />
                  </td>
                </tr>
                <tr>
                  <td class="label">' . _('Date') . '</td>
                  <td class="value">
                    <input id="info-date" type="text" value="' . $this->data['date'] . '" />
                  </td>
                </tr>
                <tr>
                  <td class="label">' . _('Type') . '</td>
                  <td class="value">' .
                    Ui::htmlSelector('info-type', Digest::getTypes(), $this->data['type']) .
           '      </td>
                </tr>
                <tr>
                  <td class="label">' . _('Language') . '</td>
                  <td class="value">' .
                    Ui::htmlSelector('info-language', Digest::getLanguages(), $this->data['language']) .
           '      </td>
                </tr>
                <tr>
                  <td class="label">' . _('Editor') . '</td>
                  <td class="value">' .
                    Ui::htmlSelector('info-editor', Digest::getUsersByPermission('editor'), $this->data['author']) .
           '      </td>
                </tr>
              </tbody>
            </table>

            <table id="contributors">
              <tbody>
                <tr>
                  <td class="label">' . _('Published') . '</td>
                  <td class="value">' .
                    Ui::htmlSelector('info-published', array('0' => _('No'), '1' => _('Yes')), $this->data['published']) .
           '      </td>
                </tr>
                <tr>
                  <td class="label">' . _('Comments URL') . '</td>
                  <td class="value">
                    <input id="info-comments" type="text" value="' . $this->data['comments'] . '" />
                  </td>
                </tr>
                <tr>
                  <td class="label">' . _('Contributors') . '</td>
                  <td class="value">
                    <textarea readonly="readonly" rows="3">' . implode("\n", array_unique($contributors)) . '</textarea>
                  </td>
                </tr>
              </tbody>
            </table>


            <h2>' .
              _('Synopsis') .
           '  <div>
                <div id="indicator-synopsis"><div>&nbsp;</div></div>
                <input id="save-synopsis" type="button" value="' . _('Save changes') . '" title="' . _('Save changes') . '" onclick="saveSection(\'' . $this->data['date'] . '\', \'synopsis\');" />
              </div>
            </h2>

            <textarea id="synopsis">' .
              $this->data['synopsis'] .
           '</textarea>' .

            $this->drawIntro() .

            $this->drawStats() .

            $this->drawCommits();

    return $buf;
  }


  private function drawIntro() {
    $buf = '<h2>' .
              _('Introduction') .
           '  <div>
                <div id="indicator-introduction"><div>&nbsp;</div></div>
                <input id="add-introduction" type="button" value="' . _('Add section') . '" title="' . _('Add section') . '" onclick="addIntroSection();" />
              </div>
            </h2>';

    if (empty($this->data['sections'])) {
      $buf .=  '<p class="prompt">' .
                  _('No introduction sections found') .
                '</p>';

    } else {
      $buf .=  '<div id="sections">';

      foreach ($this->data['sections'] as $section) {
        // set button states
        if ($section['type'] == 'message') {
          $body         = '<textarea id="body-' . $section['number'] . '" class="body" rows="8">' . $section['body'] . '</textarea>';

          $messageClass = 'selected';
          $commentClass = 'unselected';

        } else if ($section['type'] == 'comment') {
          $body         = null;

          $messageClass = 'unselected';
          $commentClass = 'selected';
        }

        // draw
        $buf .=  '<div id="intro-section-' . $section['number'] . '" class="section">
                    <span>' . $section['number'] . '</span>
                    <div id="save-introduction-' . $section['number'] . '" class="save-introduction" title="' . _('Save changes') . '" onclick="saveSection(\'' . $this->data['date'] . '\', \'introduction\', ' . $section['number'] . ');">
                      <div>&nbsp;</div>
                    </div>

                    <div class="section-container">
                      <input id="button-message-' . $section['number'] . '" type="button" value="m" title="' . _('Message') . '" class="' . $messageClass . '" onclick="changeItemType(' . $section['number'] . ', \'message\');" />
                      <input id="button-comment-' . $section['number'] . '" type="button" value="c" title="' . _('Comment') . '" class="' . $commentClass . '" onclick="changeItemType(' . $section['number'] . ', \'comment\');" />

                      <textarea id="intro-' . $section['number'] . '" class="intro-' . $section['type'] . '" rows="1">' .
                        $section['intro'] .
                   '  </textarea>' .
                      $body .
                 '  </div>
                  </div>';
      }

      $buf .=  '</div>';
    }

    return $buf;
  }


  private function drawStats() {
    // draw 'generate' button?
    if (empty($this->data['stats'])) {
      $button =  '<div>
                    <div id="indicator-stats"><div>&nbsp;</div></div>
                    <input id="generate-stats" type="button" value="' . _('Generate statistics') . '" title="' . _('Generate statistics') . '" onclick="generateStats(\'' . $this->data['date'] . '\', \'' . date('Y-m-d', strtotime($this->data['date'] . ' -1 week')) . '\');" />
                  </div>';
    } else {
      $button = null;
    }

    $buf = '<h2>' .
              _('Statistics') .
              $button .
           '</h2>';

    if (empty($this->data['stats'])) {
      // draw generation interface
      $buf .=  '<iframe id="results" src="' . BASE_URL . '/get/prompt.php?language=' . LANGUAGE . '&prompt=stats"></iframe>';

    } else {
      // define general fields to show
      $fields = array('total_commits'     => _('Total Commits'),
                      'total_lines'       => _('Total Lines'),
                      'new_files'         => _('New Files'),
                      'total_files'       => _('Total Files'),
                      'active_developers' => _('Active Developers'),
                      'open_bugs'         => _('Open Bugs'),
                      'open_wishes'       => _('Open Wishes'),
                      'bugs_opened'       => _('Bugs Opened'),
                      'bugs_closed'       => _('Bugs Closed'),
                      'wishes_opened'     => _('Wishes Opened'),
                      'wishes_closed'     => _('Wishes Closed'));

      // version 2 doesn't count some fields, remove them from table
      if ($this->data['version'] == 2) {
        array_splice($fields, 1, 2);
      }

      // sort general stats into two columns
      $counter    = 0;
      $column     = 0;
      $numFields  = count($fields);

      foreach ($fields as $key => $string) {
        if (isset($this->data['stats']['general'][$key])) {
          // go to next column?
          if ($counter == ceil($numFields / 2)) {
            $counter = 0;
            $column  = 1;
          }

          // put into array
          $tmp = array($key => array('value'  => $this->data['stats']['general'][$key],
                                     'string' => $string));

          // add to array
          $theFields[$counter++][$column] = $tmp;
        }
      }


      // draw general stats?
      if (!empty($theFields)) {
        $buf .=  '<h3 class="sub">' . _('General') . '</h3>

                  <table id="stats-general" class="stats">
                    <tbody>';

        foreach ($theFields as $column => $data) {
          $current = reset($data[0]);

          $buf .=  '<tr>
                      <td class="label">' . $current['string'] . '</td>
                      <td class="value">
                        <input id="' . key($data[0]) . '" type="text" value="' . $current['value'] . '" />
                      </td>';

          if (isset($data[1])) {
            $current = reset($data[1]);

            $buf .=  '  <td class="label labelRight">' . $current['string'] . '</td>
                        <td class="value">
                          <input id="' . key($data[1]) . '" type="text" value="' . $current['value'] . '" />
                        </td>
                      </tr>';
          }
        }

        $buf .=  '  </tbody>
                  </table>';
      }


      // draw module stats
      if (!empty($this->data['stats']['module'])) {
        $counter = 0;

        $buf .=  '<h3 class="sub">' . _('Commit Summary') . '</h3>

                  <table id="stats-module" class="stats">
                    <thead>
                      <tr>
                        <td>' . _('Module') . '</td>
                        <td>' . _('Commits') . '</td>
                      </tr>
                    </thead>

                    <tbody>';

        foreach ($this->data['stats']['module'] as $module => $value) {
          $buf .=  '<tr>
                      <td class="label">
                        <input id="module-label-' . $counter . '" type="text" value="' . $module . '" />
                      </td>
                      <td class="value">
                        <input id="module-value-' . $counter . '" type="text" value="' . $value . '" />
                      </td>
                    </tr>';

          ++$counter;
        }

        $buf .=  '  </tbody>
                  </table>';
      }


      // draw developer stats
      if ($this->data['version'] == 2) {
        $title = _('Files');
        $key   = 'num_files';
      } else {
        $title = _('Lines');
        $key   = 'num_lines';
      }

      $counter = 0;

      $buf .=  '<table id="stats-developer" class="stats">
                  <thead>
                    <tr>
                      <td>' . _('Account') . '</td>
                      <td>' . $title . '</td>
                      <td>' . _('Commits') . '</td>
                    </tr>
                  </thead>

                  <tbody>';

      foreach ($this->data['stats']['developer'] as $account => $data) {
        $buf .=  '<tr>
                    <td class="label">
                      <input id="developer-label-' . $counter . '" type="text" value="' . $account . '" />
                    </td>
                    <td class="value valueLeft">
                      <input id="developer-value1-' . $counter . '" type="text" value="' . $data[$key] . '" />
                    </td>
                    <td class="value valueRight">
                      <input id="developer-value2-' . $counter . '" type="text" value="' . $data['num_commits'] . '" />
                    </td>
                  </tr>';

        ++$counter;
      }

      $buf .=  '  </tbody>
                </table>';


      // draw i18n stats
      $counter = 0;

      $buf .=  '<h3>' . _('Internationalisation (i18n) and Bug Killers') . '</h3>

                <table id="stats-i18n" class="stats">
                  <thead>
                    <tr>
                      <td>' . _('Language') . '</td>
                      <td>' . _('Percent (%)') . '</td>
                    </tr>
                  </thead>

                  <tbody>';

      foreach ($this->data['stats']['i18n'] as $language => $data) {
        $buf .=  '<tr>
                    <td class="label">
                      <input id="i18n-label-' . $counter . '" type="text" value="' . $language . '" />
                    </td>
                    <td class="value">
                      <input id="i18n-value-' . $counter . '" type="text" value="' . $data['value'] . '" />
                    </td>
                  </tr>';

        ++$counter;
      }

      $buf .=  '  </tbody>
                </table>';


      // draw bugfixer stats
      $counter = 0;

      $buf .=  '<table id="stats-bugfixers" class="stats">
                  <thead>
                    <tr>
                      <td>' . _('Person') . '</td>
                      <td>' . _('Bugs') . '</td>
                    </tr>
                  </thead>

                  <tbody>';

      foreach ($this->data['stats']['bugfixers'] as $person => $value) {
        $buf .=  '<tr>
                    <td class="label modLabel">
                      <input id="bugfixer-label-' . $counter . '" type="text" value="' . $person . '" />
                    </td>
                    <td class="value modValue">
                      <input id="bugfixer-value-' . $counter . '" type="text" value="' . $value . '" />
                    </td>
                  </tr>';

        ++$counter;
      }

      $buf .=  '  </tbody>
                </table>';


      // draw buzz (program) stats?
      if (!empty($this->data['stats']['buzz'])) {
        $counter = 0;

        $buf .=  '<h3>' . _('Buzz') . '</h3>

                  <table id="stats-buzz-program" class="stats">
                    <thead>
                      <tr>
                        <td>' . _('Program') . '</td>
                        <td>' . _('Buzz') . '</td>
                      </tr>
                    </thead>

                    <tbody>';

        foreach ($this->data['stats']['buzz']['program'] as $data) {
          // stop after showing 10 entries
          if ($counter == 10) {
            break;
          }

          $buf .=  '<tr>
                      <td class="label">
                        <input id="buzz-program-label-' . $counter . '" type="text" value="' . $data['identifier'] . '" />
                      </td>
                      <td class="value">
                        <input id="buzz-program-value-' . $counter . '" type="text" value="' . $data['value'] . '" />
                      </td>
                    </tr>';

          ++$counter;
        }

        $buf .=  '  </tbody>
                  </table>';


        // draw buzz (person) stats
        $counter = 0;

        $buf .=  '<table id="stats-buzz-person" class="stats">
                    <thead>
                      <tr>
                        <td>' . _('Person') . '</td>
                        <td>' . _('Buzz') . '</td>
                      </tr>
                    </thead>

                    <tbody>';

        foreach ($this->data['stats']['buzz']['person'] as $data) {
          // stop after showing 10 entries
          if ($counter == 10) {
            break;
          }

          $buf .=  '<tr>
                      <td class="label">
                        <input id="buzz-person-label-' . $counter . '" type="text" value="' . $data['identifier'] . '" />
                      </td>
                      <td class="value">
                        <input id="buzz-person-value-' . $counter . '" type="text" value="' . $data['value'] . '" />
                      </td>
                    </tr>';

          ++$counter;
        }

        $buf .=  '  </tbody>
                  </table>';
      }
    }

    return $buf;
  }


  private function drawCommits() {
    if (empty($this->data['commits'])) {
      return '<h2>' .
                _('Selected Commits') .
             '  <div>
                  <input type="button" value="' . _('Review commits...') . '" title="' . _('Review commits...') . '" onclick="top.location=\'' . BASE_URL . '/review/\';" />
                  <input type="button" value="' . _('Classify commits...') . '" title="' . _('Classify commits...') . '" onclick="top.location=\'' . BASE_URL . '/classify/\';" />
                </div>
              </h2>

              <p class="prompt">' .
                _('No commits reviewed and classified!') .
             '</p>';
    }

    // initialise areas and types
    $types        = Enzyme::getTypes();
    $areas        = Enzyme::getAreas();

    $numericTypes = array_values(Enzyme::getTypes());
    $numericAreas = array_values(Enzyme::getAreas());

    // shift by 1 to match db storage
    array_unshift($numericTypes, null);
    array_unshift($numericAreas, null);

    $lastType     = null;
    $lastArea     = null;


    // draw
    $buf = null;

    foreach ($this->data['commits'] as $commit) {
      // draw new header (type)?
      if ($commit['type'] != $lastType) {
        $type = array_slice($types, ($commit['type'] - 1), 1);
        $buf .= '<h2>' . reset($type) . '</h2>';

        $lastType = $commit['type'];
      }

      // draw new subheader (area)?
      if ($commit['area'] != $lastArea) {
        $area = array_slice($areas, ($commit['area'] - 1), 1);

        $buf .= '<a id="' . key($type) . '-' . key($area) . '"></a>
                 <h3>' . reset($area) . '</h3>';

        $lastArea = $commit['area'];
      }

      // estimate number of rows to display
      $rows = ceil(strlen($commit['msg']) / 120) + substr_count($commit['msg'], '<br />');

      // draw
      $buf .=  '<div class="commit">
                  <div class="intro">' .
                    sprintf(_('%s committed changes in %s:'),
                    '<a class="n" href="http://cia.vc/stats/author/' . $commit['author'] . '/">' . $commit['name'] . '</a>',
                    Enzyme::drawBasePath($commit['basepath'])) .
               '  </div>
                  <div class="selectors">' .
                    Ui::htmlSelector('type-' . $commit['revision'], $numericTypes, $commit['type'], 'changeValue(\'type\', ' . $commit['revision'] . ');') .
                    Ui::htmlSelector('area-' . $commit['revision'], $numericAreas, $commit['area'], 'changeValue(\'area\', ' . $commit['revision'] . ');') .
               '  </div>

                  <div class="details">
                    <textarea id="msg-' . $commit['revision'] . '" class="msg" rows="' . $rows . '" onchange="changeValue(\'msg\', ' . $commit['revision'] . ');">' .
                      str_replace("<br />", "<br />\n", $commit['msg']) .
               '    </textarea>

                    <div class="info">' .
                      Digest::drawBugs($commit, 'b b-p') .

               '      <span class="d">' .
                        Digest::drawDiffs($commit, $this->data['date']) .
               '      </span>
                      <a class="r n" href="' . DIGEST_URL . '/issues/' . $this->data['date'] . '/moreinfo/' . $commit['revision'] . '/" target="_blank">' .
                        sprintf(_('Revision %d'), $commit['revision']) .
                     '</a>
                    </div>
                  </div>
                </div>';
    }

    return $buf;
  }
}

?>