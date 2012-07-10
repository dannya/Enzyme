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


class Ui {
  public static function redirect($page) {
    if (!headers_sent()) {
      header('Location: ' . BASE_URL . $page);

    } else{
      echo '<script type="text/javascript">top.location="', BASE_URL, $page, '";</script>';
    }

    exit;
  }


  public static function drawHtmlPage($content, $title = null, array $css = array(),
                                      array $js = array(), $bodyClass = null) {

    $buf = self::drawHtmlPageStart($title, $css, $js, $bodyClass) .
           $content .
           self::drawHtmlPageEnd();

    return $buf;
  }


  public static function drawHtmlPageStart($title = null, array $css = array(),
                                           array $js = array(), $bodyClass = null) {
    $style   = null;
    $script  = null;

    if ($bodyClass) {
      $bodyClass = ' class="' . $bodyClass . '"';
    }

    // draw css and js
    if ($css) {
      foreach ($css as $file) {
        $style .= '<link rel="stylesheet" href="' . BASE_URL . $file . '" type="text/css" media="screen" />' . "\n";
      }
    }

    if ($js) {
      foreach ($js as $file) {
        $script .= '<script type="text/javascript" src="' . BASE_URL . $file . '"></script>' . "\n";
      }
    }


    // draw page
    $buf = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">
              <head id="head-iframe">
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <title>' . $title . '</title>' .
                $style .
                $script .
           '  </head>

              <body id="body-iframe"' . $bodyClass . '>';

    return $buf;
  }


  public static function drawHtmlPageEnd($setFinished = true) {
    $buf = null;

    if ($setFinished) {
      $buf .= self::setProcessFinished(false);
    }

    // draw page end
    $buf .=  '  </body>
            </html>';

    return $buf;
  }


  public static function setProcessFinished($echo = true) {
    // insert "finished" element which can be recognised by observing scripts
    $buf = '<span id="finished">
              &nbsp;
            </span>';

    // automatically add to page?
    if ($echo) {
      echo $buf;
    }

    return $buf;
  }


  public static function jsStr($str) {
    $buf = null;
    $str = explode("\n", $str);

    foreach ($str as $line) {
      $buf .= '\'' . trim(str_replace("'", "\'", $line)) . '\'';

      // join onto next line?
      if ($line != end($str)) {
        $buf .= ' + ' . "\n";
      }
    }

    return $buf;
  }


  public static function htmlSelector($id, $items, $preselectKey = null,
                                      $onChange = null, $name = null, $style = null, $emptyEntry = false) {
    // set onchange?
    if ($onChange) {
      $onChange = ' onchange="' . $onChange . '"';
    }

    // name specified?
    if (!$name) {
      $name = $id;
    }

    // add styling?
    if ($style) {
      $style = ' style="' . $style . '"';
    }

    // add empty entry at top?
    if ($emptyEntry) {
      if (!isset($items[0])) {
        array_unshift($items, '');

      } else {
        // if items are numerically-indexed, use safer method
        $items[''] = '';
        ksort($items);
      }
    }


    // draw
    $buf = '<select id="' . $id . '" name="' . $name . '"' . $onChange . $style . '>';

    foreach ($items as $key => $value) {
      $params = null;

      if ($key == $preselectKey) {
        $params .= ' selected="selected"';
      }

      // check if value is array of options
      if (is_array($value)) {
        $string = $value['value'];

        if (isset($value['class'])) {
          $params .= ' class="' . $value['class'] . '"';
        }

      } else {
        $string = $value;
      }

      // set string to space character if value is empty
      if ($string == '') {
        $string = '&nbsp;';
      }

      // draw
      $buf .= '<option value="' . $key . '"' . $params . '>' . $string . '</option>';
    }

    $buf .= '</select>';

    return $buf;
  }


