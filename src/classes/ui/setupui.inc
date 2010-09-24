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


class SetupUi {
  public $id                  = 'setup';

  private $setupDatabase      = false;
  private $availableSettings  = false;


  public function __construct($setupDatabase = false) {
    $this->setupDatabase = $setupDatabase;

    // define available settings
    $this->availableSettings = Enzyme::getAvailableSettings();
  }


  public function drawPage() {
    $buf = '<div id="header">
              <div id="logo">&nbsp;</div>
              <h1 id="header-title">' . _('Setup') . '</h1>
            </div>

            <div id="content">
              <div id="setup-column">';

    if ($this->setupDatabase) {
      // draw database setup
      $buf .= $this->drawDatabase();

    } else {
      // draw regular setup
      $buf .= $this->draw();
    }

    $buf  .= '  </div>
              </div>';

    return $buf;
  }


  public function draw() {
    $buf  .= '<form id="setup-form" action="">
                <table>
                  <tbody>';

    foreach ($this->availableSettings as $key => $data) {
      $class = null;
      $value = null;

      // determine input type to show
      if (isset($data['valid']) && is_array($data['valid'])) {
        // select box
        if (!empty($data['default'])) {
          // preselect a default value
          $input = Ui::htmlSelector($key, $data['valid'], $data['default']);
        } else {
          $input = Ui::htmlSelector($key, $data['valid']);
        }

      } else {
        // input box
        if (!empty($data['default'])) {
          $value = ' value="' . $data['default'] . '"';

        } else if (!empty($data['example'])) {
          $value = ' value="' . $data['example'] . '"';
          $class = ' class="prompt" onfocus="inputPrompt(event);" onblur="inputPrompt(event);"';
        }

        $input = '<input id="' . $key . '" name="' . $key . '" type="text"' . $value . $class . ' />';
      }

      $buf  .= '<tr>
                  <td class="label">' . $data['title'] . '</td>
                  <td class="value">' . $input . '</td>
                </tr>';
    }

    $buf  .= '    </tbody>
                </table>

                <input id="setup-save" type="button" value="' . _('Save') . '" onclick="saveSetup();" />
              </form>';

    return $buf;
  }


  private function drawDatabase() {
    $buf = 'database';

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/setupui.js');
  }


  public function getStyle() {
    return array();
  }
}

?>