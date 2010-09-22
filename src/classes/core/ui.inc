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


class Ui {
  public static function redirect($page) {
    if (!headers_sent()) {
      header('Location: ' . BASE_URL . $page);

    } else{
      echo '<script type="text/javascript">top.location="', BASE_URL, $page, '";</script>';
    }

    exit;
  }


  public static function drawHtmlPage($content, $title = null, $css = null, $js = null) {
    $buf = self::drawHtmlPageStart($title, $css, $js) .
           $content .
           self::drawHtmlPageEnd();

    return $buf;
  }


  public static function drawHtmlPageStart($title = null, $css = null, $js = null) {
    $style   = null;
    $script  = null;

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

              <body id="body-iframe">';

    return $buf;
  }


  public static function drawHtmlPageEnd() {
    // draw page end
    $buf = '  </body>
            </html>';

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


  public static function htmlSelector($id, $items, $preselectKey = null, $onChange = null) {
    // set onchange?
    if ($onChange) {
      $onChange = ' onchange="' . $onChange . '"';
    }

    $buf = '<select id="' . $id . '" name="' . $id . '"' . $onChange . '>';

    foreach ($items as $key => $value) {
      if ($key == $preselectKey) {
        $selected = ' selected="selected"';
      } else {
        $selected = null;
      }

      // fill with space character if value is empty
      if ($value == '') {
        $value = '&nbsp;';
      }

      $buf .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
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


  public static function displayRevision($type, $id, $data, &$authors) {
    $buf = '<div id="' . $id . '" class="item normal">
              <div class="commit-title">
                Commit <span class="revision">' . $data['revision'] . '</span> by <span>' . Enzyme::getAuthorInfo('name', $data['author']) . '</span> (<span>' . $data['author'] . '</span>)
                <br />' .
                $data['basepath'] .
           '  </div>
              <div class="commit-msg">' .
                Enzyme::formatMsg($data['msg']) .
           '  </div>';

    // add classification input fields?
    if ($type == 'classify') {
      // show values as blank if set as 0
      $area = $data['area'];
      $type = $data['type'];

      if ($area == 0) {
        $area = null;
      }
      if ($type == 0) {
        $type = null;
      }

      $buf .=  '<div class="commit-classify">
                  <label>
                    Area <input id="' . $id . '-area" type="text" onblur="setCurrentItem(\'' . $id . '\');" onfocus="scrollItem(\'' . $id . '\');" value="' . $area . '" />
                  </label>
                  <label>
                    Type <input id="' . $id . '-type" type="text" onblur="setCurrentItem(\'' . $id . '\');" onfocus="scrollItem(\'' . $id . '\');" value="' . $type . '" />
                  </label>
                </div>';
    }

    $buf .=  '</div>';

    return $buf;
  }


  public static function statusArea($type) {
    // determine display string
    if ($type == 'classify') {
      $display = '<span id="commit-counter">0</span> commits classified (<span id="commit-total">0</span> total)';
      $buttons = '<input id="review-save" type="button" onclick="save(\'' . $type . '\');" value="' . _('Save') . '" title="' . _('Save') . '" />';

    } else if ($type == 'review') {
      $display = '<span class="bold">Selected <span id="commit-selected">0</span></span> of <span id="commit-counter">0</span> commits reviewed (<span id="commit-total">0</span> total)';
      $buttons = '<input id="review-save" type="button" disabled="disabled" onclick="save(\'' . $type . '\');" value="' . _('Save') . '" title="' . _('Save') . '" />';
    }


    $buf = '<div id="status-area">
              <div id="status-area-text">' .
                $display .
                '<input type="button" style="visibility:hidden;" />
              </div>
              <div id="status-area-actions">
                <div id="status-area-info" style="display:none;"></div>' .
                $buttons .
             '</div>
            </div>';

    return $buf;
  }


  public static function displayMsg($msg, $class = null) {
    if ($class) {
      $class = ' class="' . $class . '"';
    }

    echo '<span' . $class . '>' . $msg . "</span><br />\n";

    @ob_flush();
    @flush();
  }


  public static function processSummary($summary, $showTotal = false) {
    $total = null;

    // pre-calculate totals
    foreach ($summary as $entry) {
      $total += $entry['value'];
    }


    $buf = '<div class="summary_box">';

    foreach ($summary as $entry) {
      // show totals inline
      $percent = round((($entry['value'] / $total) * 100), 1);
      $buf .= sprintf($entry['title'] . _(' (%.1f percent of %d)') . "<br />\n", $entry['value'], $percent, $total);

      // add to total
      if (!$total) {
        $total += $entry['value'];
      }
    }

    // finally, show total?
    if ($showTotal) {
      $buf .=  '<br />
                <span class="bold">' . sprintf(_('Total: %d'), $total) . '</span>';
    }

    $buf .= '</div>';

    return $buf;
  }
}

?>