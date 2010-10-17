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


class SettingsUi extends BaseUi {
  public $id      = 'settings';

  private $user   = null;


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Settings');
  }


  public function draw() {
    // draw permissions
    $permissions          = null;
    $availablePermissions = Digest::getPermissions();

    foreach ($availablePermissions as $permission => $permissionData) {
      // check permission?
      if (in_array($permission, $this->user->permissions)) {
        $class   = null;
        $checked = ' checked="checked"';
      } else {
        $class   = ' class="fade"';
        $checked = null;
      }

      $permissions .= '<span title="' . $permissionData['title'] . '"' . $class . '>' . $permissionData['string'] . '</span>' .
                      ' <input type="checkbox" title="' . $permissionData['title'] . '" disabled="disabled"' . $checked . ' />';
    }


    // draw paths
    if (is_array($this->user->paths)) {
      $paths = implode('<br />', $this->user->paths);
    } else {
      $paths = '<span class="fade">' . _('All') . '</span>';
    }


    // define available interfaces
    $interface = array('mouse'    => _('Mouse'),
                       'keyboard' => _('Keyboard'));


    // draw settings
    $buf = '<h3>' .
              _('Personal') .
           '  <div>
                <div id="indicator-personal"><div>&nbsp;</div></div>
                <input type="button" onclick="saveChanges();" value="' . _('Save changes') . '" title="' . _('Save changes') . '" />
              </div>
            </h3>

            <table id="personal">
              <tbody>
                <tr>
                  <td class="label">' . _('Username') . '</td>
                  <td class="value">
                    <input id="data-user" type="text" value="' . $this->user->data['username'] . '" disabled="disabled" />
                  </td>
                </tr>
                <tr>
                  <td class="label">' . _('Email') . '</td>
                  <td class="value">
                    <input id="data-email" name="email" type="text" value="' . $this->user->data['email'] . '" />
                  </td>
                </tr>

                <tr class="padding">
                  <td class="label">' . _('First name') . '</td>
                  <td class="value">
                    <input id="data-firstname" name="firstname" type="text" value="' . $this->user->data['firstname'] . '" />
                  </td>
                </tr>
                <tr>
                  <td class="label">' . _('Last name') . '</td>
                  <td class="value">
                    <input id="data-lastname" name="lastname" type="text" value="' . $this->user->data['lastname'] . '" />
                  </td>
                </tr>

                <tr class="padding">
                  <td class="label">' . _('Language') . '</td>
                  <td class="value">' .
                    Ui::htmlSelector('language', Digest::getLanguages(), $this->user->data['language']) .
           '      </td>
                </tr>

                <tr class="padding">
                  <td class="label">' . _('Interface') . '</td>
                  <td class="value">' .
                    Ui::htmlSelector('interface', $interface, $this->user->data['interface']) .
           '      </td>
                </tr>
              </tbody>
            </table>

            <table id="display">
              <tbody>
                <tr>
                  <td class="label">' . _('Permissions') . '</td>
                  <td class="value">' .
                    $permissions .
           '      </td>
                </tr>

                <tr class="padding">
                  <td id="paths" class="label">' . _('Paths') . '</td>
                  <td class="value">' .
                    $paths .
           '      </td>
                </tr>
              </tbody>
            </table>


            <h3>' .
              _('Change password') .
           '  <div>
                <div id="indicator-change-password"><div>&nbsp;</div></div>
                <input type="button" onclick="changePassword(\'' . $this->user->data['username'] . '\');" value="' . _('Change password') . '" title="' . _('Change password') . '" />
              </div>
            </h3>

            <table id="change-password">
              <tbody>
                <tr>
                  <td class="label">' . _('Old password') . '</td>
                  <td class="value">
                    <input id="data-oldpassword" type="password" value="" />
                  </td>
                </tr>
                <tr class="padding">
                  <td class="label">' . _('New password') . '</td>
                  <td class="value">
                    <input id="data-newpassword" type="password" value="" />
                  </td>
                </tr>
                <tr>
                  <td class="label">' . _('Repeat password') . '</td>
                  <td class="value">
                    <input id="data-repeatpassword" type="password" value="" />
                  </td>
                </tr>
              </tbody>
            </table>';

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/settingsui.js');
  }


  public function getStyle() {
    return array('/css/settingsui.css');
  }
}

?>