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


class EnzymeUi {
  public $user              = null;
  public $frame             = null;

  private $title            = APP_NAME;

  private $style            = array('/css/common.css');
  private $appScript        = array('/js/prototype.js',
                                    '/js/effects.js',
                                    '/js/hotkey.js');

  private $userScript       = null;


  public function __construct() {
    // handle login
    $this->user = new User();

    // determine current frame
    if (isset($_GET['page'])) {
      $request = explode('/', trim($_GET['page'], '/'));
      $current = reset($request);

    } else {
      $request = array();
      $current = null;
    }


    if ($current == 'about') {
      // show information about Enzyme
      $this->frame = new AboutUi();

    } else {
      if (empty($this->user->auth)) {
        // not logged in
        if ($current == 'reset') {
          // show password reset
          $this->frame = new ResetUi();

        } else {
          // show login prompt
          $this->frame = new LoginUi();
        }

      } else {
        // logged in, initialise authorised UI
        if ($current == 'insert') {
          $this->frame = new InsertUI($this->user);

        } else if ($current == 'review') {
          $this->frame = new ReviewUI($this->user);

        } else if ($current == 'classify') {
          $this->frame = new ClassifyUI($this->user);

        } else if ($current == 'digests') {
          $this->frame = new DigestsUI($this->user);

        } else if ($current == 'tools') {
          $this->frame = new ToolsUI($this->user);

        } else if ($current == 'settings') {
          $this->frame = new SettingsUI($this->user);

        } else if ($current == 'setup') {
          $this->frame = new SetupUI();

        } else if ($current == 'users' || $current == 'error/HTTP_NOT_FOUND.html.var') {
          $this->frame = new UsersUI($this->user);

        } else {
          $this->frame = new HomeUi($this->user);
        }
      }
    }

    // get specific style
    $this->style        = array_merge($this->style, $this->frame->getStyle());

    // set script
    $this->userScript[] = '/js/index.php?script=common&amp;id=' . $this->frame->id;
  }


  public function drawTitle() {
    $buf = '<title>' . APP_NAME . ' - ' . $this->frame->title . '</title>';

    return $buf;
  }


  public function drawMeta() {
    $buf = '<meta name="description" content="' . META_DESCRIPTION . '" />
            <meta name="keywords" content="' . META_KEYWORDS . '" />
            <meta name="viewport" content="width=device-width; initial-scale=1.0" />

            <link rel="shortcut icon" href="' . BASE_URL .'/favicon.ico" type="image/x-icon" />
            <link rel="icon" href="' . BASE_URL . '/favicon.ico" type="image/x-icon" />
            <link rel="alternate" type="application/rss+xml" title="" href="http://boo" />';

    return $buf;
  }


  public function drawStyle() {
    $buf = null;

    foreach ($this->style as $style) {
      $buf .= '<link rel="stylesheet" href="' . BASE_URL . $style . '?version=' . VERSION . '" type="text/css" media="screen" />' . "\n";
    }

    return $buf;
  }


  public function drawScript() {
    if (!LIVE_SITE) {
      // don't use minified and cached version on dev
      $theScript = array_merge($this->appScript,
                               $this->userScript,
                               $this->frame->getScript());
    } else {
      // use cached and minified versions
      $theScript    = $this->userScript;
      array_unshift($theScript, Cache::getMinJs('app', $this->appScript));
      $theScript[]  = Cache::getMinJs($this->frame->id,  $this->frame->getScript());
    }

    // draw out script
    $buf = null;

    foreach ($theScript as $script) {
      $buf .= '<script type="text/javascript" src="' . BASE_URL . $script . '"></script>' . "\n";
    }

    return $buf;
  }


  public function drawHeader() {
    // show title?
    if ($this->frame->id != 'index') {
      $title = '<h1 id="header-title">' . $this->frame->title . '</h1>';
    } else {
      $title = null;
    }

    // include user control?
    if (!empty($this->user->auth)) {
      // select "settings" button?
      if ($this->frame->id == 'settings') {
        $settingsSelected = ' selected';
      } else {
        $settingsSelected = null;
      }

      // show "setup" button (only admins)
      if ($this->user->hasPermission('admin')) {
        if ($this->frame->id == 'setup') {
          $setupSelected = ' selected';
        } else {
          $setupSelected = null;
        }

        $setupButton = '<input id="button_setup" class="padding' . $setupSelected . '" type="button" title="' . _('Setup') . '" value="' . _('Setup') . '" onclick="top.location=\'' . BASE_URL . '/setup/\';" />';
      } else {
        $setupButton = null;
      }

      // draw header
      $user =  '<div id="header-user">
                  <span>' . $this->user->data['firstname'] . ' ' . $this->user->data['lastname'] . '</span>
                  <input id="button_logout" type="button" title="' . _('Logout') . '" value="' . _('Logout') . '" onclick="top.location=\'?logout\';" />' .
                  $setupButton .
               '  <input id="button_settings" class="padding' . $settingsSelected . '" type="button" title="' . _('Settings') . '" value="' . _('Settings') . '" onclick="top.location=\'' . BASE_URL . '/settings/\';" />
                </div>';
    } else {
      $user = null;
    }

    $buf = '<div id="header">
              <a id="logo" href="' . BASE_URL . '/" class="n">&nbsp;</a>' .
              $title .
              $user .
           '</div>';

    return $buf;
  }


  public function drawFooter() {
    if (method_exists($this->frame, 'drawFooter')) {
      return $this->frame->drawFooter();

    } else {
      return null;
    }
  }


  public function drawSidebar() {
    if (method_exists($this->frame, 'drawSidebar')) {
      return $this->frame->drawSidebar();

    } else {
      return null;
    }
  }
}

?>