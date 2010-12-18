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


class Panels {
  public $panels  = array();

  private $user   = null;


  public function __construct($user) {
    // set user object
    $this->user = $user;


    // define panels, permissions
    $this->panels['review-status']    = array('title'       => _('Review Status'),
                                              'content'     => 'reviewStatus',
                                              'column'      => 'left',
                                              'permissions' => array('admin'));

    $this->panels['my-stats']         = array('title'       => _('My Stats'),
                                              'content'     => 'myStats',
                                              'column'      => 'left',
                                              'permissions' => array('reviewer', 'classifier', 'admin'));

    $this->panels['leader-board']     = array('title'       => _('Leaderboard'),
                                              'content'     => 'leaderBoard',
                                              'column'      => 'left',
                                              'permissions' => array('admin'));

    $this->panels['enzyme-updates']   = array('title'       => _('Enzyme Updates'),
                                              'content'     => 'enzymeUpdates',
                                              'column'      => 'right',
                                              'permissions' => array('admin'));

    $this->panels['active-users']     = array('title'       => _('Active Users'),
                                              'content'     => 'activeUsers',
                                              'column'      => 'right',
                                              'permissions' => array('admin'));
  }


  public function draw($id, $drawContainer = true) {
    // check that we have panel content
    if (!isset($this->panels[$id]['content'])) {
      return null;

    } else {
      // check we have correct permissions
      $hasPermission = false;

      foreach ($this->panels[$id]['permissions'] as $permission) {
        if ($this->user->hasPermission($permission)) {
          $hasPermission = true;
          break;
        }
      }

      if (!$hasPermission) {
        return null;
      }
    }

    // check that we have content to display
    $panel = $this->panels[$id]['content'];
    if (!$content = $this->$panel()){
      return null;
    }

    // draw
    if ($drawContainer) {
      $buf = '<h3>' .
                $this->panels[$id]['title'] .
             '  <a class="button-refresh button n" href="#" onclick="panelRefresh(\'' . $id . '\');" title="' . _('Refresh') . '">
                  &nbsp;
                </a>
              </h3>

              <div id="panel-' . $id . '" class="container r">' .
                $content .
             '</div>';

      return $buf;

    } else {
      // don't draw container
      return $content;
    }
  }


  public function drawLayout() {
    // iterate through panels, determine whether to draw
    $buf    = null;
    $left   = null;
    $right  = null;

    foreach ($this->panels as $id => $panel) {
      //print_r($panel);
      if ($panel['column'] == 'left') {
        $left .= $this->draw($id);
      } else if ($panel['column'] == 'right') {
        $right .= $this->draw($id);
      }
    }


    // combine into a layout
    if ($left) {
      $buf  .= '<div id="column-left">' .
                  $left .
               '</div>';
    }
    if ($right) {
      $buf  .= '<div id="column-right">' .
                  $right .
               '</div>';
    }

    return $buf;
  }


  private function reviewStatus() {
    $buf = '';

    return $buf;
  }


  private function myStats() {
    // get participation stats
    $stats = $this->user->getStats();


    // draw
    $buf = '<table class="display">
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
            </table>';

    return $buf;
  }


  private function leaderBoard() {
    // get participation stats
    $stats = Enzyme::getParticipationStats();


    // draw
    $buf = '<table class="display">
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
              </table>';

    return $buf;
  }


  private function activeUsers() {
    // get currently active users
    $users = Track::getUsers(true);


    // draw
    $buf = '<table class="display">
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


  private function enzymeUpdates() {
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

      $buf = sprintf(_('There is an update for Enzyme available, version %.2f (released %s).'), $updates->releases[0]->version, Date::ago($updates->releases[0]->date)) . '<br /><br />' .
                     '<a href="#" onclick="changelog(event);">' . _('Changelog') . '</a> | <a href="' . $updates->releases[0]->download . '">' . _('Download') . '</a>' .

             '<div id="changelog" style="display:none;">
                <ul>
                  <li>' .
                    implode('</li><li>', $changelog) .
             '    </li>
                </ul>
              </div>';

    } else {
      $buf = sprintf(_('You are running the latest version of Enzyme (version %.2f).'), VERSION);
    }


    // draw
    return $buf;
  }
}

?>