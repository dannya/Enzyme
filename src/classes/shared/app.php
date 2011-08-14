<?php

/*-------------------------------------------------------+
 | PHPzy (Web Application Framework)
 | Copyright 2010-2011 Danny Allen <me@dannya.com>
 | http://www.dannya.com/
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
    if (!Config::getSetting('locale', 'LANGUAGE')) {
      Config::setSetting(array('locale', 'LANGUAGE'), Config::$locale['language']);
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
      define('LANGUAGE', Config::$locale['language']);
    }


    // load language strings for set language
    putenv('LC_ALL=' . LANGUAGE);
    setlocale(LC_ALL, array(LANGUAGE . '.utf8', LANGUAGE . '.utf-8', LANGUAGE));
    bindtextdomain(Config::$app['id'], BASE_DIR . '/languages');
    textdomain(Config::$app['id']);
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


  public static function implode($delimiter, $content, $trim = true, $representNull = false) {
    if (!is_array($content)) {
      return $content;

    } else {
      if ($representNull) {
        // manually implode array so we can represent null elements with NULL
        $str    = null;

        $i      = 0;
        $total  = count($content);

        foreach ($content as $item) {
          if ($item === null) {
            $str .= 'null';
          } else {
            $str .= $item;
          }

          // add delimiter?
          if (++$i != $total) {
            $str .= $delimiter;
          }
        }

      } else {
        // use PHP implode
        $str = implode($delimiter, $content);
      }

      // return
      if (!$trim) {
        return $str;
      } else {
        return trim($str);
      }
    }
  }


  public static function returnHeaderJson($finish = true, $json = null) {
    if (!$json) {
      // use existing $json data
      global $json;
    }

    if (JAVASCRIPT_LIBRARY == 'prototype') {
      // prototype (send as header)
      header('X-JSON: ' . json_encode($json));

    } else {
      // jQuery
      echo json_encode($json);
    }

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


  public static function checkPermission($user, $permissions, $return = true) {
    $message = false;

    if (!is_array($permissions)) {
      // convert to array for iteration (only one element though)
      $permissions = array($permissions);
    }

    // check for required permission
    foreach ($permissions as $permission) {
      if (!$user->hasPermission($permission)) {
        $message = sprintf(_('You need to have the permission "%s" to view this section'), $permission);

      } else {
        // user has permission, stop check
        $message = null;
        break;
      }
    }

    // respond
    if ($message) {
      if ($return) {
        return $message;

      } else {
        echo $message;
        exit;
      }

    } else {
      return false;
    }
  }


  public static function getDirs($path, $sort = true, $includeSrc = false) {
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

    // append source directory to list?
    if ($includeSrc) {
      $dirs[] = rtrim($path, '/');
    }

    // sort?
    if ($sort) {
      sort($dirs);
    }

    return $dirs;
  }


  public static function getFiles($path, $extension = false, $sort = true) {
    if (!is_dir($path)) {
      return false;
    }

    $dirHandle = opendir($path);

    if (!$dirHandle) {
      return false;
    }

    // look in directory for other files
    $files = array();

    while ($fileName = readdir($dirHandle)) {
      if (($fileName != '.') && ($fileName != '..')) {
        $thePath = $path . $fileName;

        if (is_file($thePath)) {
          if ($extension && ($tmp = explode('.', $thePath)) && (array_pop($tmp) != $extension)) {
            continue;
          }

          $files[] = $thePath;
        }
      }
    }

    // sort?
    if ($sort) {
      sort($files);
    }

    return $files;
  }


  public static function getName($data) {
    if (isset($data['firstname']) && $data['lastname']) {
      return $data['firstname'] . ' ' . $data['lastname'];
    } else {
      return null;
    }
  }


  public static function splitCommaList($string) {
    return preg_split('/[\s,]+/', $string);
  }


  public static function combineCommaList($array) {
    return implode(', ', $array);
  }


  public static function addToCommaList($commaList, $value) {
    if (is_array($commaList)) {
      // array-based list...
      if (!isset($commaList[$value]) && !in_array($value, $commaList)) {
        // add value
        $commaList[] = $value;
      }

    } else {
      // string-based list...
    }

    return $commaList;
  }


  public static function removeFromCommaList($commaList, $value) {
    if (is_array($commaList)) {
      // array-based list...
      $commaList = array_flip($commaList);

      // remove value
      unset($commaList[$value]);

      $commaList = array_flip($commaList);

    } else {
      // string-based list...
    }

    return $commaList;
  }


  // adapted from http://www.php.net/manual/en/function.get-browser.php#92310
  public static function getBrowserInfo($agent = null, $getPlatform = false) {
    // Clean up agent and build regex that matches phrases for known browsers
    // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
    // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
    $agent    = strtolower($agent ? $agent : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''));
    $pattern  = '#(?<browser>chrome|msie|firefox|safari|webkit|opera|netscape|konqueror|gecko)[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

    // check if this is an iPhone/iPod (agent is in slightly different format)
    $iphone   = (strpos($agent, "iphone") !== false || strpos($agent, "ipod") !== false);

    // get platfrom details?
    $platform = false;

    if ($getPlatform) {
        // look for common platform identifiers
        preg_match('/windows|win32|macintosh|mac os x|linux/i', $agent, $platformMatches);
        $platformMatches = reset($platformMatches);

        if (($platformMatches == 'windows') || ($platformMatches == 'win32')) {
            $platform = 'windows';
        } else if (($platformMatches == 'macintosh') || ($platformMatches == 'mac os x')) {
            $platform = 'mac';
        } else if ($platformMatches == 'linux') {
            $platform = 'linux';
        }
    }

    // Find all phrases (or return empty array if none found)
    // single '&' operator is intentional!
    if (!$iphone & !preg_match_all($pattern, $agent, $matches)) {
      return array('name'         => '',
                   'version'      => 0,
                   'fullVersion'  => 0,
                   'rawVersion'   => 0,
                   'platform'     => $platform);
    }

    // determine agent
    if ($iphone) {
        // is iPhone / iPod
        return array('name'         => 'iphone',
                     'version'      => 0,
                     'fullVersion'  => 0,
                     'rawVersion'   => 0,
                     'platform'     => 'ios');

    } else {
      // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
      // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
      // in the UA). That's usually the most correct (use last-1 if chrome!).
      $numMatches = count($matches['browser']);

      if (isset($matches['browser'][$numMatches - 2]) &&
          ($matches['browser'][$numMatches - 2] == 'chrome')) {

        // special case chrome
        $i = $numMatches - 2;

      } else {
        $i = $numMatches - 1;
      }

      // return as name, version (major), full version (no trailing zeros), and [name] => [full version]
      return array('name'         => $matches['browser'][$i],
                   'version'      => (int)$matches['version'][$i],
                   'fullVersion'  => rtrim($matches['version'][$i], '.0'),
                   'rawVersion'   => $matches['version'][$i],
                   'platform'     => $platform);
    }
  }


  public static function getExtension($filename) {
    $tmp = explode('.', $filename);
    return strtolower(end($tmp));
  }


  public static function stripExtension($filename) {
    $tmp = explode('.', $filename);
    array_pop($tmp);

    return implode('.', $tmp);
  }
}

?>