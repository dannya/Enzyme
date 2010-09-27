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


class App {
  public static function setLanguage($language = null) {
    if (defined('LANGUAGE')) {
      return false;
    }

    // set language
    if (isset($_GET['language'])) {
      // load language from query string
      define('LANGUAGE', $_REQUEST['language']);

      // store in session
      $_SESSION['language'] = LANGUAGE;

    } else if (isset($_SESSION['language'])) {
      // load language from cookie
      define('LANGUAGE', $_SESSION['language']);

    } else if ($language) {
      // load language from function invocation
      define('LANGUAGE', $language);

    } else {
      // use default language
      define('LANGUAGE', DEFAULT_LANGUAGE);
    }


    // load language strings for set language
    putenv('LC_ALL=' . LANGUAGE);
    setlocale(LC_ALL, LANGUAGE . '.utf8');
    bindtextdomain('messages', BASE_DIR . '/languages');
    textdomain('messages');
  }


  public static function truncate($string, $numChars, $addEllipsis = false) {
    if (strlen($string) <= $numChars) {
      return $string;
    }

    $buf = substr($string, 0, $numChars);

    if ($addEllipsis) {
      $buf .= '...';
    }

    return $buf;
  }


  public static function implode($delimiter, $content, $trim = true) {
    if (!is_array($content)) {
      return $content;
    } else {
      if ($trim) {
        return trim(implode($delimiter, $content));
      } else {
        return implode($delimiter, $content);
      }
    }
  }


  public static function returnHeaderJson($finish = true, $json = null) {
    if (!$json) {
      // use existing $json data
      global $json;
    }

    header('X-JSON: (' . json_encode($json) . ')');

    if ($finish) {
      exit;
    }
  }


  public static function randomString($length = 8) {
    // available characters (33) to construct random string (alphanumeric)
    $chars = 'abcdefghijkmnopqrstuvwxyz023456789';

    // seed random numbers
    srand();

    $buf = null;
    for ($i = 0; $i < $length; $i++) {
      $buf .= $chars[rand() % 33];
    }

    return $buf;
  }


  public static function flipNumber($num){
    if (!is_numeric($num)) {
      return false;
    }

    return (0 - $num);
  }


  public static function checkPermission($user, $permission, $return = true) {
    if (!$user->hasPermission($permission)) {
      $message = sprintf(_('You need to have the permission "%s" to view this section'), $permission);

      if ($return) {
        return $message;

      } else {
        echo $message;
        exit;
      }
    }
  }


  public static function getDirs($path, $sort = true) {
    if (!is_dir($path)) {
      return false;
    }

    $dirHandle = opendir($path);

    if (!$dirHandle) {
      return false;
    }

    // look in directory for other dirs
    $dirs = array();

    while ($fileName = readdir($dirHandle)) {
      if (($fileName != '.') && ($fileName != '..')) {
        $thePath = $path . $fileName;

        if (is_dir($thePath)) {
          $dirs[] = $thePath;
        }
      }
    }

    // sort?
    if ($sort) {
      sort($dirs);
    }

    return $dirs;
  }
}

?>