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


class HelpUi extends BaseUi {
  public $id                    = 'help';

  private $user                 = array();


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Help');
  }


  public function draw() {
    // draw
    $buf = '<h3>
              <b>' .
                _('Help') .
           '  </b>

              <a class="button-back button n" href="#" onclick="helpBack(); return false;" title="' . _('Back') . '">
                &nbsp;
              </a>
              <a class="button-refresh button n" href="#" onclick="helpRefresh(); return false;" title="' . _('Refresh') . '">
                &nbsp;
              </a>
              <a class="button-home button n" href="#" onclick="helpHome(); return false;" title="' . _('Home') . '">
                &nbsp;
              </a>

              <span>' .
                sprintf(_('This help content is a Wiki: help improve it @ %s'), '<a href="' . HELP_URL . '" target="_blank">' . HELP_URL . '</a>') .
           '  </span>
            </h3>

            <iframe id="help-content" src="' . BASE_URL . '/get/help.php"></iframe>';

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/helpui.js');
  }


  public function getStyle() {
    return array('/css/helpui.css');
  }
}

?>