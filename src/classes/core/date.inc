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


class Date {
  public static function load($key = null) {
    if (!$key) {
      $key = date('Y-m');
    }

    // attempt to load time data from cache
    $time = Cache::load('time_' . $key);

    if (empty($time)) {
      // get needed times
      $time['year']                = date('Y');
      $time['today']               = date(DATE_FORMAT);

      // store in cache until end of day!
      $endDay = strtotime('Tomorrow') - time();
      Cache::store('_time_' . $key, $time, false, $endDay);
    }

    return $time;
  }


  public static function get($type, $date) {
    if ($type == 'full') {
      $format = 'jS F Y';
    } else if ($type == 'short') {
      $format = 'd/m/Y';
    }

    return date($format, strtotime($date));
  }


  public static function getInterval($rawDate1, $rawDate2 = null, $extended = false) {
    // get time
    $time = self::load();
    $now  = time();

    // process and set dates
    if (!is_int($rawDate1)) {
      $date1 = strtotime($rawDate1);
    } else {
      // timestamp given
      $date1 = $rawDate1;
    }

    if ($rawDate2) {
      if (!is_int($rawDate2)) {
        $date2 = strtotime($rawDate2);
      } else {
        // timestamp given
        $date2 = $rawDate2;
      }

    } else {
      $date2    = $now;
      $rawDate2 = $time['today'];
    }


    // determine past / future
    if ($rawDate1 == $rawDate2) {
      $past = false;
      $string = _('today');

    } else if ($date1 < $now) {
      // past
      $past = true;
      $string = _('%d days ago');

    } else {
      // future
      $past = false;
      $string = _('in %d days');
    }

    // do calculation
    $sum = $date1 - $date2;


    // return result
    if (!$extended) {
      return sprintf($string, abs($sum / 86400));
    } else {
      return array('past' => $past,
                   'string' => sprintf($string, abs($sum / 86400)));
    }
  }
}

?>