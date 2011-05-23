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


class Db {
  public static function __callStatic($name, $args) {
    try {
      return call_user_func_array(array('Db' . constant('DB_TYPE'), $name), $args);

    } catch (Exception $e) {
      throw new Exception('Invalid database method ' . $name);
    }
  }


  public static function key($key) {
    $pattern = array('/amp;/',
                     '/( *)/',
                     '/[^a-zA-Z0-9\s]/');
    $replace = array(null);

    return App::truncate(strtolower(preg_replace($pattern, $replace, $key)), 100);
  }


  public static function getHash($string) {
    return hash('ripemd160', $string);
  }


  public static function serialize($string) {
    return base64_encode(serialize($string));
  }


  public static function unserialize($string) {
    return unserialize(base64_decode($string));
  }
}

?>