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


final class Config {
  public static $framework  = array('version'     => '20110912');


  // define app constants
  public static $app        = array('id'          => 'enzyme',
                                    'name'        => 'Enzyme',
                                    'version'     => '1.20');


  // define meta information
  public static $meta       = array('author'      => false,
                                    'description' => 'A project-independent tool for creating regular project reports and assisting interesting statistical analysis.',
                                    'keywords'    => 'enzyme, digest, open source');


  // define locale information
  public static $locale     = array('language'    => 'en_US',
                                    'timezone'    => 'Europe/London');


  // define database settings
  public static $db         = array('type'        => 'Mysql',
                                    'host'        => 'localhost',
                                    'user'        => 'root',
                                    'password'    => 'hello1',
                                    'database'    => 'enzyme',

                                    'tables'      => array('applications',
                                                           'bugfixers',
                                                           'commits',
                                                           'commits_reviewed',
                                                           'commit_bugs',
                                                           'commit_files',
                                                           'commit_path_filters',
                                                           'countries',
                                                           'data_terms',
                                                           'developers',
                                                           'developer_privacy',
                                                           'developer_survey',
                                                           'digests',
                                                           'digest_intro_people',
                                                           'digest_intro_sections',
                                                           'digest_intro_media',
                                                           'digest_stats',
                                                           'digest_stats_bugfixers',
                                                           'digest_stats_buzz',
                                                           'digest_stats_developers',
                                                           'digest_stats_extended',
                                                           'digest_stats_i18n',
                                                           'digest_stats_modules',
                                                           'errors',
                                                           'filetypes',
                                                           'languages',
                                                           'links',
                                                           'repositories',
                                                           'settings',
                                                           'tmp_commits',
                                                           'users'));


  // define settings information
  private static $settings  = null;


  public static function getSetting() {
    $args = func_get_args();

    // load Enzyme settings if not cached in object
    if (!self::$settings) {
      self::$settings = Enzyme::loadSettings(true);

      // run setup?
      if (!self::$settings) {
        $setup = new SetupUi();

        echo Ui::drawHtmlPage($setup->drawPage(),
                              Config::$app['name'] . ' - ' . _('Setup'),
                              array('/css/common.css', '/css/setupui.css'),
                              array_merge(array('/js/prototype.js', '/js/effects.js', '/js/index.php?script=common'), $setup->getScript()));
        exit;
      }
    }


    // return specified setting key
    if (func_num_args() == 1) {
      if (isset(self::$settings[$args[0]])) {
        return self::$settings[$args[0]]['value'];

      } else {
        Log::error('Setting not found', false, $args);

        return false;
      }

    } else if (func_num_args() == 2) {
      if (isset(self::$settings[$args[0]][$args[1]])) {
        return self::$settings[$args[0]][$args[1]]['value'];

      } else {
        Log::error('Setting not found', false, $args);

        return false;
      }
    }
  }


  public static function setSetting($args, $value, $persist = false) {
    // set specified setting key
    $numArgs = count($args);

    if ($numArgs == 1) {
      self::$settings[$args[0]]['value'] = $value;

    } else if ($numArgs == 2) {
      self::$settings[$args[0]][$args[1]]['value'] = $value;

    } else {
      Log::error('Could not save setting', false, $args);
    }

    return true;
  }
}

?>