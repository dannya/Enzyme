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
      $buf  .= '<div id="column-left">' .
                  $this->panelReviewStatus() .
                  $this->panelMyStats() .
                  $this->panelLeaderBoard() .
               '</div>

                <div id="column-right">' .
                  $this->panelEnzymeUpdates() .
                  $this->panelActiveUsers() .
               '</div>';
    }

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/homeui.js');
  }


  public function getStyle() {
    return array('/css/homeui.css');
  }


  private function panelReviewStatus() {
    $buf = '<h3>' . _('Review Status') . '</h3>

            <div class="container r">
            </div>';

    //return $buf;
  }


  private function panelMyStats() {
    // get participation stats
    $stats = $this->user->getStats();


    // draw
    $buf = '<h3>' . _('My Stats') . '</h3>

            <div class="container r">
              <table class="display">
                <thead>
                  <tr>
                    <th>&nbsp;</th>
                    <th>' . _('Past Week') . '</th>
                    <th>' . _('Total') . '</th>
                  </tr>
                </thead>

                <tbody>
                  <tr>
                    <td class="label">' . _('Reviewed') . '</td>
                    <td>' . $stats['reviewed']['week'] . '</td>
                    <td>' . $stats['reviewed']['total'] . '</td>
                  </tr>
                  <tr>
                    <td class="label">' . _('Classified') . '</td>
                    <td>' . $stats['classified']['week'] . '</td>
                    <td>' . $stats['classified']['total'] . '</td>
                  </tr>
                </tbody>
              </table>
            </div>';

    return $buf;
  }


  private function panelLeaderBoard() {
    // get participation stats
    $stats = Enzyme::getParticipationStats();


    // draw
    $buf = '<h3>' . _('Leaderboard') . '</h3>

            <div class="container r">
              <table class="display">
                <thead>
                  <tr>
                    <th>' . _('Username') . '</th>
                    <th>' . _('Reviewed (Week)') . '</th>
                    <th>' . _('Reviewed (Total)') . '</th>
                    <th>' . _('Classified (Week)') . '</th>
                    <th>' . _('Classified (Total)') . '</th>
                  </tr>
                </thead>

                <tbody>';

    foreach ($stats as $person => $row) {
      $buf  .= '<tr>
                  <td>' . $person . '</td>
                  <td>' . $row['reviewed']['week'] . '</td>
                  <td>' . $row['reviewed']['total'] . '</td>
                  <td>' . $row['classified']['week'] . '</td>
                  <td>' . $row['classified']['total'] . '</td>
                </tr>';
    }

    $buf  .= '  </tbody>
              </table>
            </div>';

    return $buf;
  }


  private function panelActiveUsers() {
    // get currently active users
    $users = Track::getUsers(true);


    // draw
    $buf = '<h3>' . _('Active Users') . '</h3>

            <div class="container r">
              <table class="display">
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

    $buf  .= '    </tbody>
                </table>
              </div>';

    return $buf;
  }


  private function panelEnzymeUpdates() {
      // check for updates
    $updates = json_decode(Cache::loadSave('updates', 'file_get_contents', array('http://enzyme-project.org/get/update.php?project=' . urlencode(PROJECT_NAME) . '&version=' . VERSION . '&url=' . BASE_URL)));

    // set message
    if (!empty($updates->available)) {
      // process changelog
      $changelog = explode('*', $updates->releases[0]->description);

      foreach ($changelog as $key => &$item) {
        if (empty($item)) {
          unset($changelog[$key]);
        } else {
          $item = trim($item);
        }
      }

      $msg = sprintf(_('There is an update for Enzyme available, version %.2f (released %s).'), $updates->releases[0]->version, Date::ago($updates->releases[0]->date)) . '<br /><br />' .
                     '<a href="#" onclick="changelog(event);">' . _('Changelog') . '</a> | <a href="' . $updates->releases[0]->download . '">' . _('Download') . '</a>' .

             '<div id="changelog" style="display:none;">
                <ul>
                  <li>' .
                    implode('</li><li>', $changelog) .
             '    </li>
                </ul>
              </div>';

    } else {
      $msg = sprintf(_('You are running the latest version of Enzyme (version %.2f).'), VERSION);
    }


    // draw
    $buf = '<h3>' . _('Enzyme Updates') . '</h3>

            <div class="container r">' .
              $msg .
           '</div>';

    return $buf;
  }
}

?>