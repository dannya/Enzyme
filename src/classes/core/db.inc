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


class Db {
  private static $tables  = array('applications',
                                  'authors',
                                  'bugfixers',
                                  'commits',
                                  'commits_reviewed',
                                  'commits_bugs',
                                  'commits_files',
                                  'countries',
                                  'digests',
                                  'digest_intro_people',
                                  'digest_intro_sections',
                                  'digest_intro_videos',
                                  'digest_stats',
                                  'digest_stats_bugfixers',
                                  'digest_stats_buzz',
                                  'digest_stats_developers',
                                  'digest_stats_extended',
                                  'digest_stats_i18n',
                                  'digest_stats_modules',
                                  'filetypes',
                                  'languages',
                                  'people',
                                  'settings',
                                  'users');


  public static function connect() {
    // connect to database server
    mysql_pconnect(DB_HOST, DB_USER, DB_PASSWORD) or trigger_error(sprintf(_("Couldn't connect to database: ensure you have set the correct values at the top of %s/autoload.inc"), BASE_DIR));

    // select database
    $success = @mysql_select_db(DB_DATABASE);

    // ensure database communicates using utf8
    mysql_query('SET NAMES \'utf8\'');

    // return select database success
    return $success;
  }


  public static function create($databaseName = DB_DATABASE, $select = true) {
    // create the database
    $success = mysql_query('CREATE DATABASE ' . $databaseName) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    // switch to the newly-created database?
    if ($select) {
      return mysql_select_db($databaseName);

    } else {
      return $success;
    }
  }


  public static function sanitise($string, $default = null) {
    if (($default != null) && ($string == $default)) {
      // if string is same as default, do not run through sanitise
      return $string;
    }

    return mysql_real_escape_string($string);
  }


  public static function serialize($data) {
    return base64_encode(serialize($data));
  }


  public static function unserialize($data) {
    return unserialize(base64_decode($data));
  }


  public static function loadCache($key, $table, $filter, $limit = null, $fields = '*', $explode = true) {
    $data = self::load($table, $filter, $limit, $fields, $explode);

    return $data;
  }


