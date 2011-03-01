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
                                      $onChange = null, $name = null, $style = null) {
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

    $buf = '<select id="' . $id . '" name="' . $name . '"' . $onChange . $style . '>';

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


  public static function displayRevision($type, $id, $data, &$authors, &$user = null, &$classifications = null) {
    // show date and buttons?
    if ($type == 'review') {
      $date = '<div class="date">' .
                 $data['date'] .
              '</div>
               <div class="buttons">
                 <div class="yes" onclick="actionSelect(event);">&nbsp;</div>
                 <div class="no" onclick="actionNext(event);">&nbsp;</div>
               </div>';
    } else {
      $date = null;
    }


    // set path
    $data['basepath'] = Enzyme::drawBasePath($data['basepath']);


    // show bugs (as icons) if available
    if (isset($data['bug'])) {
      if ($type == 'review') {
        $bugs = Digest::drawBugs($data, 'bugs');

      } else if ($type == 'classify') {
        $bugs = '<div class="bugs">';

        foreach ($data['bug'] as $bug) {
          $bugs  .= '<div onclick="window.open(\'' . WEBBUG . $bug['bug'] . '\');" title="' . sprintf(_('Bug %d: %s'), $bug['bug'], App::truncate(htmlentities($bug['title']), 90, true)) . '">
                       &nbsp;
                     </div>';
        }

        $bugs  .= '</div>';
      }

    } else {
      $bugs = null;
    }


    // set item class
    if ($user && ($user->data['interface'] == 'mouse')) {
      $itemClass = 'mouse';
    } else {
      $itemClass = 'keyboard';
    }


    // show repository name? (for Git commits)
    $repository = null;

    if (!empty($data['format']) && ($data['format'] == 'git')) {
      // Git
      if (!empty($data['repository'])) {
        $repository = self::formatRepositoryName($data['repository']);
      }

      $revisionLink  = '<i id="r::' . $data['revision'] . '" class="revision">' .
                          Digest::getShortGitRevision($data['revision']) .
                       '</i>';

    } else {
      // SVN
      $revisionLink  = '<a id="r::' . $data['revision'] . '" class="revision" href="' . WEBSVN . '?view=revision&amp;revision=' . $data['revision'] . '" target="_blank" tabindex="0">' .
                          $data['revision'] .
                       '</a>';
    }


    // draw commit
    $buf = '<div id="' . $id . '" class="item normal ' . $type . ' ' . $itemClass . '">
              <div class="commit-title">' .
                sprintf(_('Commit %s by %s (%s)'),
                  $revisionLink,
                  '<span>' . Enzyme::getAuthorInfo('name', $data['author']) . '</span>',
                  '<span>' . $data['author'] . '</span>') .
           '    <br />' .
                $repository . Enzyme::drawBasePath($data['basepath']) .
                $date .
           '  </div>
              <div class="commit-msg">
                <span>' .
                  Enzyme::formatMsg($data['msg']) .
           '    </span>' .
                $bugs .
           '  </div>';


    // add classification input fields?
    if ($type == 'classify') {
      // search for basepath in common area classifications, so we can prefill value
      if ($classifications) {
        foreach ($classifications as $filter) {
          if ((($filter['target'] == 'path') && (strpos($data['basepath'], $filter['matched']) !== false)) ||
              (($filter['target'] == 'repository') && (strpos($data['repository'], $filter['matched']) !== false))) {

            $data['area'] = $filter['area'];

            break;
          }
        }
      }

      // show values as blank if set as 0
      if ($data['area'] == 0) {
        $data['area'] = null;
      }
      if ($data['type'] == 0) {
        $data['type'] = null;
      }


      // show remove button? (if user is admin, or reviewed this commit)
      if ($user && ($user->hasPermission(array('editor')) || ($data['reviewer'] == $user->data['username']))) {
        $removeButton  = '<div onclick="removeCommit(' . Digest::quoteRevision($data['revision']) . ', callbackRemoveCommit);" title="' . _('Unselect this commit?') . '" class="remove">
                            &nbsp;
                          </div>';
      } else {
        $removeButton  = null;
      }


      // use mouse-oriented or keyboard-oriented interface?
      if ($user && ($user->data['interface'] == 'mouse')) {
        // mouse
        $areas = array_values(Enzyme::getAreas(true));
        $types = array_values(Enzyme::getTypes(true));

        $buf  .= '<div class="commit-panel">
                    <div class="commit-blame' . (($data['reviewer'] == $user->data['username']) ? ' me' : '') . '">' .
                      sprintf(_('Reviewed by %s'), $data['reviewer']) .
                 '  </div>' .

                    $removeButton .

                 '  <div class="commit-classify mouse">
                      <div>
                        <label>Area</label>' .
                        Ui::htmlSelector($id . '-area', $areas, $data['area'], 'setCurrentItem(\'' . $id . '\');') .
                 '    </div>
                      <div>
                        <label>Type</label>' .
                        Ui::htmlSelector($id . '-type', $types, $data['type'], 'setCurrentItem(\'' . $id . '\');') .
                 '    </div>
                    </div>
                  </div>';

      } else {
        // keyboard
        $buf  .= '<div class="commit-classify keyboard">
                    <label>' .
                      _('Area') . ' <input id="' . $id . '-area" type="text" onblur="setCurrentItem(\'' . $id . '\');" onfocus="scrollItem(\'' . $id . '\');" value="' . $data['area'] . '" />
                  </label>
                    <label>' .
                      _('Type') . ' <input id="' . $id . '-type" type="text" onblur="setCurrentItem(\'' . $id . '\');" onfocus="scrollItem(\'' . $id . '\');" value="' . $data['type'] . '" />
                  </label>
                </div>';
    }
    }

    $buf .=  '</div>';

    return $buf;
  }


  public static function statusArea($type, $user = null) {
    // determine interface elements
    if ($type == 'classify') {
      // get total number of commits available to classify
      $total   = Enzyme::getProcessedRevisions('marked', null, null, null, true);

      $display = sprintf(_('%s commits classified (%s displayed, %s total)'),
                         '<span id="commit-counter">0</span>',
                         '<span id="commit-displayed">0</span>',
                         '<span id="commit-total">' . $total . '</span>');

      // interface selector
      $interface = array('mouse'    => _('Mouse'),
                         'keyboard' => _('Keyboard'));

      $interfaceSelector = '<div id="interface-selector">';

      foreach ($interface as $key => $value) {
        if ($user && ($user->data['interface'] == $key)) {
          $selected = ' checked="checked"';
        } else {
          $selected = null;
        }

        $interfaceSelector  .= '<label title="' . $value . '" class="' . $key . '">
                                  <input id="interface-' . $key . '" name="interface" value="' . $key . '" type="radio" onclick="changeInterface(\'' . $key . '\');"' . $selected . ' /> <i>&nbsp;</i>
                                </label>';
      }


      // allow users to only see commits they have reviewed
      if (isset($user->data['classify_user_filter']) && ($user->data['classify_user_filter'] == 'Y')) {
        $userFilterChecked = ' checked="checked"';
      } else {
        $userFilterChecked = null;
      }

      $interfaceSelector  .= '  <label id="classify-user-filter" title="' . _('Only show commits I reviewed') . '">
                                  <input type="checkbox" onchange="setClassifyUserFilter(event);"' . $userFilterChecked . ' /> <i>&nbsp;</i>
                                </label>
                              </div>';


      // buttons
      $buttons = '<input id="review-save" type="button" onclick="save(\'' . $type . '\', this);" value="' . _('Save') . '" title="' . _('Save') . '" />
                  <input id="review-cancel" class="cancel" type="button" onclick="if (confirm(strings.confirm_dataloss)) { location.reload(true); } return false;" value="' . _('Cancel') . '" title="' . _('Cancel') . '" />';


    } else if ($type == 'review') {
      // get total number of commits available to review
      $total   = Enzyme::getProcessedRevisions('unreviewed', true, null, null, true);

      $display = sprintf('<span class="bold">' . _('Selected %s of %s commits reviewed (%s displayed, %s total)'),
                         '<span id="commit-selected">0</span></span>',
                         '<span id="commit-counter">0</span>',
                         '<span id="commit-displayed">0</span>',
                         '<span id="commit-total">' . $total . '</span>');

      $interfaceSelector = null;
      $buttons = '<input id="review-save" type="button" disabled="disabled" onclick="save(\'' . $type . '\', this);" value="' . _('Save') . '" title="' . _('Save') . '" />
                  <input id="review-cancel" class="cancel" type="button" onclick="if (confirm(strings.confirm_dataloss)) { location.reload(true); } return false;" value="' . _('Cancel') . '" title="' . _('Cancel') . '" />';
    }


    // draw
    $buf = '<div id="status-area">
              <div id="status-area-text">' .
                $display .
                '<input type="button" style="visibility:hidden;" />
              </div>' .
              $interfaceSelector .
           '  <div id="status-area-actions">
                <div id="status-area-info" style="display:none;">&nbsp;</div>
                <img id="status-area-spinner" style="display:none;" src="' . BASE_URL . '/img/spinner-dark-small.gif" alt="" />' .
                $buttons .
             '</div>
            </div>';

    return $buf;
  }


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


  public static function formatRepositoryName($repositoryName) {
    return '[' . $repositoryName . '] ';
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
}

?>