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


class ToolsUi extends BaseUi {
  public $id              = 'tools';

  public $categories      = array();
  public $tools           = array();

  private $user           = null;

  private $message        = null;
  private $toolFunction   = null;
  private $toolParams     = null;


  public function __construct($user) {
    $this->user   = $user;


    // setup tool categories
    $this->categories             = array('general'   => _('Tools'),
                                          'config'    => _('Configuration'),
                                          'migration' => _('Legacy Migration'),
                                          'import'    => _('Legacy Import'));


    // setup available tools
    $this->tools['general'][]     = array('id'          => 'parse-developers',
                                          'string'      => _('Parse Developers Data'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/parse-developers/',
                                          'function'    => array($this, 'parseDevelopers'),
                                          'params'      => null);

    $this->tools['general'][]     = array('id'          => 'parse-i18n-teams',
                                          'string'      => _('Parse I18n Teams'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/parse-i18n-teams/',
                                          'function'    => array($this, 'parseI18n'),
                                          'params'      => null);

    //////////////////////////////////////

    $this->tools['config'][]      = array('id'          => 'commit-area-filtering',
                                          'string'      => _('Commit Area Filtering'),
                                          'permission'  => array('admin', 'reviewer', 'classifier'),
                                          'url'         => BASE_URL . '/tools/commit-area-filtering/',
                                          'function'    => array($this, 'commitAreaFiltering'),
                                          'params'      => null);

    $this->tools['config'][]      = array('id'          => 'project-links',
                                          'string'      => _('Project Links'),
                                          'permission'  => array('admin', 'reviewer', 'classifier'),
                                          'url'         => BASE_URL . '/tools/project-links/',
                                          'function'    => array($this, 'projectLinks'),
                                          'params'      => null);

    //////////////////////////////////////

    $this->tools['import'][]      = array('id'          => 'parse-people',
                                          'string'      => _('Parse People'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/parse-people',
                                          'function'    => array($this, 'parsePeople'),
                                          'params'      => null);

    $this->tools['import'][]      = array('id'          => 'parse-bugfixers',
                                          'string'      => _('Parse Bugfixers'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/parse-bugfixers/',
                                          'function'    => array($this, 'parseBugfixers'),
                                          'params'      => null);

    $this->tools['import'][]      = array('id'          => 'parse-countries',
                                          'string'      => _('Parse Countries'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/parse-countries/',
                                          'function'    => array($this, 'parseCountries'),
                                          'params'      => null);

    $this->tools['import'][]      = array('id'          => 'parse-filetypes',
                                          'string'      => _('Parse Filetypes'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/parse-filetypes/',
                                          'function'    => array($this, 'parseFiletypes'),
                                          'params'      => null);

    $this->tools['import'][]      = array('id'          => 'parse-links',
                                          'string'      => _('Parse Links'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/parse-links/',
                                          'function'    => array($this, 'parseLinks'),
                                          'params'      => null);

    //////////////////////////////////////

    $this->tools['migration'][]   = array('id'          => 'digest-intro/issues',
                                          'string'      => _('Import Digest Intro (Issues)'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/digest-intro/issues/',
                                          'function'    => array($this, 'digestIntro'),
                                          'params'      => 'issue');

    $this->tools['migration'][]   = array('id'          => 'digest-intro/archive',
                                          'string'      => _('Import Digest Intro (Archive)'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/digest-intro/archive/',
                                          'function'    => array($this, 'digestIntro'),
                                          'params'      => 'archive');

    $this->tools['migration'][]   = array('id'          => 'digest-stats/issues',
                                          'string'      => _('Import Digest Stats (Issues)'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/digest-stats/issues/',
                                          'function'    => array($this, 'digestStats'),
                                          'params'      => 'issue');

    $this->tools['migration'][]   = array('id'          => 'digest-stats/archive',
                                          'string'      => _('Import Digest Stats (Archive)'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/digest-stats/archive/',
                                          'function'    => array($this, 'digestStats'),
                                          'params'      => 'archive');

    $this->tools['migration'][]   = array('id'          => 'digest-commits',
                                          'string'      => _('Import Digest Commits'),
                                          'permission'  => 'admin',
                                          'url'         => BASE_URL . '/tools/digest-commits/',
                                          'function'    => array($this, 'digestCommits'),
                                          'params'      => null);

    // set title
    $this->title = _('Tools');


    // get tool context
    $this->toolContext = $this->getToolContext();
  }


  public function draw() {
    if (!$this->toolContext || ($this->toolContext == 'need-permissions')) {
      // error
      return $this->message;

    } else if ($this->toolContext == 'menu') {
      // draw menu UI
      return $this->drawMenu();

    } else {
      // show a specific tool
      return call_user_func($this->toolFunction, $this->toolParams);
    }
  }


  public function getScript() {
    return array('/js/frame/toolsui.js');
  }


  public function getStyle() {
    return array('/css/toolsui.css');
  }


  private function getToolContext() {
    // strip trailing slash from passed-in tool name
    $_REQUEST['tool'] = rtrim($_REQUEST['tool'], '/');

    // show menu / specific tool UI?
    if (empty($_REQUEST['tool']) || (strpos($_REQUEST['tool'], 'tools') !== false)) {
      // draw menu UI
      return 'menu';

    } else {
      // show a specific tool
      foreach ($this->tools as $category => $tools) {
        foreach ($tools as $tool) {
          // match requested tool to available tools
          if ($tool['id'] == $_REQUEST['tool']) {
            // check that user has needed permissions to access tool
            if (($buf = App::checkPermission($this->user, $tool['permission'])) ||
                (!ENABLE_LEGACY && (($category == 'migration') || ($category == 'import')))) {

              // user does not have needed access permissions
              $this->message = $buf;

              return 'need-permissions';

            } else {
              $this->title          = $tool['string'];

              $this->toolFunction   = $tool['function'];
              $this->toolParams     = $tool['params'];

              return $tool['id'];
            }
          }
        }
      }


      // tool not found
      $this->message = _('Tool not found');

      return false;
    }
  }


  private function drawMenu() {
    $buf = null;

    // draw tools within sections
    foreach ($this->tools as $category => $tools) {
      // show legacy section?
      if (!ENABLE_LEGACY && (($category == 'migration') || ($category == 'import'))) {
        continue;
      }

      // draw section
      $buf  .= '<h3>' . $this->categories[$category] . '</h3>

                <ul>';

      foreach ($tools as $tool) {
        // only draw if we have permission to access this tool
        if (!App::checkPermission($this->user, $tool['permission'])) {
          $buf  .= '<li>
                      <a href="' . $tool['url'] . '">' .
                        $tool['string'] .
                   '  </a>
                    </li>';
        }
      }

      $buf  .= '</ul>';

      // add to created array, reset for next iteration
      $tmp[] = $buf;
      $buf   = null;
    }


    // output
    foreach ($tmp as $t) {
      if (strpos($t, '<li>') !== false) {
        $buf .= $t;
      }
    }

    return $buf;
  }


  private function parseDevelopers() {
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

                <input type="submit" value="' . _('Parse developers') . '" title="' . _('Parse developers') . '" onclick="parseDevelopers(event);" />
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


  private function parseLinks() {
    $buf = '<iframe id="result" src="' . BASE_URL . '/tool/parse-links.php"></iframe>';

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


  private function commitAreaFiltering() {
    // get existing commit path classifications
    $commitPaths    = Enzyme::getClassifications(true);

    // get available areas
    $availableAreas = array_values(Enzyme::getAreas());
    array_unshift($availableAreas, null);

    // get available targets
    $availableTargets = Enzyme::getFilterTargets();


    // draw
    $buf   = '<h3>' .
                _('Commit Area Filtering') .
             '  <span class="floating-buttons">
                  <span id="status" class="status">' .
                    sprintf(_('%d filters'), count($commitPaths)) .
             '    </span>
                  <input type="button" value="' . _('Save changes') . '" title="' . _('Save changes') . '" onclick="saveFilters();" />
                  <input type="button" value="' . _('Add new filter') . '" title="' . _('Add new filter') . '" onclick="addNewFilter();" />' .
                  Ui::drawIndicator('save-items') .
             '  </span>
              </h3>

              <form id="path-filters-data" action="">
                <table id="path-filters">
                  <thead>
                    <tr>
                      <th class="delete">&nbsp;</th>
                      <th>' . _('Target') . '</th>
                      <th>' . _('Match') . '</th>
                      <th>' . _('Area') . '</th>
                    </tr>
                  </thead>

                  <tbody id="path-filters-items">';

    foreach ($commitPaths as $id => $item) {
      $buf  .= '<tr id="path-filter-' . $id . '"  class="' . $id . '">
                  <td>
                    <span class="delete-link indicator-failure" onclick="deleteItem(\'filter\', \'path-filter-' . $id . '\', \'' . $id . '\');" title="' . _('Delete this filter?') . '">
                      <span>&nbsp;</span>
                    </span>
                  </td>
                  <td>
                    <input type="hidden" name="id[]" value="' . $id . '" />' .
                    Ui::htmlSelector('target-' . $id, $availableTargets, $item['target'], null, 'targets[]') .
               '  </td>
                  <td>
                    <input type="text" value="' . $item['matched'] . '" name="matches[]" />
                  </td>
                  <td>' .
                    Ui::htmlSelector('path-' . $id, $availableAreas, $item['area'], null, 'areas[]') .
               '  </td>
                </tr>';
    }

    // draw empty row
    $buf  .= '      <tr id="path-filters-new" style="display:none;">
                      <td>
                        &nbsp;
                      </td>
                      <td>
                        <input type="hidden" name="id[]" value="" style="display:none;" />' .
                        Ui::htmlSelector('target-new', $availableTargets, null, null, 'targets[]', 'display:none;') .
             '        </td>
                      <td>
                        <input type="text" value="" name="matches[]" style="display:none;" />
                      </td>
                      <td>' .
                        Ui::htmlSelector('path-new', $availableAreas, null, null, 'areas[]', 'display:none;') .
                   '  </td>
                    </tr>';

    $buf  .= '    </tbody>
                </table>
              </form>';

    return $buf;
  }


  private function projectLinks() {
    // get links
    $links = Enzyme::loadLinks(false, 'type');

    // calculate number of links
    $numLinks = 0;

    foreach ($links as $section) {
      $numLinks += count($section);
    }

    $availableTypes = array('project'   =>  _('Project'),
                            'program'   =>  _('Program'),
                            'external'  =>  _('External'),
                            'other'     =>  _('Other'));

    // draw
    $buf   = '<h3>' .
                _('Project Links') .
             '  <span class="floating-buttons">
                  <span id="status" class="status">' .
                    sprintf(_('%d links'), $numLinks) .
             '    </span>
                  <input type="button" value="' . _('Save changes') . '" title="' . _('Save changes') . '" onclick="saveLinks();" />
                  <input type="button" value="' . _('Add new link') . '" title="' . _('Add new link') . '" onclick="addNewLink();" />' .
                  Ui::drawIndicator('save-items') .
             '  </span>
              </h3>

              <form id="path-links-data" action="">';

    foreach ($links as $name => $section) {
      $buf  .= '<table id="path-links-' . $name . '" class="path-links">
                  <thead>
                    <tr>
                      <th class="delete">&nbsp;</th>
                      <th class="type">' . _('Type') . '</th>
                      <th class="name">' . _('Name') . '</th>
                      <th class="link">' . _('URL') . '</th>
                      <th class="area">' . _('Area') . '</th>
                    </tr>
                  </thead>

                  <tbody id="path-links-' . $name . '-items">';

      foreach ($section as $id => $item) {
        $buf  .= '<tr id="path-link-' . $name . '-' . $id . '">
                    <td>
                      <span class="delete-link indicator-failure" onclick="deleteItem(\'link\', \'path-link-' . $name . '-' . $id . '\', \'' . $item['name'] . '\');" title="' . _('Delete this link?') . '">
                        <span>&nbsp;</span>
                      </span>
                    </td>
                    <td>' .
                      Ui::htmlSelector('type-' . $name . '-' . $id, $availableTypes, $item['type'], null, 'types[]') .
                 '  </td>
                    <td>
                      <input type="text" value="' . $item['name'] . '" name="names[]" />
                    </td>
                    <td>
                      <input type="text" value="' . $item['url'] . '" name="links[]" />
                    </td>
                    <td>
                      <input type="text" value="' . $item['area'] . '" name="areas[]" />
                    </td>
                  </tr>';
      }

      // draw empty row
      $buf  .= '      <tr id="path-links-' . $name . '-new" style="display:none;">
                        <td>
                          &nbsp;
                        </td>
                        <td>' .
                          Ui::htmlSelector('type-' . $name . '-new', $availableTypes, null, null, 'types[]', 'display:none;') .
               '        </td>
                        <td>
                          <input type="text" value="" name="names[]" style="display:none;" />
                        </td>
                        <td>
                          <input type="text" value="" name="links[]" style="display:none;" />
                        </td>
                        <td>
                          <input type="text" value="" name="areas[]" style="display:none;" />
                        </td>
                      </tr>';

      $buf  .= '    </tbody>
                  </table>';
    }

    $buf  .= '  </form>';

    return $buf;
  }
}

?>