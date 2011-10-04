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


abstract class DbMysql extends Db {
  public static function connect() {
    // connect to database server
    mysql_connect(Config::$db['host'], Config::$db['user'], Config::$db['password']) or trigger_error(sprintf('Could not connect to database: ensure you have set the correct values in %s/classes/specific/config.php', BASE_DIR));

    // select database
    $success = @mysql_select_db(Config::$db['database']);

    // ensure database communicates using utf8
    self::query('SET NAMES \'utf8\'', true);

    // return select database success
    return $success;
  }


  public static function create($databaseName = null, $select = true) {
    if (!$databaseName) {
      $databaseName = Config::$db['database'];
    }

    // create the database
    $success = self::query('CREATE DATABASE ' . self::sanitise($databaseName, Config::$db['database']));

    // switch to the newly-created database?
    if ($select) {
      return mysql_select_db($databaseName);

    } else {
      return $success;
    }
  }


  public static function getTables() {
    return Config::$db['tables'];
  }


  public static function getCreateSql($table) {
    // check specified table is valid
    if (!in_array($table, Config::$db['tables'])) {
      return null;
    }

    // get schema
    $query  = self::query('SHOW CREATE TABLE ' . $table);
    $schema = mysql_fetch_row($query);

    return array_pop($schema);
  }


  public static function getDataSql($table) {
    // check specified table is valid
    if (!in_array($table, Config::$db['tables'])) {
      return null;
    }

    // load data
    $values = self::load($table, false, null, '*', false);

    // fake insertion (don't execute) to get data SQL
    return self::insert($table, $values, false, false, false);
  }


  public static function sanitise($string = null, $default = null) {
    if ($string === null) {
      return null;
    }

    if (($default != null) && ($string == $default)) {
      // if string is same as default, do not run through sanitise
      return $string;
    }

    return mysql_real_escape_string($string);
  }


  public static function loadCache($key, $table, $filter, $limit = null, $fields = '*', $explode = true, $order = null) {
    if (!($data = Cache::load($key))) {
      $data = self::load($table, $filter, $limit, $fields, $explode, $order);
    }

    return $data;
  }


