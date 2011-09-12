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


class Cache {
  public static function exists($key) {
    self::getKey($key);

    return apc_exists($key['full']);
  }


  public static function load($key, $unserialize = false) {
    self::getKey($key);

    if ($unserialize) {
      return Db::unserialize(apc_fetch($key['full']));

    } else {
      return apc_fetch($key['full']);
    }
  }


  public static function loadSave($key, $function, $args = array(), $newData = null,
                                  $serialize = false, $ttl = 0) {

    self::getKey($key);

    // attempt to load data
    $data = self::load($key, $serialize);

    if (!empty($data)) {
      return $data;

    } else {
      // data doesn't exist in cache...
      if (!$newData) {
        // call function to get data
        $newData = call_user_func_array($function, $args);
      }

      // store data in cache
      self::store($key, $newData, $serialize, $ttl);

      return $newData;
    }
  }


  public static function delete($key) {
    self::getKey($key);

    if (is_array($key['id'])) {
      $success = true;

      foreach ($key['id'] as $theKey) {
        if ($key['base'] === false) {
          // sometimes, we can only pass the full key name
          $tmpSuccess = apc_delete($theKey);
        } else {
          $tmpSuccess = apc_delete($key['base'] . '_' . $theKey);
        }

        // report any failures
        if (!$tmpSuccess) {
          $success = false;
        }
      }

      return $success;

    } else {
      if ($key['base'] === false) {
        // sometimes, we can only pass the full key name
        return apc_delete($key['id']);

      } else {
        return apc_delete($key['full']);
      }
    }
  }


  public static function deletePartial($key) {
    self::getKey($key);

    $deleted  = 0;
    $cache    = apc_cache_info('user');

    foreach ($cache['cache_list'] as $item) {
      if ((strpos($item['info'], $key['base'] . '_') !== false) &&
          (strpos($item['info'], $key['id']) !== false)) {

        // partial key found (in app namespace!), delete
        self::delete($item['info'], false);
        ++$deleted;
      }
    }

    return $deleted;
  }


  public static function store($key, $data, $serialize = false, $ttl = 0) {
    self::getKey($key);

    if (function_exists('apc_add')) {
      if ($serialize) {
        $data = Db::serialize($data);
      }

      return apc_add($key['full'], $data, $ttl);

    } else {
      return self::save($key, $data, $serialize, $ttl);
    }
  }


  public static function save($key, $data, $serialize = false, $ttl = 0) {
    self::getKey($key);

    if ($serialize) {
      $data = Db::serialize($data);
    }

    return apc_store($key['full'], $data, $ttl);
  }


  public static function getMinJs($key, $script, $minScript = null, $minify = true) {
    self::getKey($key);

    // output filename
    $filename = '/js/min/' . $key['id'] . '.js';

    if (!is_file(BASE_DIR . $filename)) {
      // minify script
      $buf = null;

      // combine all script into single file
      foreach ($script as $file) {
        // file or url?
        if (strpos($file, '=') !== false) {
          $base = BASE_URL;
        } else {
          $base = BASE_DIR;
        }

        $buf .= file_get_contents($base . $file) . "\n\n";
      }

      // minify?
      if ($minify) {
      $min = MinifyJs::minify($buf);
      } else {
        $min = $buf;
      }

      // append script already minified
      if (is_array($minScript) && $minScript) {
        foreach ($minScript as $file) {
          $min .= file_get_contents($base . $file) . "\n\n";
        }
      }

      // write to file
      file_put_contents(BASE_DIR . $filename, $min);

      // return filename
      return $filename . '?version=' . Config::$app['version'];

    } else {
      // already minified, return filename
      return $filename . '?version=' . Config::$app['version'];
    }
  }


  public static function getMinInlineJs($script, $cacheKey = null) {
    if (!LIVE_SITE) {
      return $script;
    }

    // look in cache
    if ($cacheKey) {
      $cacheKey  = $cacheKey;
      $min       = self::load('script_' . $cacheKey);
    } else {
      $min = null;
    }

    if (!$min) {
      // minify
      $min = MinifyJs::minify($script);

      // store in cache?
      if ($cacheKey) {
        self::store('script_' . $cacheKey, $min);
      }
    }

    return $min;
  }


  public static function getMinCss($key, $style, $minStyle = null, $minify = true) {
    self::getKey($key);

    // output filename
    $filename = '/css/min/' . $key['id'] . '.css';

    if (!is_file(BASE_DIR . $filename)) {
      // minify style
      $buf = null;

      // combine all style into single file
      foreach ($style as $file) {
        // file or url?
        if (strpos($file, '=') !== false) {
          $base = BASE_URL;
        } else {
          $base = BASE_DIR;
        }

        $buf .= file_get_contents($base . $file) . "\n\n";
      }

      // minify?
      if ($minify) {
        $min = MinifyCss::minify($buf);

      } else {
        $min = $buf;
      }

      // append style already minified
      if (is_array($minStyle) && $minStyle) {
        foreach ($minStyle as $file) {
          $min .= file_get_contents($base . $file) . "\n\n";
        }
      }

      // write to file
      file_put_contents(BASE_DIR . $filename, $min);
    }

    return $filename . '?version=' . Config::$app['version'];
  }


  private static function getKey(&$key) {
    if (is_array($key)) {
      if (isset($key['base']) || isset($key['id'])) {
        // already set, return
        $key['full'] = $key['base'] . '_' . $key['id'];
        return true;
      }

      $tmp['base']  = $key[0];
      $tmp['id']    = $key[1];

    } else {
      $tmp['base']  = Config::$app['id'];
      $tmp['id']    = $key;
    }

    $tmp['full'] = $tmp['base'] . '_' . $tmp['id'];

    // set
    $key = $tmp;
  }
}

?>