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


abstract class Object {
  protected $data = array();


  public function __construct($data) {
    $this->data = $data;
  }


  public function __get($key) {
    return $this->data[$key];
  }


  public function __isset($key) {
    return isset($this->data[$key]);
  }
}

?>