  public static function load($table, $filter, $limit = null, $fields = '*',
                              $explode = true, $order = null, $fillTable = null) {
    $data = null;

    // ensure table(s) is valid
    if (is_array($table)) {
      foreach ($table as $theTable) {
        if (!in_array($theTable, Config::$db['tables'])) {
          return null;
        }
      }

      $table = implode(',', $table);

    } else if (!in_array($table, Config::$db['tables'])) {
      return null;
    }

    // check if table is valid, and filter is provided
    if (($filter !== false) && count($filter) == 0) {
      return null;
    }


    // create appropriate select query
    $selectQuery = 'SELECT ' . self::sanitise($fields, '*') . ' FROM ' . $table .
                   ' WHERE ' . self::createFilter($table, $filter);

    // order?
    if ($order) {
      $selectQuery .= ' ORDER BY ' . self::sanitise($order);
    }

    // limit?
    if (isset($limit)) {
      if (is_array($limit)) {
        $selectQuery .= ' LIMIT ' . intval($limit[0]) . ' ,' . intval($limit[1]);
      } else {
        $selectQuery .= ' LIMIT ' . intval($limit);
      }
    }


    // load into, or process and return?
    if ($fillTable) {
      // check fill table is valid
      if (!in_array($fillTable, Config::$db['tables'])) {
        return false;
      }

      // prepend insert statement to select query
      $selectQuery = 'INSERT INTO ' . $fillTable . ' ' . $selectQuery;

      return self::query($selectQuery);


    } else {
      // get and return data
      $query   = self::query($selectQuery);
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
  }


  public static function count($table, $filter) {
    // ensure table is valid
    if (!in_array($table, Config::$db['tables'])) {
      return null;
    }

    // check if table is valid, and filter is provided
    if (($filter !== false) && count($filter) == 0) {
      return null;
    }

    // get and return count
    $tmp  = self::sql('SELECT COUNT(*) AS count FROM ' . $table .
                      ' WHERE ' . self::createFilter($table, $filter), true);

    return $tmp[0]['count'];
  }


  public static function save($table, $filter, $values, $silentError = false) {
    // check if table is valid, and filter is provided
    if (!in_array($table, Config::$db['tables']) || (count($filter) == 0)) {
      return null;
    }

    // create appropriate update query
    $updateQuery = 'UPDATE ' . $table . ' SET ' . self::createValues('update', $values) .
                   ' WHERE ' . self::createFilter($table, $filter);

    // save data
    return self::query($updateQuery, $silentError);
  }


  public static function saveMulti($table, $values) {
    // check if table is valid, and filter is provided
    if (!in_array($table, Config::$db['tables']) || (count($values) == 0)) {
      return null;
    }

    // create query components
    $keys   = array_keys(reset($values));
    $fields = '(' . implode(', ', $keys) . ')';

    foreach ($keys as $key) {
      $update[] = $key . ' = VALUES(' . $key . ')';
    }

    // create appropriate update query
    $updateQuery = 'INSERT INTO ' . $table . ' ' . self::sanitise($fields) . ' VALUES ' . self::createValues('updateMulti', $values) .
                   ' ON DUPLICATE KEY UPDATE ' . self::sanitise(implode(', ', $update)) . ';';

    // save data
    return self::query($updateQuery);
  }


  public static function saveSingleField($table, $filter, $values, $isEnum = false) {
    // check if table is valid, and filter is provided
    if (!in_array($table, Config::$db['tables']) || (count($filter) == 0) || (count($values) != 1)) {
      return null;
    }

    // create appropriate update query
    $updateQuery = 'UPDATE ' . $table . ' SET ' . self::createValues('update', $values, $isEnum) .
                   ' WHERE ' . self::createFilter($table, $filter);

    // save data
    return self::query($updateQuery);
  }


  public static function insert($table, $values, $ignore = false, $delay = false, $execute = true, $silentError = false) {
    // check if table is valid, and filter is provided
    if (!in_array($table, Config::$db['tables']) || (count($values) == 0)) {
      return null;
    }

    if ($ignore !== false) {
      $ignore = ' IGNORE';
    }
    if ($delay !== false) {
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
      return self::query($insertQuery, $silentError);

    } else {
      return $insertQuery;
    }
  }


  public static function clear($table) {
    // check if table is valid, and filter is provided
    if (!in_array($table, Config::$db['tables'])) {
      return null;
    }

    return self::query('TRUNCATE TABLE ' . $table);
  }


  public static function delete($table, $values, $ignore = false, $delay = false) {
    // check if table is valid, and filter is provided
    if (!in_array($table, Config::$db['tables']) || (count($values) == 0)) {
      return null;
    }

    if ($ignore !== false) {
      $ignore = ' IGNORE';
    }
    if ($delay !== false) {
      $delay = ' DELAYED';
    }

    // create values string
    $values = self::createValues('delete', $values);

    // create appropriate delete query
    $deleteQuery = 'DELETE' . $delay . $ignore . ' FROM ' . $table . ' WHERE ' . $values . ';';

    return self::query($deleteQuery);
  }


  public static function exists($table, $filter = false) {
    // check if table is valid, and filter is provided
    if (!in_array($table, Config::$db['tables'])) {
      return null;
    }

    if (($filter !== false) && count($filter) == 0) {
      return null;
    }


    // create appropriate select query
    $selectQuery = 'SELECT * FROM ' . $table .
                   ' WHERE ' . self::createFilter($table, $filter) .
                   ' LIMIT 1';

    $query = self::query($selectQuery);

    return (bool)mysql_num_rows($query);
  }


  public static function getNextId($table, $field = 'id') {
    // check if table is valid
    if (!in_array($table, Config::$db['tables'])) {
      return null;
    }

    $selectQuery  = 'SELECT MAX(' . self::sanitise($field) .  ') + 1 FROM ' . $table;

    $query        = self::query($selectQuery);

    return array_pop(mysql_fetch_row($query));
  }


  public static function createFilter($table, $filter) {
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

        } else if (isset($tmpValue['type'])) {
          if ($tmpValue['type'] == 'eq') {
            // equal to
            $query[] = self::createFilterElement($key, '=', $tmpValue['value']);

          } else if ($tmpValue['type'] == 'gt') {
            // greater than
            $query[] = self::createFilterElement($key, '>', $tmpValue['value']);

          } else if ($tmpValue['type'] == 'gte') {
            // greater than or equal to
            $query[] = self::createFilterElement($key, '>=', $tmpValue['value']);

          } else if ($tmpValue['type'] == 'lt') {
            // lower than
            $query[] = self::createFilterElement($key, '<', $tmpValue['value']);

          } else if ($tmpValue['type'] == 'lte') {
            // lower than or equal to
            $query[] = self::createFilterElement($key, '<=', $tmpValue['value']);

          } else if ($tmpValue['type'] == '!=') {
            // not equal to
            $query[] = self::createFilterElement($key, '!=', $tmpValue['value']);

          } else if ($tmpValue['type'] == 'range') {
            // between two values
            sort($tmpValue['args']);

            $query[] = $key . ' >= ' . self::quote($tmpValue['args'][0]) . ' AND ' . $key . ' <= ' . self::quote($tmpValue['args'][1]);

          } else if ($tmpValue['type'] == 'start') {
            // starts with
            $query[] = self::createFilterElement($key, 'LIKE', self::prepareLike($tmpValue['value']) . '%');

          } else if ($tmpValue['type'] == 'end') {
            // ends with
            $query[] = self::createFilterElement($key, 'LIKE', '%' . self::prepareLike($tmpValue['value']));

          } else if ($tmpValue['type'] == 'contain') {
            // contains with
            $query[] = self::createFilterElement($key, 'LIKE', '%' . self::prepareLike($tmpValue['value']) . '%');

          } else {
            // invalid type provided
            throw new Exception('Invalid type provided to Db::createFilter()');
          }

        } else {
          return false;
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


  private static function createFilterElement($key, $operator, $value) {
    if (is_array($value)) {
      $filter = array();

      foreach ($value as $v) {
        $filter[] = self::sanitise($key) . ' ' . self::sanitise($operator) . ' ' . self::quote($v);
      }

      return implode(' AND ', $filter);

    } else {
      // single element
      return self::sanitise($key) . ' ' . self::sanitise($operator) . ' ' . self::quote($value);
    }

    return $buf;
  }


  private static function createValues($context, $values, $isEnum = false) {
    if (empty($values) || !is_array($values)) {
      trigger_error('Query failed');
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
      if ($tmpValue === null) {
        $value = null;

      } else {
        if (is_array($tmpValue)) {
          $value = array();

          foreach ($tmpValue as $tmp) {
            // add quotes?
            $value[] = self::quote($tmp, false, $isEnum);
          }

        } else {
          $value = self::quote($tmpValue, false, $isEnum);
        }
      }


      if (($context == 'update') || ($context == 'delete')) {
        if ($value === null) {
          $query[] = $key . ' = NULL';
        } else if (is_array($value)) {
          $query[] = $key . ' IN (' . implode(',', $value) . ')';
        } else {
          $query[] = $key . ' = ' . $value;
        }

      } else if ($context == 'insert') {
        $theKeys[]   = $key;

        if ($value === null) {
          $theValues[] = 'NULL';
        } else {
        $theValues[] = $value;
        }

      } else if ($context == 'updateMulti') {
        $query[] = '(' . App::implode(',', $value, false, true) . ')';
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


  private static function createValuesMulti($context, $values, $isEnum = false) {
    if (empty($values) || !is_array($values)) {
      trigger_error('Query failed');
      return null;
    }

    // get first row to determine structure of values
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
        $value = self::quote($tmpValue, false, $isEnum);

        // set as null?
        if ($value === null) {
          $tmpValues[] = 'NULL';
        } else {
          $tmpValues[] = $value;
        }
      }

      // add to array
      $valuesArray[] = '(' . implode(', ', $tmpValues) . ')';
    }


    // return
    return '(`' . implode('`, `', $theKeys) . '`) VALUES ' . implode(', ', $valuesArray);
  }


  public static function reindex($array, $key, $processKey = false, $overwrite = true) {
    $data = array();

    if (empty($array)) {
      return $data;
    }

    foreach ($array as $item) {
      if ($processKey) {
        if (is_string($processKey)) {
          // use specified function to process key
          $theKey = call_user_func($processKey, $item[$key]);

        } else {
          // use standard key processing function
        $theKey = self::key($item[$key]);
        }

      } else {
        $theKey = $item[$key];
      }


      // overwrite values?
      if ($overwrite === true) {
        // overwrite values on 2nd level
        $data[$theKey] = $item;

      } else if (is_string($overwrite)) {
        // write values on 2nd level using specific key name for key
        $data[$theKey][$item[$overwrite]] = $item;

      } else {
        // write values on 2nd level using auto numeric keys
        $data[$theKey][] = $item;
      }
    }

    return $data;
  }


  public static function id() {
    $query = self::query('SELECT LAST_INSERT_ID();');

    return reset(mysql_fetch_assoc($query));
  }


  public static function sql($sql, $index = false, $silentError = false) {
    $data  = array();

    // determine how to handle errors
    if ($silentError) {
      $query = self::query($sql, true);

      if (!$query) {
        return false;
      }

    } else {
      $query = self::query($sql);
    }


    // index and return data?
    if ($index) {
      if (mysql_num_rows($query) != 0) {
        while ($tmp = mysql_fetch_assoc($query)) {
          if ($index === true) {
            $data[] = $tmp;

          } else {
            // index by specific field
            $data[$tmp[$index]] = $tmp;
          }
        }
      }

      return $data;

    } else {
      return true;
    }
  }


  public static function loadEfficient($table, $fields = '*', $idField = 'id', $pageSize = 10000) {
    $data = null;

    // ensure table(s) is valid
    if (is_array($table)) {
      foreach ($table as $theTable) {
        if (!in_array($theTable, Config::$db['tables'])) {
          return null;
        }
      }

      $table = implode(',', $table);

    } else if (!in_array($table, Config::$db['tables'])) {
      return null;
    }


    // sanitise
    $idField = self::sanitise($idField, 'id');


    // get min / max boundaries
    $boundaries = Db::sql('SELECT MIN(' . $idField . ') AS min, MAX(' . $idField . ') AS max FROM ' . $table,
                          true);
    $boundaries = reset($boundaries);


    // execute queries
    $i = $boundaries['min'];

    while ($i < $boundaries['max']) {
      $start  = $i;
      $end    = $i + $pageSize;

      // create appropriate select query
      $selectQuery = 'SELECT ' . self::sanitise($fields, '*') . ' FROM ' . $table .
                     ' WHERE ' . $idField . ' >= ' . $start . ' AND ' . $idField . ' < ' . $end;

      // run query
      $query = self::query($selectQuery);

      // index data
      if (mysql_num_rows($query) != 0) {
        while ($tmp = mysql_fetch_assoc($query)) {
          $data[] = $tmp;
        }
      }

      unset($query);
      $i += $pageSize;
    }

    // return data
    return $data;
  }


  public static function quote($value, $sanitised = false, $isEnum = false) {
    // sanitise first?
    if (!$sanitised) {
      $value = self::sanitise($value);
    }

    if ($value === 'true') {
      return 1;
    } else if ($value === 'false') {
      return 0;
    } else if (is_numeric($value) && !$isEnum) {
      return $value;
    } else if ($isEnum || (is_string($value) && ($value != 'NOW()'))) {
      return "'" . $value . "'";
    } else {
      return $value;
    }
  }


  public static function convertDatatype($value, $nullToString = false) {
    if ($value === 'Y') {
      return true;
    } else if ($value === 'N') {
      return false;

    } else {
      if ($nullToString && ($value === null)) {
        return 'null';
      } else {
        return $value;
      }
    }
  }


  public static function prepareLike($value) {
    // remove & and _
    return str_replace(array('%', '_'), null, $value);
  }


  public static function objectify($class, $data) {
    if (!$data) {
      return $data;
    }

    if (isset($data[0])) {
      // numerically-indexed, create list of objects
      $list = array();

      foreach ($data as $item) {
         $list[] = new $class($item);
      }

      return $list;

    } else {
      // create single object
      return new $class($data);
    }
  }


  private static function query($sql, $silentError = false) {
    // print debug SQL?
    if (isset($_REQUEST['debug'])) {
      echo $sql . "\n<br />";
    }

    // run query silently?
    if ($silentError) {
      return mysql_query($sql);

    } else {
      $result = mysql_query($sql);

      if (!$result) {
        trigger_error(sprintf('Query failed: %s', mysql_error()));
      }

      return $result;
    }
  }
}

?>