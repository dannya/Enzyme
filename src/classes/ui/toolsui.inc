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


class ToolsUi extends BaseUi {
  public $id      = 'tools';

  private $user   = null;


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Tools');
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'admin')) {
      return $buf;
    }

    if (empty($_REQUEST['tool']) || ($_REQUEST['tool'] == '/tools/')) {
      // draw menu UI
      return $this->drawMenu();

    } else {
      // show a specific tool
      if ($_REQUEST['tool'] == '/tools/parse-authors/') {
        $buf = $this->parseAuthors();
      } else if ($_REQUEST['tool'] == '/tools/parse-i18n-teams/') {
        $buf = $this->parseI18n();
      } else if ($_REQUEST['tool'] == '/tools/parse-people/') {
        $buf = $this->parsePeople();
      } else if ($_REQUEST['tool'] == '/tools/parse-bugfixers/') {
        $buf = $this->parseBugfixers();
      } else if ($_REQUEST['tool'] == '/tools/parse-countries/') {
        $buf = $this->parseCountries();
      } else if ($_REQUEST['tool'] == '/tools/parse-filetypes/') {
        $buf = $this->parseFiletypes();
      } else if ($_REQUEST['tool'] == '/tools/digest-intro/issues/') {
        $buf = $this->digestIntro('issue');
      } else if ($_REQUEST['tool'] == '/tools/digest-intro/archive/') {
        $buf = $this->digestIntro('archive');
      } else if ($_REQUEST['tool'] == '/tools/digest-stats/issues/') {
        $buf = $this->digestStats('issue');
      } else if ($_REQUEST['tool'] == '/tools/digest-stats/archive/') {
        $buf = $this->digestStats('archive');
      } else if ($_REQUEST['tool'] == '/tools/digest-commits/') {
        $buf = $this->digestCommits();
      }
    }

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/toolsui.js');
  }


  public function getStyle() {
    return array('/css/toolsui.css');
  }


  private function drawMenu() {
    $buf = '<h3>' . _('Tools') . '</h3>

            <ul>
              <li>
                <a href="' . BASE_URL . '/tools/parse-authors/">' .
                  _('Parse Authors Data') .
           '    </a>
              </li>
              <li>
                <a href="' . BASE_URL . '/tools/parse-i18n-teams/">' .
                  _('Parse I18n Teams') .
           '    </a>
              </li>
            </ul>';

    // show legacy tools?
    if (ENABLE_LEGACY) {
      $buf  .= '<h3>' . _('Legacy Migration') . '</h3>

                <ul>
                  <li>
                    <a href="' . BASE_URL . '/tools/digest-intro/issues/">' .
                      _('Import Digest Intro (Issues)') .
               '    </a>
                  </li>
                  <li>
                    <a href="' . BASE_URL . '/tools/digest-intro/archive/">' .
                      _('Import Digest Intro (Archive)') .
               '    </a>
                  </li>

                  <li>
                    <a href="' . BASE_URL . '/tools/digest-stats/issues/">' .
                      _('Import Digest Stats (Issues)') .
               '    </a>
                  </li>
                  <li>
                    <a href="' . BASE_URL . '/tools/digest-stats/archive/">' .
                      _('Import Digest Stats (Archive)') .
               '    </a>
                  </li>

                  <li>
                    <a href="' . BASE_URL . '/tools/digest-commits/">' .
                      _('Import Digest Commits') .
               '    </a>
                  </li>
                </ul>


                <h3>' . _('Legacy Import') . '</h3>

                <ul>
                  <li>
                    <a href="' . BASE_URL . '/tools/parse-bugfixers/">' .
                      _('Parse Bugfixers') .
               '    </a>
                  </li>
                  <li>
                    <a href="' . BASE_URL . '/tools/parse-countries/">' .
                      _('Parse Countries') .
               '    </a>
                  </li>
                  <li>
                    <a href="' . BASE_URL . '/tools/parse-filetypes/">' .
                      _('Parse Filetypes') .
               '    </a>
                  </li>
                  <li>
                    <a href="' . BASE_URL . '/tools/parse-people/">' .
                      _('Parse People') .
               '    </a>
                  </li>
                </ul>';
    }

    return $buf;
  }


  private function parseAuthors() {
    if (!empty($_POST['show_skipped'])) {
      $skip = ' checked="checked"';
    } else {
      $skip = null;
    }

    // draw settings console
    $buf = '<div id="console">
              <form id="settings" name="settings" method="post" action="">
                <label>
                  <input id="show-skipped" type="checkbox" value="1"' . $skip . ' /> ' . _('Show Skipped?') .
           '    </label>

                <input type="submit" value="' . _('Parse authors') . '" title="' . _('Parse authors') . '" onclick="parseAuthors(event);" />
              </form>
            </div>

            <iframe id="result" src="' . BASE_URL . '/get/prompt.php?language=' . LANGUAGE . '"></iframe>';

    return $buf;
  }


  private function parseI18n() {
    $buf = '<iframe id="result" src="' . BASE_URL . '/tool/parse-i18n.php"></iframe>';

    return $buf;
  }


  private function parseBugfixers() {
    $buf = '<iframe id="result" src="' . BASE_URL . '/tool/parse-bugfixers.php"></iframe>';

    return $buf;
  }


  private function parseCountries() {
    $buf = '<iframe id="result" src="' . BASE_URL . '/tool/parse-countries.php"></iframe>';

    return $buf;
  }


  private function parseFiletypes() {
    $buf = '<iframe id="result" src="' . BASE_URL . '/tool/parse-filetypes.php"></iframe>';

    return $buf;
  }


  private function parsePeople() {
    $buf = '<iframe id="result" src="' . BASE_URL . '/tool/parse-people.php"></iframe>';

    return $buf;
  }


  private function digestIntro($type = 'issue') {
    $buf = '<iframe id="result" src="' . BASE_URL . '/tool/digest-intro.php?type=' . $type . '"></iframe>';

    return $buf;
  }


  private function digestStats($type = 'issue') {
    $buf = '<iframe id="result" src="' . BASE_URL . '/tool/digest-stats.php?type=' . $type . '"></iframe>';

    return $buf;
  }


  private function digestCommits() {
    $buf = '<iframe id="result" src="' . BASE_URL . '/tool/digest-commits.php"></iframe>';

    return $buf;
  }
}

?>