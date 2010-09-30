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


class HomeUi extends BaseUi {
  public $id      = 'home';

  private $user   = null;


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Home');
  }


  public function draw() {
    $buf = null;

    if (!$this->user->hasPermission('admin')) {
      $buf  .= '<h3>' . _('Welcome to Enzyme!') . '</h3>';

    } else {
      // draw admin information panels
      $buf .= $this->panelReviewStatus() .
              $this->panelActiveUsers() .
              $this->panelEnzymeUpdates();
    }

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array('/css/homeui.css');
  }


  private function panelReviewStatus() {
    $buf = '<h3>' . _('Review Status') . '</h3>';

    return $buf;
  }


  private function panelActiveUsers() {
    $buf = '<h3>' . _('Active Users') . '</h3>';

    // get currently active users
    $users = Track::getUsers(true);

    // draw data as a table
    $buf  .= '<table>
                <thead>
                  <tr>
                    <th>' . _('Username') . '</th>
                    <th>' . _('IP') . '</th>
                    <th>' . _('Page') . '</th>
                    <th>' . _('Time') . '</th>
                  </tr>
                </thead>

                <tbody>';

    foreach ($users as $username => $data) {
      $buf  .= '<tr>
                  <td>' . $username . '</td>
                  <td>' . $data['ip'] . '</td>
                  <td>' . $data['page'] . '</td>
                  <td>' . date('H:i:s', $data['time']) . '</td>
                </tr>';
    }

    $buf  .= '  </tbody>
              </table>';

    return $buf;
  }


  private function panelEnzymeUpdates() {
    $buf = '<h3>' . _('Enzyme Updates') . '</h3>';

    return $buf;
  }
}

?>