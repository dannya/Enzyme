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


// define database settings
define('DB_HOST',           'localhost');
define('DB_USER',           'root');
define('DB_PASSWORD',       'hello1');
define('DB_DATABASE',       'enzyme');


// ------- YOU SHOULDN'T NEED TO MODIFY BELOW HERE --------


// set initial values
if (empty($_SERVER['DOCUMENT_ROOT'])) {
  define('COMMAND_LINE',    true);
  define('BASE_DIR',        dirname(__FILE__));

} else {
  define('COMMAND_LINE',    false);
  define('BASE_DIR',        rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
  define('BASE_URL',        'http://' . $_SERVER['HTTP_HOST']);
}


// add class dira to include path
$classDirs = array(BASE_DIR . '/classes/core/',
                   BASE_DIR . '/classes/ext/',
                   BASE_DIR . '/classes/ui/');

set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $classDirs));


// define autoloader
spl_autoload_register();


if (COMMAND_LINE) {
  ini_set('display_errors', true);

  // autoload doesn't work in command-line
  function autoload($classes) {
    if (!is_array($classes)) {
      $classes = array($classes);
    }

    foreach ($classes as $class) {
      include('classes/' . $class . '.inc');
    }
  }
}


// define app constants
define('APP_ID',    'enzyme');
define('APP_NAME',  'Enzyme');
define('VERSION',   '0.81');


// connect to database
$databaseExists = Db::connect();

if (($_SERVER['SCRIPT_URL'] == '/js/index.php') || ($_SERVER['SCRIPT_URL'] == '/get/setup.php')) {
  // common script and setup handler need to bypass database check (used in setup!)
  define('DEFAULT_TIMEZONE', 'Europe/London');

} else {
  if (!$databaseExists) {
    // database doesn't exist, run setup (with database creation stage)
    $setup = new SetupUi(true);

    echo Ui::drawHtmlPage($setup->drawPage(),
                          APP_NAME . ' - ' . _('Setup'),
                          array('/css/common.css', '/css/setupui.css'),
                          array_merge(array('/js/prototype.js', '/js/effects.js', '/js/index.php?script=common'), $setup->getScript()));
    exit;

  } else {
    // load Enzyme settings
    $settings = Cache::load('settings');
    if (!$settings) {
      $settings = Db::load('settings', false);
    }

    if (!$settings) {
      // run setup
      $setup = new SetupUi();

      echo Ui::drawHtmlPage($setup->drawPage(),
                            APP_NAME . ' - ' . _('Setup'),
                            array('/css/common.css', '/css/setupui.css'),
                            array_merge(array('/js/prototype.js', '/js/effects.js', '/js/index.php?script=common'), $setup->getScript()));
      exit;

    } else {
      // save settings in cache
      Cache::save('settings', $settings);

      // define settings for app access
      foreach ($settings as $setting) {
        define($setting['setting'], $setting['value']);
      }
    }
  }
}


// define meta tags
define('META_DESCRIPTION',  'A project-independent tool for creating regular project reports and assisting interesting statistical analysis.');
define('META_KEYWORDS',     'enzyme, digest, open source');


// define environment settings
if (COMMAND_LINE) {
  // set command line vars (error reporting, etc)
  error_reporting(E_ALL);
  define('LIVE_SITE', null);

} else {
  // set general site vars
  error_reporting(E_ALL|E_STRICT);

  // start user session
  session_start();

  // set environment (live / development)
  if ($_SERVER['HTTP_HOST'] == 'enzyme') {
    define('LIVE_SITE', false);
  } else {
    define('LIVE_SITE', true);
  }
}


// set live site vars
if (LIVE_SITE) {
  ini_set('display_errors', false);
  ini_set('log_errors', true);
} else {
  ini_set('display_errors', true);
  ini_set('log_errors', false);
}


// set timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

?>