  public static function pagination($perPage, $total, $currentPage, $action, $showText = null) {
    $page    = 1;
    $counter = 0;

    $buf =  ' <div class="pagination">';

    // show status text?
    if ($showText) {
      $buf .= '  <span>' . str_replace(array('[START]', '[END]', '[TOTAL]'),
                                       array(((($currentPage - 1) * $perPage) + 1), min(($currentPage * $perPage), $total), $total),
                                       $showText) . '</span>';
    }

    $buf .= '  <div>';

    while ($counter < $total) {
      // styling and click action
      if ($page == $currentPage) {
        $class = ' class="s"';
        $onclick = null;
      } else {
        $class = null;
        $onclick = ' onclick="' . str_replace('[PAGE]', $page, $action) . '"';
      }

      $buf .= '<div' . $class . $onclick . '>' . $page++ . '</div>';

      // increment
      $counter += $perPage;
    }

    $buf .= '   </div>
              </div>';

    return $buf;
  }


  // Valid classes:
  //  - 'error'
  //  - 'msg_skip'
  //  - 'msg_fetch'
  //  - 'msg_title'
  public static function displayMsg($msg, $class = null) {
    if (COMMAND_LINE) {
      // command-line, no need for fancy formatting!
      if ($class) {
        echo ' - ' . $msg . "\n";
      } else {
        echo $msg . "\n";
      }

    } else {
      if ($class) {
        $class = ' class="' . $class . '"';
      }

      echo '<span' . $class . '>' . $msg . "</span><br />\n";

      @ob_flush();
      @flush();
    }
  }


  public static function processSummary($summary, $showTotal = false) {
    $total = null;

    // define glue based on runtime environment
    if (COMMAND_LINE) {
      $glue = "\n";
    } else {
      $glue = "<br />\n";
    }


    // pre-calculate totals
    foreach ($summary as $entry) {
      $total += $entry['value'];
    }


    $buf = null;


    if ($total > 0) {
      // process values
      foreach ($summary as $entry) {
        // show totals inline
        $percent = round((($entry['value'] / $total) * 100), 1);
        $values[] = sprintf($entry['title'] . _(' (%.1f percent of %d)'), $entry['value'], $percent, $total);

        // add to total
        if (!$total) {
          $total += $entry['value'];
        }
      }


      // draw
      $buf = implode($glue, $values);

      // show total?
      if ($showTotal) {
        $buf .= $glue;

        if (COMMAND_LINE) {
          $buf .= sprintf(_('Total: %d'), $total);
        } else {
          $buf .= '<span class="bold">' . sprintf(_('Total: %d'), $total) . '</span>';
        }
      }
    }


    // wrap in markup?
    if (COMMAND_LINE) {
      return "-------------------------------------\n" .
              $buf . "\n";

    } else {
      return '<div class="summary_box">' .
                $buf .
             '</div>';
    }
  }


  public static function displayEmailAddress($emailAddress) {
    $pattern  = array('@',
                      '.');
    $replace  = array(' at ',
                      ' dot ');

    return str_replace($pattern, $replace, $emailAddress);
  }


  public static function drawIndicator($id) {
    return '<span id="indicator-' . $id . '"><span>&nbsp;</span></span>';
  }


  public static function filesize($bytes, $base = 1024) {
    if (!$bytes) {
      return null;
    }

    // choose prefix
    if ($base == 1000) {
      $units = array('B', 'kB', 'MB', 'GB');
    } else {
      $units = array('B', 'KiB', 'MiB', 'GiB');
    }

    // determine power to select correct units
    $power = floor(log($bytes) / log($base));

    return round(($bytes / pow($base, floor($power))), 2) . ' ' . $units[$power];
  }


  public static function fillEmpty($string, $return = '&nbsp;') {
    if (!empty($string)) {
      return $string;
    } else {
      return $return;
    }
  }


  /**
   * Get either a Gravatar URL or complete image tag for a specified email address.
   *
   * @param string $email The email address
   * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
   * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
   * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
   * @param boole $img True to return a complete IMG tag False for just the URL
   * @param array $atts Optional, additional key/value attributes to include in the IMG tag
   * @return String containing either just a URL or a complete image tag
   * @source http://gravatar.com/site/implement/images/php/
   */
  public static function getGravatar($email, $size = 80, $default = 'monsterid', $rating = 'g', $wrap = false, $atts = array()) {
    $url = 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?s=' . $size . '&amp;d=' . $default . '&amp;r=' . $rating;

    if ($wrap) {
      // wrap in image tag
      $url = '<img src="' . $url . '"';

      foreach ($atts as $key => $val) {
        $url .= ' ' . $key . '="' . $val . '"';
      }

      $url .= ' />';
    }

    return $url;
  }
}

?>