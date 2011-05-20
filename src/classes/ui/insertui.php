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


class InsertUi extends BaseUi {
  public $id      = 'insert';

  private $user   = null;


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Insert');
  }


  public function draw() {
    // check permission (and insert is set to show)
    if ($buf = App::checkPermission($this->user, 'editor') ||
        (defined('SHOW_INSERT') && !SHOW_INSERT)) {

      return $buf;
    }

    // suggest current dates
    if (empty($_POST['start'])) {
      $start = date('Y-m-d', strtotime('last Sunday -1 week'));
    } else {
      $start = $_POST['start'];
    }

    if (empty($_POST['end'])) {
      $end = date('Y-m-d', strtotime('last Sunday'));
    } else {
      $end = $_POST['end'];
    }

    if (!empty($_POST['show_skipped'])) {
      $skip = ' checked="checked"';
    } else {
      $skip = null;
    }

    // draw settings console
    $buf = '<div id="console">
              <form id="settings" name="settings" method="post" action="">
                <label>' .
                  _('Start') . ' <input id="start" type="text" value="' . $start . '" />
                </label>
                <label>' .
                  _('End') . ' <input id="end" type="text" value="' . $end . '" />
                </label>
                <label>
                  <input id="show-skipped" type="checkbox" value="1"' . $skip . ' /> ' . _('Show Skipped?') .
           '    </label>

                <input type="submit" value="' . _('Insert commits') . '" title="' . _('Insert commits') . '" onclick="insertCommits(event);" />
              </form>
            </div>

            <iframe id="result" src="' . BASE_URL . '/get/prompt.php?language=' . LANGUAGE . '"></iframe>';

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/insertui.js');
  }


  public function getStyle() {
    return array('/css/insertui.css');
  }
}

?>