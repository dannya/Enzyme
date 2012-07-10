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


class Date {
  public static function get($type, $date) {
    if ($type == 'full') {
      $format = 'jS F Y';
    } else if ($type == 'full-day') {
      $format = 'l, jS F Y';
    } else if ($type == 'full-day-time') {
      $format = 'l, jS F Y @ g:ia';

    } else if ($type == 'short') {
      $format = 'd/m/Y';
    } else if ($type == 'short-day-time') {
      $format = 'D, jS M @ g:ia';
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

    $lengths  = array(60, 60, 24, 7, 4.35, 12, 1);

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
      $difference = $difference / $lengths[$j];

      if ($j >= 6) {
        break;
      }
    }

    // handle years string better
    if ($j == 6) {
      $difference = round($difference, 2);
    } else {
      $difference = round($difference);
    }

    return sprintf($periods[$j], $difference);
  }


  public static function getSelectData($context, $prependBlank = false) {
    if ($prependBlank) {
      $data = array(0 => '&nbsp;');
    } else {
      $data = array();
    }

    if ($context == 'days') {
      for ($i = 1; $i <= 31; $i++) {
        $data[$i] = $i;
      }

    } else if ($context == 'months') {
      $data = array('01' => _('January'),
                    '02' => _('February'),
                    '03' => _('March'),
                    '04' => _('April'),
                    '05' => _('May'),
                    '06' => _('June'),
                    '07' => _('July'),
                    '08' => _('August'),
                    '09' => _('September'),
                    '10' => _('October'),
                    '11' => _('November'),
                    '12' => _('December'));

      if ($prependBlank) {
        $data = array_merge(array(0 => '&nbsp;'), $data);
      }

    } else if ($context == 'years') {
      $time = self::load();

      for ($i = ($time['year'] - 12); $i >= ($time['year'] - 80); $i--) {
        $data[$i] = $i;
      }
    }

    return $data;
  }
}

?>