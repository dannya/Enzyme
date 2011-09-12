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


class HomeUi extends BaseUi {
  public $id      = 'home';

  private $user   = null;


  public function __construct($user) {
    $this->user = $user;

    // set title
    $this->title = _('Home');
  }


  public function draw() {
    // setup panels manager
    $panels = new Panels($this->user);

    // let panels manager handle display and layout
    $buf = $panels->drawLayout();

    if (empty($buf)) {
      // if no panels found, show generic welcome message
      $buf = '<h3>' . _('Welcome to Enzyme!') . '</h3>';
    }

    return $buf;
  }


  public function getScript() {
    return array('/js/frame/homeui.js');
  }


  public function getStyle() {
    return array('/css/frame/homeui.css');
  }
}

?>