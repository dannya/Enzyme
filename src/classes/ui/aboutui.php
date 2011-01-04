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


class AboutUi {
  public $id    = 'about';
  public $title = null;


  public function __construct() {
    $this->title = _('About');
  }


  public function draw() {
    $buf = 'Enzyme<br />' .
            sprintf(_('Version %.2f'), VERSION) . '<br />
            Copyright 2010-2011 Danny Allen<br />
            <a href="http://enzyme-project.org/">http://enzyme-project.org/</a>';

    return $buf;
  }


  public function getScript() {
    return array();
  }


  public function getStyle() {
    return array();
  }
}

?>