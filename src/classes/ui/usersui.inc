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


  public function __construct() {
    $this->title = _('Users');

    // get currently logged in user, so we can check permissions, and hide disastarous buttons!
    $this->user = new User();

    // load users, grouped by permission
    $this->users = Digest::getUsersByPermission(null, false, true);

    // set available permissions
    $this->availablePermissions = Digest::getPermissions();
  }


  public function draw() {
    // check permission
    if ($buf = App::checkPermission($this->user, 'admin')) {
      return $buf;
    }

    // draw
    $buf = '<h3>' .
              _('Users') .
           '  <div>
                <input type="button" title="' . _('Add new user') . '" value="' . _('Add new user') . '" onclick="addUser();" />
              </div>
            </h3>

            <table id="users">
              <thead>
                <tr>
                  <td>&nbsp;</td>
                  <td>' . _('Username') . '</td>
                  <td>' . _('Email') . '</td>
                  <td>' . _('First name') . '</td>
                  <td class="padding">' . _('Last name') . '</td>';

    // draw columns for permissions
    foreach ($this->availablePermissions as $permission => $permissionData) {
      $buf .=  '<td class="permission" title="' . $permissionData['title'] . '">' .
                  $permissionData['string'] .
               '</td>';
    }

    $buf .=  '    <td>' . _('Paths') . '</td>
                </tr>
              </thead>

              <tbody id="users-body">';

    foreach ($this->users as $user) {
      $buf .= $this->drawRow($user);
    }

    // draw hidden row, to allow creation of new users
    $buf .= $this->drawRow(null);

    $buf .=  '  </tbody>
              </table>';

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
}

?>