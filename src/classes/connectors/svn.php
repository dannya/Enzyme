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


class Svn extends Connector {
  private $start        = null;
  private $end          = null;
  private $showErrors   = true;


  public function __construct($repo) {
    // setup summary, repo details
    parent::__construct($repo);
  }


  public function setupInsertRevisions($start = null, $end = null, $showErrors = true) {
    // setup summary
    parent::setupInsertRevisions();

    // set options
    $this->start        = $start;
    $this->end          = $end;
    $this->showErrors   = $showErrors;
  }


  public function insertRevisions() {
    // check we have initialised
    if ($this->initialised != 'insertRevisions') {
      throw new Exception('Call setupInsertRevisions() on this object first');
    }

    // allow start and end to be passed in any order
    if ($this->start < $this->end) {
      $boundaries['start']  = $this->start;
      $boundaries['end']    = $this->end;

    } else {
      $boundaries['start']  = $this->end;
      $boundaries['end']    = $this->start;
    }

    // get start and end revision numbers
    foreach ($boundaries as $boundary => $value) {
      if (is_numeric($value)) {
        // assume this is a revision number
        $revision[$boundary] = $value;

      } else {
        // assume this is a date
        $cmd    = 'svn log --non-interactive ' . $this->getRepoCmdAuth() . '--xml -v -r {' . $value . '} ' . $this->repo['hostname'];
        $data   = shell_exec(escapeshellcmd($cmd) . $this->showErrors);
        $data   = simplexml_load_string($data);

        $revision[$boundary] = (int)$data->logentry->attributes()->revision;
      }
    }


    // get list of processed commits so we don't fetch twice
    $processedRevisions = Enzyme::getProcessedRevisionsList();


    // get revision information
    $cmd    = 'svn log --non-interactive ' . $this->getRepoCmdAuth() . '--xml -v -r ' . $revision['start'] . ':' . $revision['end'] . ' ' . $this->repo['hostname'];
    $data   = shell_exec(escapeshellcmd($cmd) . $this->showErrors);
    $data   = simplexml_load_string(utf8_encode($data));


    // get date of last published issue (+ 14 day safety margin) for commit comparison?
    if (AUTO_REVIEW_COMMITS) {
      $lastPublishedIssue = strtotime(Digest::getLastIssueDate(null, true, true)) - 1209600;
    }


    // process and store data
    foreach ($data as $entry) {
      // initialise
      $parsed['commit']       = array();
      $parsed['commitFiles']  = array();
      $parsed['tmp']          = array();
      $parsed['tmp']['paths'] = array();


      // get commit revision
      $parsed['commit']['revision'] = (int)$entry->attributes()->revision;


      // check if revision has already been processed
      if (isset($processedRevisions[$parsed['commit']['revision']])) {
        if (COMMAND_LINE || !empty($_REQUEST['show_skipped'])) {
          Ui::displayMsg(sprintf(_('Skipping revision %s'), $parsed['commit']['revision']), 'msg_skip');
        }

        // increment summary counter
        ++$this->summary['skipped']['value'];

        continue;
      }


      // get additional commit data
      $parsed['tmp']['date']          = strtotime((string)$entry->date);

      $parsed['commit']['date']       = date('Y-m-d H:i:s', $parsed['tmp']['date']);
      $parsed['commit']['developer']  = (string)$entry->author;
      $parsed['commit']['msg']        = Enzyme::processCommitMsg($parsed['commit']['revision'], (string)$entry->msg);
      $parsed['commit']['format']     = 'svn';


      // insert commit files into database
      if (!empty($entry->paths->path[0])) {
        $parsed['commitFiles']['revision'] = $parsed['commit']['revision'];

        // hold in tmp variable to fix PHP memory issues
        $paths = $entry->paths->path;

        foreach ($paths as $path) {
          $parsed['commitFiles']['path']       = (string)$path;
          $parsed['commitFiles']['operation']  = (string)$path->attributes()->action;

          Db::insert('commit_files', $parsed['commitFiles'], true);

          // save data to enable base path calculation below
          $parsed['tmp']['paths'][] = $parsed['commitFiles']['path'];
        }

        // determine base commit path
        $parsed['commit']['basepath'] = Enzyme::getBasePath($parsed['tmp']['paths']);
      }


      // insert commit into database
      Db::insert('commits', $parsed['commit'], true);

      // report successful process/insertion
      Ui::displayMsg(sprintf(_('Processed revision %s'), $parsed['commit']['revision']));

      // auto-mark as reviewed?
      Enzyme::autoReview($parsed, $lastPublishedIssue);

      // increment summary counter
      ++$this->summary['processed']['value'];
    }
  }


  public function parseDevelopers() {
    // check we have initialised
    if ($this->initialised != 'parseDevelopers') {
      throw new Exception('Call setupParseDevelopers() on this object first');
    }


    // get existing authors data from db
    $existingDevelopers = Enzyme::getDevelopers();


    // get fresh developers data
    $cmd    = 'svn cat --non-interactive ' . $this->getRepoCmdAuth() . $this->repo['hostname'] . $this->repo['accounts_file'];
    $data   = shell_exec(escapeshellcmd($cmd));
    $data   = preg_split("/(\r?\n)/", $data);

    if (!isset($data[1])) {
      Ui::displayMsg(_('Could not download developer data'), 'error');
      return false;
    }


    // append accounts (if file present)
    if (is_file(BASE_DIR . '/data/append_accounts.txt')) {
      $data = array_merge(file(BASE_DIR . '/data/append_accounts.txt'), $data);
    }


    // iterate through file line-by-line, inserting into database where not present
    foreach ($data as $theDeveloper) {
      if (empty($theDeveloper)) {
        continue;
      }


      // split into parts by spaces
      $elements = preg_split('/\s+/', $theDeveloper, -1, PREG_SPLIT_NO_EMPTY);


      // check enough elements are present
      if (count($elements) <= 3) {
        // report malformed entry
        Ui::displayMsg(sprintf(_('Entry "%s" malformed, not added'), $theDeveloper), 'error');

        // increment summary counter
        ++$this->summary['malformed']['value'];
        continue;
      }


      // set data - methodology:
      //  - email has no spaces, will always be last element
      //  - account has no spaces, will always be first element
      //  - name will be the remaining middle elements combined
      $developer['email']    = rtrim(array_pop($elements));
      $developer['account']  = array_shift($elements);
      $developer['name']     = trim(implode(' ', $elements));


      // check if developer has already been processed
      if (isset($existingDevelopers[$developer['account']])) {
        if (!empty($_POST['show_skipped'])) {
          Ui::displayMsg(sprintf(_('Skipping: %s'), $theDeveloper));
        }

        // increment summary counter
        ++$this->summary['skipped']['value'];
        continue;
      }


      // insert into database (update if existing)
      Db::saveMulti('developers', array($developer));
      Db::insert('developer_privacy', array('account' => $developer['account']), true);

      // report success
      Ui::displayMsg(sprintf(_('Added %s (%s) to developers table'), $developer['name'], $developer['account']));

      // increment summary counter
      ++$this->summary['added']['value'];
    }
  }


  private function getRepoCmdAuth() {
    if (!empty($this->repo->username) && !empty($this->repo->password)) {
      return '--username ' . $this->repo->username . ' --password ' . $this->repo->password . ' ';

    } else {
      return null;
    }
  }
}

?>