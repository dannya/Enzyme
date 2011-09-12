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


class SetupUi extends BaseUi {
  public $id                        = 'setup';
  public $title                     = null;

  private $setupDatabase            = false;
  private $configuredRepositories   = false;
  private $availableSettings        = false;

  private $users                    = false;
  private $settings                 = false;


  public function __construct($setupDatabase = false) {
    // setup language
    App::setLanguage();

    // set title
    $this->title = _('Setup');

    // do we need to setup the database?
    $this->setupDatabase = $setupDatabase;


    if (!$this->setupDatabase) {
      // get currently logged in user, so we can check permissions, and hide disastrous buttons!
      $this->user = new User();

      if ($this->users = Db::exists('users')) {
        // load current settings (if available)
        $this->settings = Db::reindex(Db::load('settings', false), 'setting');
      }
    }

    // define configured repositories
    $this->configuredRepositories = Connector::getRepositories();

    // define available settings
    $this->availableSettings      = Enzyme::getGroupedSettings();
  }


  public function drawPage() {
    $buf = '<div id="header">
              <a id="logo" class="n" href="' . BASE_URL . '/">&nbsp;</a>
              <h1 id="header-title">' . _('Setup') . '</h1>
            </div>

            <div id="content">
              <div id="setup-column">';

    if ($this->setupDatabase) {
      // draw database setup
      $buf  .= '<h2>' . sprintf(_('Step %d of %d'), 1, 3) . '</h2>' .
                $this->drawDatabaseSetup();

    } else if (!$this->users) {
      // ensure we are logged out!
      User::logout(false);

      // draw user setup
      $buf  .= '<h2>' . sprintf(_('Step %d of %d'), 2, 3) . '</h2>' .
                $this->drawUserSetup();

    } else {
      // draw regular setup
      $buf  .= '<h2>' . sprintf(_('Step %d of %d'), 3, 3) . '</h2>' .
                $this->draw();
    }

    $buf  .= '  </div>
              </div>';

    return $buf;
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'admin')) {
      return $buf;
    }


    // draw repositories management interface
    $buf = $this->drawRepositories();


    // draw other settings
    $buf  .= '<form id="setup-form" action="">';

    foreach ($this->availableSettings as $settings) {
      // draw title
      $buf  .= '<h3>' . $settings['title'] . '</h3>

                <table class="settings">
                  <tbody>';

      foreach ($settings['settings'] as $key => $data) {
        $class = null;
        $value = null;

        // determine input type to show
        if (isset($data['valid']) && is_array($data['valid'])) {
          // select box
          if (isset($this->settings[$key]['value'])) {
            $default = $this->settings[$key]['value'];
          } else if (!empty($data['default'])) {
            // preselect a default value
            $default = $data['default'];
          } else {
            $default = null;
          }

          $input = Ui::htmlSelector($key, $data['valid'], $default);

        } else {
          // input box
          if (!empty($this->settings[$key]['value'])) {
            $value = ' value="' . $this->settings[$key]['value'] . '"';

          } else if (!empty($data['default'])) {
            $value = ' value="' . $data['default'] . '"';

          } else if (!empty($data['example'])) {
            $value = ' value="' . $data['example'] . '"';
            $class = ' class="prompt" onfocus="inputPrompt(event);" onblur="inputPrompt(event);"';
          }

          $input = '<input id="' . $key . '" name="' . $key . '" type="text"' . $value . $class . ' />';
        }

        // show per-setting comment?
        if (!empty($data['comment'])) {
          $comment = '<div class="comment">' .
                        $data['comment'] .
                     '</div>';
        } else {
          $comment = null;
        }

        // draw row
        $buf  .= '<tr>
                    <td class="label">' . $data['title'] . '</td>
                    <td class="value">
                      <div>' . $input . $comment . '</div>
                    </td>
                  </tr>';
      }

      $buf  .= '    </tbody>
                </table>';
    }

    $buf  .= '  <input id="setup-save" type="button" value="' . _('Save') . '" onclick="saveSetup();" />
              </form>';

    return $buf;
  }


  public function drawRepositories() {
    // draw
    $buf = '<h3>' .
              _('Repositories') .
           '<span>
                <span class="status">' . sprintf(_('%d repositories'), count($this->configuredRepositories)) . '</span>
                <input type="button" onclick="addRepository();" value="' . _('Add repository') . '" title="' . _('Add repository') . '" />
              </span>
            </h3>

            <table id="repositories">
              <thead>
                <tr>
                  <th>&nbsp;</th>
                  <th class="col-priority">' . _('Priority') . '</th>
                  <th class="col-id">' . _('ID') . '</th>
                  <th class="col-type">' . _('Type') . '</th>
                  <th class="col-hostname">' . _('Hostname') . '</th>
                  <th class="col-port">' . _('Port') . '</th>
                  <th class="col-username">' . _('Username') . '</th>
                  <th class="col-password">' . _('Password') . '</th>
                  <th class="col-accounts-file">' . _('Accounts file') . '</th>
                  <th class="col-web-viewer">' . _('Web viewer') . '</th>
                </tr>
              </thead>

              <tbody>';

    foreach ($this->configuredRepositories as $repo) {
      $buf .= $this->drawRow($repo);
    }

    // draw hidden row, to allow creation of new users
    $buf  .= $this->drawRow(null);

    $buf  .= '  </tbody>
              </table>

              <span id="repo-security-msg">' .
                _('Repository passwords are stored as plaintext. Consider setting up a separate, read-only repository user if security is important.') .
             '</span>';

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/setupui.js');
  }


  public function getStyle() {
    return array('/css/frame/setupui.css');
  }


  private function drawRow($repo = null) {
    if ($repo) {
      $rowId    = 'row-' . $repo['id'];
      $rowStyle = null;
      $pathsId  = ' id="paths-' . $repo['id'] . '"';

      // set onchange function
      $onChange = ' onchange="saveChange(\'' . $repo['id'] . '\', event);"';

      // set repo delete button
      $buttonClass = 'active';
      $buttonState = 'false';
      $buttonTitle = _('Delete repository?');

      $accountButton = '<div id="active-' . $repo['id'] . '" class="repository-status ' . $buttonClass . '" title="' . $buttonTitle . '" onclick="deleteRepository(\'' . $repo['id'] . '\');">
                          <div>&nbsp;</div>
                        </div>';

    } else {
      // draw a blank row
      $rowId             = 'row-new-0';
      $rowStyle          = ' style="display:none;"';
      $pathsId           = null;
      $onChange          = null;
      $pathsState        = null;

      $accountButton     = '<div class="repository-status" title="' . _('Save new repository?') . '" onclick="saveNewRepository(event);">
                              <div>&nbsp;</div>
                            </div>';
    }


    // draw row
    $buf =   '<tr id="' . $rowId . '"' . $rowStyle . '>
                <td>' .
                  $accountButton .
             '  </td>
                <td class="col-priority">
                  <input type="text" value="' . $repo['priority'] . '" name="priority"' . $onChange . ' />
                </td>
                <td class="col-id">
                  <input type="text" value="' . $repo['id'] . '" name="id"' . $onChange . ' />
                </td>
                <td class="col-type">' .
                  Ui::htmlSelector('repo-types-' . $rowId, Connector::getTypes(), $repo['type'], null, 'type') .
             '  </td>
                <td class="col-hostname">
                  <input type="text" value="' . $repo['hostname'] . '" name="hostname"' . $onChange . ' />
                </td>
                <td class="col-port">
                  <input type="text" value="' . $repo['port'] . '" name="port"' . $onChange . ' />
                </td>
                <td class="col-username">
                  <input type="text" value="' . $repo['username'] . '" name="username"' . $onChange . ' />
                </td>
                <td class="col-password">
                  <input type="text" value="' . $repo['password'] . '" name="password"' . $onChange . ' />
                </td>
                <td class="col-accounts-file">
                  <input type="text" value="' . $repo['accounts_file'] . '" name="accounts_file"' . $onChange . ' />
                </td>
                <td class="col-web-viewer">
                  <input type="text" value="' . $repo['web_viewer'] . '" name="web_viewer"' . $onChange . ' />
                </td>
              </tr>';

    return $buf;
  }


  private function drawDatabaseSetup() {
    $buf = '<div id="setup-database">
              <div id="setup-database-details">' .
                sprintf(_('Enzyme cannot find a compatible database at the location set at the top of %s:'), '<a href="file:///' . str_replace('\\', '/', BASE_DIR) . '/autoload.php">' . BASE_DIR . '/autoload.php</a>') .

           '    <table class="setup-details">
                  <tbody>
                    <tr>
                      <td class="label">' . _('Server') . '<td>
                      <td class="value">' . Config::$db['host'] . '</td>
                    </tr>
                    <tr>
                      <td class="label">' . _('Database') . '<td>
                      <td class="value">' . Config::$db['database'] . '</td>
                    </tr>
                  </tbody>
                </table>

                <span>' .
                  _('Click "Setup database" to let Enzyme create a compatible database ready for use.') .
           '    </span>
              </div>

              <div id="setup-database-output" class="r" style="display:none;">
              </div>

              <input id="setup-database-button" type="button" value="' . _('Setup database') . '" onclick="setupDatabase();" />
              <input id="setup-database-next" type="button" value="' . _('Next') . '" onclick="location.reload(true);" style="display:none;" />
            </div>

            <div id="setup-database-error" style="display:none;">' .
              _('Errors were encountered when trying to create the database and tables, please try to fix them manually.') .
           '</div>';

    return $buf;
  }


  private function drawUserSetup() {
    $buf = '<div id="setup-user">' .
              _('Next, we need to setup an admin user account for Enzyme.') .

           '  <table id="setup-user-details" class="setup-details">
                <tbody>
                  <tr>
                    <td class="label">' . _('Username') . '<td>
                    <td class="value">
                      <input id="setup-user-username" type="text" value="admin" name="username" />
                    </td>
                  </tr>
                  <tr>
                    <td class="label">' . _('Password') . '<td>
                    <td class="value">
                      <input id="setup-user-password" type="password" name="password" />
                    </td>
                  </tr>

                  <tr class="padding">
                    <td class="label">' . _('First name') . '<td>
                    <td class="value">
                      <input id="setup-user-username" type="text" name="firstname" />
                    </td>
                  </tr>
                  <tr>
                    <td class="label">' . _('Last name') . '<td>
                    <td class="value">
                      <input id="setup-user-username" type="text" name="lastname" />
                    </td>
                  </tr>
                  <tr>
                    <td class="label">' . _('Email') . '<td>
                    <td class="value">
                      <input id="setup-user-email" type="text" name="email" />
                    </td>
                  </tr>
                </tbody>
              </table>

              <input id="setup-user-button" type="button" value="' . _('Create user') . '" onclick="setupUser();" />
            </div>

            <script type="text/javascript">
              if ($("setup-user-password")) {
                $("setup-user-password").focus();
              }
            </script>';

    return $buf;
  }
}

?>