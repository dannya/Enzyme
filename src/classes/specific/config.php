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
  // define app constants
  public static $app    = array('id'          => 'enzyme',
                                'name'        => 'Enzyme',
                                'version'     => '1.20');

  // define meta information
  public static $meta   = array('author'      => false,
                                'description' => 'A project-independent tool for creating regular project reports and assisting interesting statistical analysis.',
                                'keywords'    => 'enzyme, digest, open source');


  // define database settings
  public static $db     = array('type'        => 'Mysql',
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
}

?>