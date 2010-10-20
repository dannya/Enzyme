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
    $buf = '<h3>' .
              _('Help') .
           '  <span>' .
                sprintf(_('This help content is a Wiki: help improve it @ %s'), '<a href="' . HELP_URL . '" target="_blank">' . HELP_URL . '</a>') .
           '  </span>
            </h3>

            <iframe src="' . BASE_URL . '/get/help.php"></iframe>';

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array('/css/helpui.css');
  }
}

?>