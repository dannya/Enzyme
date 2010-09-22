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


class AdminUi {
  public $id      = 'admin';


  public function __construct() {
    $this->title = _('Admin');
  }


  public function draw() {
    $buf = '<h2>' . $this->title . '</h2>';

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