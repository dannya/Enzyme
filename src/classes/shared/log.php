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


class Log {
  public static function error($error, $userData = false) {
    $backtrace  = debug_backtrace();
    $source     = reset($backtrace);

    // set error details
    $data['file']       = $source['file'];
    $data['line']       = $source['line'];
    $data['page']       = $_SERVER['SCRIPT_NAME'];
    $data['ip']         = $_SERVER['REMOTE_ADDR'];
    $data['browser']    = $_SERVER['HTTP_USER_AGENT'];
    $data['string']     = $error;
    $data['backtrace']  = Db::serialize($backtrace);

    // add user data?
    if ($userData && class_exists('User')) {
      if ($userData === true) {
        // try and load user data
        $userData = new User();
      }

      $data['user'] = Db::serialize($userData);
    }

    // insert into errors table
    Db::insert('errors', $data);
  }
}

?>