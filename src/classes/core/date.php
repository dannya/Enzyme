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


class Date {
  public static function get($type, $date) {
    if ($type == 'full') {
      $format = 'jS F Y';
    } else if ($type == 'short') {
      $format = 'd/m/Y';
    }

    return date($format, strtotime($date));
  }


  public static function ago($timestamp, $comparison = null) {
    // set periods and lengths of the periods
    $periods  = array(_('%d seconds ago'),
                      _('%d minutes ago'),
                      _('%d hours ago'),
                      _('%d days ago'),
                      _('%d weeks ago'),
                      _('%d months ago'),
                      _('%d years ago'));

    $lengths  = array(60, 60, 24, 7, 4.35, 12);

    // process input?
    if (!is_int($timestamp)) {
      $timestamp = strtotime($timestamp);
    }

    // set comparison to now if not provided
    if (!$comparison) {
      $comparison = time();
    }

    // do calculation
    $difference = $comparison - $timestamp;

    // find correct period
    for ($j = 0; $difference >= $lengths[$j]; $j++) {
      $difference /= $lengths[$j];
    }

    $difference = round($difference);

    return sprintf($periods[$j], $difference);
  }
}

?>