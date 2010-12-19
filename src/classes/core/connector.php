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


abstract class Connector {
  protected $repo         = null;
  protected $initialised  = false;

  public $summary         = array();
  public static $types    = array('svn'   => 'SVN',
                                  'imap'  => 'IMAP');


  public function __construct($repo) {
    // set repository details
    $this->repo = $repo;
  }


  public function setupInsertRevisions() {
    // setup summary
    $this->summary['skipped']['title']    = _('Skipped: %d');
    $this->summary['skipped']['value']    = 0;
    $this->summary['processed']['title']  = _('Processed: %d');
    $this->summary['processed']['value']  = 0;

    // set initialised flag
    $this->initialised = 'insertRevisions';
  }


  public function setupParseAuthors() {
    // setup summary
    $this->summary['skipped']['title']    = _('Skipped: %d');
    $this->summary['skipped']['value']    = 0;
    $this->summary['added']['title']      = _('Added: %d');
    $this->summary['added']['value']      = 0;
    $this->summary['malformed']['title']  = _('Malformed: %d');
    $this->summary['malformed']['value']  = 0;

    // set initialised flag
    $this->initialised = 'parseAuthors';
  }


  public static function getTypes() {
    return self::$types;
  }


  public static function getRepositories() {
    $repos = Cache::load('repositories');

    if (!$repos) {
      // load from database
      $repos = Db::reindex(Db::load('repositories', false, null, '*', true, 'priority'), 'id');

      // save in cache
      Cache::save('repositories', $repos);
    }

    return $repos;
  }


  public static function getRepository($id) {
    // load list of repositories
    $repos = self::getRepositories();

    // pick repository out of list
    if (isset($repos[$id])) {
      return $repos[$id];

    } else {
      return false;
    }
  }
}

?>