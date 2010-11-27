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


class UsersUi extends BaseUi {
  public $id                    = 'users';

  private $user                 = array();
  private $users                = array();

  private $availablePermissions = array();

  private $applications         = array();


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Users');

    // load users, grouped by permission
    $this->users = Digest::getUsersByPermission(null, true, true);
    sort($this->users);

    // set available permissions
    $this->availablePermissions = Digest::getPermissions();

    // get available jobs
    $this->availableJobs = Enzyme::getAvailableJobs();

    // load applications
    $this->applications = Db::load('applications', false, null, '*', false);
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'admin')) {
      return $buf;
    }

    // draw
    $buf = '<h3>' .
              _('Users') .
           '  <span>
                <span class="status">' .
                  sprintf(_('%d users'), Digest::getNumUsers()) .
           '    </span>
                <input type="button" title="' . _('Add new user') . '" value="' . _('Add new user') . '" onclick="addUser();" />
              </span>
            </h3>

            <table id="users">
              <thead>
                <tr>
                  <th>&nbsp;</th>
                  <th>' . _('Username') . '</th>
                  <th>' . _('Email') . '</th>
                  <th>' . _('First name') . '</th>
                  <th class="padding">' . _('Last name') . '</th>';

    // draw columns for permissions
    foreach ($this->availablePermissions as $permission => $permissionData) {
      $buf .=  '<th class="permission" title="' . $permissionData['title'] . '">' .
                  $permissionData['string'] .
               '</th>';
    }

    $buf .=  '    <th>' . _('Paths') . '</th>
                </tr>
              </thead>

              <tbody id="users-body">';

    $shown = array();

    foreach ($this->users as $permission => $users) {
      foreach ($users as $username => $user) {
        if (!isset($shown[$username])) {
          $buf .= $this->drawRow($user);

          // remember user, so we only draw each one once!
          $shown[$username] = true;
        }
      }
    }

    // draw hidden row, to allow creation of new users
    $buf  .= $this->drawRow(null);

    $buf  .= '  </tbody>
              </table>';


    // draw available jobs
    $buf  .= '<h3>' .
              _('Available Jobs') .
             '</h3>

              <div id="available-jobs">';

    foreach ($this->availableJobs as $job => $jobData) {
      $buf  .= '<label>
                  <input type="checkbox" checked="checked" />' . $jobData['title'] .
               '  <span>' .
                    $jobData['description'] .
               '  </span>
                </label>';
    }

    $buf  .= '</div>';


    // draw job applications?
    if ($this->applications) {
      $buf  .= '<h3>' .
                _('Applications') .
               '  <div>
                    <span class="status">' .
                      sprintf(_('%d applications'), count($this->applications)) .
               '    </span>
                  </div>
                </h3>' .

               $this->drawApplications();
    }

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/usersui.js');
  }


  public function getStyle() {
    return array('/css/usersui.css');
  }


  private function drawRow($user = null) {
    if ($user) {
      $rowId    = 'row-' . $user['data']['username'];
      $rowStyle = null;
      $pathsId  = ' id="paths-' . $user['data']['username'] . '"';

      // set onchange function
      $onChange = ' onchange="saveChange(\'' . $user['data']['username'] . '\', event);"';

      // set account status button
      if ($user['data']['active']) {
        // active
        $buttonClass = 'active';
        $buttonState = 'false';
        $buttonTitle = _('Make this user inactive?');

      } else {
        // inactive
        $buttonClass = 'inactive';
        $buttonState = 'true';
        $buttonTitle = _('Make this user active?');
      }


      // draw permissions
      $userPermissions      = array_flip(preg_split('/[\s,]+/', $user['data']['permissions']));
      $permissionsString    = null;

      foreach ($this->availablePermissions as $permission => $permissionData) {
        // don't allow user to remove their own admin permission!
        if (($user['data']['username'] == $this->user->data['username']) &&
            ($permission == 'admin')) {

          $disabled = ' disabled="disabled"';

        } else {
          $disabled = null;
        }

        // is permission currently set?
        if (isset($userPermissions[$permission])) {
          $checked = ' checked="checked"';
        } else {
          $checked = null;
        }

        $permissionsString .= '<td>
                                 <input id="permission-' . $permission . '-' . $user['data']['username'] . '" type="checkbox"' . $checked . ' name="permission-' . $permission . '"' . $onChange . $disabled . ' />
                               </td>';
      }


      // show paths (reviewers / classifiers)
      if (isset($userPermissions['reviewer']) || isset($userPermissions['classifier'])) {
        $pathsState = null;
      } else {
        $pathsState = ' style="display:none;"';
      }


      // don't allow user to disable their own account!
      if ($user['data']['username'] == $this->user->data['username']) {
        $accountButton = null;
      } else {
        $accountButton = '<div id="active-' . $user['data']['username'] . '" class="account-status ' . $buttonClass . '" title="' . $buttonTitle . '" onclick="setAccountActive(\'' . $user['data']['username'] . '\', ' . $buttonState . ');">
                            <div>&nbsp;</div>
                          </div>';
      }

    } else {
      // draw a blank row
      $rowId             = 'row-new-0';
      $rowStyle          = ' style="display:none;"';
      $pathsId           = null;
      $onChange          = null;
      $pathsState        = null;

      $accountButton     = '<div class="account-status" title="' . _('Save new account?') . '" onclick="saveNewAccount(event);">
                              <div>&nbsp;</div>
                            </div>';

      $permissionsString = null;

      foreach ($this->availablePermissions as $permission => $permissionData) {
        $permissionsString .= '<td>
                                 <input id="permission-' . $permission . '" type="checkbox" name="permission-' . $permission . '" />
                               </td>';
      }
    }


    // draw row
    $buf =   '<tr id="' . $rowId . '"' . $rowStyle . '>
                <td>' .
                  $accountButton .
             '  </td>
                <td>
                  <input type="text" value="' . $user['data']['username'] . '" name="username"' . $onChange . ' />
                </td>
                <td>
                  <input type="text" value="' . $user['data']['email'] . '" name="email"' . $onChange . ' />
                </td>
                <td>
                  <input type="text" value="' . $user['data']['firstname'] . '" name="firstname"' . $onChange . ' />
                </td>
                <td class="padding">
                  <input type="text" value="' . $user['data']['lastname'] . '" name="lastname"' . $onChange . ' />
                </td>' .
                $permissionsString .
             '  <td>
                  <input' . $pathsId . ' type="text" value="' . $user['data']['paths'] . '" name="paths"' . $onChange . $pathsState . ' />
                </td>
              </tr>';

    return $buf;
  }


  private function drawApplications() {
    if (!$this->applications) {
      return false;
    }

    $counter = 0;

    // draw applications
    $buf = '<div id="applications">';

    foreach ($this->applications as $application) {
      $permissionsString = null;

      // draw permission (job) checkboxes
      foreach ($this->availablePermissions as $permission => $permissionData) {
        // is permission currently set?
        if ($permission == $application['job']) {
          $checked = ' checked="checked"';
        } else {
          $checked = null;
        }

        $permissionsString  .= '<label>
                                  <input id="permission-' . $permission . '-' . $counter . '" type="checkbox"' . $checked . ' name="permission-' . $permission . '" /> ' . $permissionData['title'] .
                               '</label>';
      }

      // draw application
      $buf  .= '<div class="application">
                  <table id="application-' . $counter . '">
                    <tbody>
                      <tr>
                        <td class="label">' . _('Username') . '</td>
                        <td class="value">
                          <input id="username-' . $counter . '" name="username" type="text" value="" />
                        </td>
                      </tr>
                      <tr>
                        <td class="label">' . _('First name') . '</td>
                        <td class="value">
                          <input id="firstname-' . $counter . '" name="firstname" type="text" value="' . $application['firstname'] . '" />
                        </td>
                      </tr>
                      <tr>
                        <td class="label">' . _('Last name') . '</td>
                        <td class="value">
                          <input id="lastname-' . $counter . '" name="lastname" type="text" value="' . $application['lastname'] . '" />
                        </td>
                      </tr>
                      <tr>
                        <td class="label">' . _('Email') . '</td>
                        <td class="value">
                          <input id="email-' . $counter . '" name="email" type="text" value="' . $application['email'] . '" />
                        </td>
                      </tr>
                      <tr class="jobs">
                        <td class="label">' . _('Job') . '</td>
                        <td class="value">' . $permissionsString . '</td>
                      </tr>
                      <tr>
                        <td class="label">' . _('Paths') . '</td>
                        <td class="value">
                          <input id="paths-' . $counter . '" name="paths" type="text" value="' . $application['paths'] . '" />
                        </td>
                      </tr>
                      <tr class="message">
                        <td class="label">' . _('Message') . '</td>
                        <td class="value">' . str_replace("\n", '<br />', $application['message']) . '</td>
                      </tr>
                    </tbody>
                  </table>

                  <div class="reply">
                    <input type="button" value="' . _('Decline') . '" onclick="manageApplication(\'decline\', \'' . $counter . '\');" />
                    <input type="button" value="' . _('Approve') . '" onclick="manageApplication(\'approve\', \'' . $counter . '\');" />
                  </div>
                </div>';

      ++$counter;
    }

    $buf  .= '</div>';

    return $buf;
  }
}

?>