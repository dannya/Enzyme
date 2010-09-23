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


// define enzyme settings
define('REPOSITORY_TYPE',   'svn');
define('REPOSITORY',        'svn://anonsvn.kde.org/home/kde/');
define('ACCOUNTS_FILE',     'trunk/kde-common/accounts');
define('EXISTING_ISSUES',   'C:/www/commit-digest/issues');
define('EXISTING_ARCHIVE',  'C:/www/commit-digest/archive');
define('EXISTING_DATA',     'C:/www/enzyme/src/data');

define('WEBSVN',            'http://websvn.kde.org');
define('WEBBUG',            'http://bugs.kde.org/show_bug.cgi?id=');
define('WEBBUG_XML',        'http://bugs.kde.org/show_bug.cgi?ctype=xml&id=');
define('I18N_STATS',        'http://l10n.kde.org/stats/gui/trunk-kde4/toplist.php');
define('I18N_TEAMS',        'http://i18n.kde.org/teams-list.php');
define('BUG_STATS',         'https://bugs.kde.org/weekly-bug-summary.cgi');

define('DISPLAY_URL',       'http://commit-digest.org');


// define database settings
define('DB_HOST',           'localhost');
define('DB_USER',           'root');
define('DB_PASSWORD',       'hello1');
define('DB_DATABASE',       'enzyme');


// define app constants
define('APP_ID',            'enzyme');
define('APP_NAME',          'Enzyme');
define('VERSION',           '0.7');

define('META_DESCRIPTION',  'A project-independent tool for creating regular project reports and assisting interesting statistical analysis.');
define('META_KEYWORDS',     'enzyme, digest, open source');

define('DEFAULT_LOCATION',  'uk');
define('DEFAULT_LANGUAGE',  'en');
define('DATE_FORMAT',       'Y-m-d');


if (empty($_SERVER['DOCUMENT_ROOT'])) {
  define('COMMAND_LINE',    true);
  define('BASE_DIR',        dirname(__FILE__));
} else {
  define('BASE_DIR',        rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
  define('COMMAND_LINE',    false);
}


// set command line vars (error reporting, etc)
if (COMMAND_LINE) {
  // set command line vars (error reporting, etc)
  error_reporting(E_ALL);
  define('LIVE_SITE', null);

} else {
  // set general site vars
  error_reporting(E_ALL|E_STRICT);

  define('DOMAIN',    'enzyme');
  define('BASE_URL',  'http://' . $_SERVER['HTTP_HOST']);

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
date_default_timezone_set('Europe/London');


if (COMMAND_LINE) {
  ini_set('display_errors', true);

  // autoload doesn't work...
  function autoload($classes) {
    if (!is_array($classes)) {
      $classes = array($classes);
    }

    foreach ($classes as $class) {
      include('classes/' . $class . '.inc');
    }
  }

} else {
  // set language
  if (isset($_GET['language'])) {
    // load language from query string
    define('LANGUAGE', $_REQUEST['language']);

    // store in session
    $_SESSION['language'] = LANGUAGE;

  } else if (isset($_SESSION['language'])) {
    // load language from cookie
    define('LANGUAGE', $_SESSION['language']);

  } else {
    // use default language
    define('LANGUAGE', DEFAULT_LANGUAGE);
  }


  putenv('LC_ALL=' . LANGUAGE);
  setlocale(LC_ALL, LANGUAGE);
  bindtextdomain('messages', BASE_DIR . '/languages');
  textdomain('messages');

  // add class dir to include path
  $classDirs = array(BASE_DIR . '/classes/core/',
                     BASE_DIR . '/classes/ext/',
                     BASE_DIR . '/classes/ui/');

  set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $classDirs));

  // define autoloader
  spl_autoload_register();
}


// connect to database
Db::connect();

?>