  public static function load($table, $filter, $limit = null, $fields = '*', $explode = true, $order = null) {
    $data = null;

    // check if table is valid, and filter is provided
    if (($filter !== false) && count($filter) == 0) {
      return null;
    }

    if (is_array($table)) {
      foreach ($table as $theTable) {
        if (!in_array($theTable, self::$tables)) {
          return null;
        }
      }

      $table = implode(',', $table);

    } else if (!in_array($table, self::$tables)) {
      return null;
    }


    // create appropriate select query
    $selectQuery = 'SELECT ' . self::sanitise($fields, '*') . ' FROM ' . $table .
                   ' WHERE ' . self::createFilter($table, $filter);

    // order?
    if ($order) {
      $selectQuery .= ' ORDER BY ' . $order;
    }

    // limit?
    if (isset($limit)) {
      $selectQuery .= ' LIMIT ' . intval($limit);
    }


    // print debug SQL?
    if (isset($_REQUEST['debug'])) {
      echo $selectQuery;
    }


    // get and return data
    $query   = mysql_query($selectQuery) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));
    $numRows = mysql_num_rows($query);

    if ($numRows != 0) {
      while ($tmp = mysql_fetch_assoc($query)) {
        $data[] = $tmp;
      }

      // for convenience, explode data array if only one row
      if ($explode && ($numRows == 1)) {
        $data = reset($data);
      }
    }

    return $data;
  }


  public static function save($table, $filter, $values) {
    // check if table is valid, and filter is provided
    if (!in_array($table, self::$tables) || (count($filter) == 0)) {
      return null;
    }

    // create appropriate update query
    $updateQuery = 'UPDATE ' . $table . ' SET ' . self::createValues('update', $values) .
                   ' WHERE ' . self::createFilter($table, $filter);

    // save data
    return mysql_query($updateQuery) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));
  }


  public static function saveMulti($table, $values) {
    // check if table is valid, and filter is provided
    if (!in_array($table, self::$tables) || (count($values) == 0)) {
      return null;
    }

    // create query components
    $keys   = array_keys(reset($values));
    $fields = '(' . implode(', ', $keys) . ')';

    foreach ($keys as $key) {
      $update[] = $key . ' = VALUES(' . $key . ')';
    }

    // create appropriate update query
    $updateQuery = 'INSERT INTO ' . $table . ' ' . $fields . ' VALUES ' . self::createValues('updateMulti', $values) .
                   ' ON DUPLICATE KEY UPDATE ' . implode(', ', $update) . ';';

    // save data
    return mysql_query($updateQuery) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));
  }


  public static function insert($table, $values, $ignore = false, $delay = false, $execute = true) {
    if ($ignore) {
      $ignore = ' IGNORE';
    }
    if ($delay) {
      $delay = ' DELAYED';
    }

    // create values string
    if (isset($values[0])) {
      // numerically indexed, create multi string
      $values = self::createValuesMulti('insert', $values);
    } else {
      $values = self::createValues('insert', $values);
    }

    // create appropriate insert query
    $insertQuery = 'INSERT' . $delay . $ignore . ' INTO ' . $table . ' ' . $values . ';';

    if ($execute) {
      return mysql_query($insertQuery) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));
    } else {
      return $insertQuery;
    }
  }


  public static function delete($table, $values, $ignore = false, $delay = false) {
    if ($ignore) {
      $ignore = ' IGNORE';
    }
    if ($delay) {
      $delay = ' DELAYED';
    }

    // create values string
    $values = self::createValues('delete', $values);

    // create appropriate delete query
    $deleteQuery = 'DELETE' . $delay . $ignore . ' FROM ' . $table . ' WHERE ' . $values . ';';

    return mysql_query($deleteQuery) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));
  }


  public static function exists($table, $filter = false) {
    // check if table is valid, and filter is provided
    if (($filter !== false) && count($filter) == 0) {
      return null;
    }

    if (!in_array($table, self::$tables)) {
      return null;
    }


    // create appropriate select query
    $selectQuery = 'SELECT * FROM ' . $table .
                   ' WHERE ' . self::createFilter($table, $filter);

    $query = mysql_query($selectQuery) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    return (bool)mysql_num_rows($query);
  }


  public static function getNextId($table, $field = 'id') {
    // check if table is valid
    if (!in_array($table, self::$tables)) {
      return null;
    }

    $selectQuery  = 'SELECT MAX(' . $field .  ') + 1 FROM ' . $table;

    $query        = mysql_query($selectQuery) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    return array_pop(mysql_fetch_row($query));
  }


  private static function createFilter($table, $filter) {
    // load all?
    if ($filter === false) {
      return '1';
    }

    $query = array();

    foreach ($filter as $key => $tmpValue) {
      // check if filter is expressed as array
      if (is_array($tmpValue)) {
        // check if numerically-indexed array of values given (IN (...))
        if (isset($tmpValue[0])) {
          // sanitise (and quote?) all values first
          foreach ($tmpValue as &$value) {
            $value = self::quote($value);
          }

          $query[] = $key . ' IN (' . implode(',', $tmpValue) . ')';

        } else if ($tmpValue['type'] == 'range') {
          sort($tmpValue['args']);

          $query[] = $key . ' >= \'' . self::sanitise($tmpValue['args'][0]) . '\' AND ' . $key . ' <= \'' . self::sanitise($tmpValue['args'][1]) . '\'';
        }

      } else if ($tmpValue === true) {
        $query[] = $key . ' IS NOT NULL';

      } else if ($tmpValue === false) {
        $query[] = $key . ' IS NULL';

      } else {
        $value = self::sanitise($tmpValue);

        // add quotes?
        if (is_string($value) &&
            (($table == 'users') || (strpos($value, '.') === false))) {

          $value = self::quote($value);
        }

        $query[] = $key . ' = ' . $value;
      }
    }

    // join filters
    return implode(' AND ', $query);
  }


  private static function createValues($context, $values) {
    if (empty($values)) {
      trigger_error(_('Query failed'));
      return null;
    }

    // initialise
    if (($context == 'update') || ($context == 'updateMulti')) {
      $query      = null;
    } else if ($context == 'insert') {
      $theKeys    = null;
      $theValues  = null;
    }

    // iterate
    foreach ($values as $key => $tmpValue) {
      if (is_array($tmpValue)) {
        $value = array();

        foreach ($tmpValue as $tmp) {
          // add quotes?
          $value[] = self::quote($tmp);
        }

      } else {
        $value = self::quote($tmpValue);
      }


      if (($context == 'update') || ($context == 'delete')) {
        if (is_array($value)) {
          $query[] = $key . ' IN (' . implode(',', $value) . ')';
        } else {
          $query[] = $key . ' = ' . $value;
        }

      } else if ($context == 'insert') {
        $theKeys[]   = $key;
        $theValues[] = $value;

      } else if ($context == 'updateMulti') {
        $query[] = '(' . implode(',', $value) . ')';
      }
    }

    // return
    if (($context == 'update') || ($context == 'updateMulti')) {
      return implode(', ', $query);

    } else if ($context == 'delete') {
      return implode(' AND ', $query);

    } else if ($context == 'insert') {
      return '(' . implode(', ', $theKeys) . ') VALUES (' . implode(', ', $theValues) . ')';
    }
  }


  private static function createValuesMulti($context, $values) {
    $firstRow = reset($values);

    // compose keys
    $theKeys = array();

    foreach ($firstRow as $key => $tmpValue) {
      $theKeys[] = $key;
    }


    // compose values
    foreach ($values as $row) {
      // iterate
      $tmpValues = array();

      foreach ($row as $key => $tmpValue) {
        $tmpValues[] = self::quote($tmpValue);
      }

      // add to array
      $valuesArray[] = '(' . implode(', ', $tmpValues) . ')';
    }


    // return
    return '(' . implode(', ', $theKeys) . ') VALUES ' . implode(', ', $valuesArray);
  }


  public static function reindex($array, $key, $processKey = false, $overwrite = true) {
    $data = array();

    if (empty($array)) {
      return $data;
    }

    foreach ($array as $item) {
      if ($processKey) {
        $theKey = self::key($item[$key]);
      } else {
        $theKey = $item[$key];
      }

      // overwrite values?
      if ($overwrite) {
      $data[$theKey] = $item;
      } else {
        $data[$theKey][] = $item;
      }
    }

    return $data;
  }


  public static function key($key) {
    $pattern = array('/amp;/',
                     '/( *)/',
                     '/[^a-zA-Z0-9\s]/');
    $replace = array(null);

    return App::truncate(strtolower(preg_replace($pattern, $replace, $key)), 100);
  }


  public static function id() {
    $query = mysql_query('SELECT LAST_INSERT_ID();') or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));

    return reset(mysql_fetch_assoc($query));
  }


  public static function sql($sql, $index = false, $silentError = false) {
    $data  = array();

    // determine how to handle errors
    if ($silentError) {
      $query = mysql_query($sql);

      if (!$query) {
        return false;
      }

    } else {
      $query = mysql_query($sql) or trigger_error(sprintf(_('Query failed: %s'), mysql_error()));
    }


    // index and return data?
    if ($index) {
      if (mysql_num_rows($query) != 0) {
        while ($tmp = mysql_fetch_assoc($query)) {
          $data[] = $tmp;
        }
      }

      return $data;

    } else {
      return true;
    }
  }


  public static function getHash($string) {
    return hash('ripemd160', $string);
  }


  public static function quote($value, $sanitised = false) {
    // sanitise first?
    if (!$sanitised) {
      $value = self::sanitise($value);
    }

    if ($value === 'true') {
      return 1;
    } else if ($value === 'false') {
      return 0;
    } else if (is_numeric($value)) {
      return $value;
    } else if (is_string($value) && ($value != 'NOW()')) {
      return "'" . $value . "'";
    } else {
      return $value;
    }
  }
}

?>