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


class Cache {
  public static function exists($key) {
    return apc_exists(APP_ID . '_' . $key);
  }


  public static function load($key, $unserialize = false) {
    if ($unserialize) {
      return Db::unserialize(apc_fetch(APP_ID . '_' . $key));

    } else {
      return apc_fetch(APP_ID . '_' . $key);
    }
  }


  public static function loadSave($key, $function, $args = array(), $newData = null,
                                  $serialize = false, $ttl = 0) {

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


  public static function delete($key, $baseKey = APP_ID) {
    if (is_array($key)) {
      $success = true;

      foreach ($key as $theKey) {
        if ($baseKey === false) {
          // sometimes, we can only pass the full key name
          $tmpSuccess = apc_delete($theKey);
        } else {
          $tmpSuccess = apc_delete($baseKey . '_' . $theKey);
        }

        // report any failures
        if (!$tmpSuccess) {
          $success = false;
        }
      }

      return $success;

    } else {
      if ($baseKey === false) {
        // sometimes, we can only pass the full key name
        return apc_delete($key);
      } else {
        return apc_delete($baseKey . '_' . $key);
      }
    }
  }


  public static function deletePartial($key, $baseKey = APP_ID) {
    $deleted  = 0;
    $cache    = apc_cache_info('user');

    foreach ($cache['cache_list'] as $item) {
      if ((strpos($item['info'], $baseKey . '_') !== false) &&
          (strpos($item['info'], $key) !== false)) {

        // partial key found (in app namespace!), delete
        self::delete($item['info'], false);
        ++$deleted;
      }
    }

    return $deleted;
  }


  public static function store($key, $data, $serialize = false, $ttl = 0) {
    if (function_exists('apc_add')) {
      if ($serialize) {
        $data = Db::serialize($data);
      }

      return apc_add(APP_ID . '_' . $key, $data, $ttl);

    } else {
      return self::save($key, $data, $serialize, $ttl);
    }
  }


  public static function save($key, $data, $serialize = false, $ttl = 0) {
    if ($serialize) {
      $data = Db::serialize($data);
    }

    return apc_store(APP_ID . '_' . $key, $data, $ttl);
  }


  public static function getMinJs($key, $script, $minScript = null) {
    // output filename
    $filename = '/js/min/' . $key . '.js';

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

      // minify
      $min = MinifyJs::minify($buf);

      // append script already minified
      if (is_array($minScript) && $minScript) {
        foreach ($minScript as $file) {
          $min .= file_get_contents($base . $file) . "\n\n";
        }
      }

      // write to file
      file_put_contents(BASE_DIR . $filename, $min);

      // return filename
      return $filename . '?version=' . VERSION;

    } else {
      // already minified, return filename
      return $filename . '?version=' . VERSION;
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
}

?>