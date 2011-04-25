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


class Track {
  // session timeout in minutes
  const timeout = 180;


  public static function user($username) {
    // only track useful pages
    if (strpos($_SERVER['SCRIPT_NAME'], '/get/') !== false) {
      return false;
    }

    // set important tracking information
    $data = array('time'    => time(),
                  'page'    => $_SERVER['SCRIPT_NAME'],
                  'ip'      => $_SERVER['REMOTE_ADDR'],
                  'browser' => $_SERVER['HTTP_USER_AGENT']);

    // attempt to load existing tracking information from cache
    $users = Cache::load('users');

    // set data
    $users[$username] = $data;

    // save changes
    return Cache::save('users', $users);
  }


  public static function getUsers($discard = true, $save = false) {
    $users = Cache::load('users');

    // discard timed out sessions?
    if ($discard) {
      $now = time();

      foreach ($users as $username => $data) {
        if (($data['time'] + self::timeout) < $now) {
          // session has timed out, discard
          unset($users[$username]);
        }
      }

      // save changes?
      if ($save) {
        Cache::save('users', $users);
      }
    }

    return $users;
  }
}

?>