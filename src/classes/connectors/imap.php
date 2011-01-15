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


class Imap extends Connector {
  public function __construct($repo) {
    // setup summary, repo details
    parent::__construct($repo);
  }


  public function setupInsertRevisions() {
    // setup summary
    parent::setupInsertRevisions();
  }


  public function insertRevisions() {
    // check we have initialised
    if ($this->initialised != 'insertRevisions') {
      throw new Exception('Call setupInsertRevisions() on this object first');
    }

    $hostname = '{' . $this->repo['hostname'] . ':' . $this->repo['port'] . '/imap/ssl}';
//    $hostname = '{' . $this->repo['hostname'] . ':' . $this->repo['port'] . '/imap/novalidate-cert}';

    // connect to inbox
    $inbox = imap_open($hostname, $this->repo['username'], $this->repo['password'], null, 1) or die(imap_last_error());

    // get unseen emails
    //$emails = imap_search($inbox, 'UNSEEN');
    $emails = imap_search($inbox, 'ALL');

    if ($emails) {
      foreach ($emails as $emailNumber) {
        // initialise
        $i            = 0;
        $tmpPaths     = array();
        $commitFiles  = array();
        $commit       = array();


        // get message header
        $header = imap_fetch_overview($inbox, $emailNumber, 0);
        $header = reset($header);


        // check if we should include this commit
        // (kde-commits mailing list prefixes subjects with [ when it is a Git commit...)
        if (!isset($header->subject) || trim($header->subject[0]) != '[') {
          // delete email message
          imap_delete($inbox, $emailNumber);

          // increment summary counter
          ++$this->summary['skipped']['value'];
          continue;

        } else {
          // set repository name and type
          $tmp                  = explode(']', $header->subject);
          $commit['repository'] = ltrim(reset($tmp), '[');
          $commit['format']     = 'git';
        }


        // get message body
        $body = explode("\n", trim(imap_fetchbody($inbox, $emailNumber, 1)));


        // check for added/deleted files at start of message
        while (isset($body[$i]) && (substr($body[$i], 0, 6) != 'commit')) {
          $body[$i] = trim($body[$i]);

          if (!empty($body[$i])) {
            // extract path and operation
            $tmp        = preg_split('/\s+/', $body[$i], -1, PREG_SPLIT_NO_EMPTY);
            $tmpPath    = '/' . $tmp[1];

            $commitFiles[$tmpPath]  = array('operation' => $tmp[0],
                                            'path'      => $tmpPath);

            // remember path so we can calculate basepath later
            $tmpPaths[] = $tmpPath;
          }

          ++$i;
        }


        // revision
        if (!isset($body[$i])) {
          // log
          continue;
        }

        $commit['revision'] = trim(str_replace('commit ', null, $body[$i]));


        // extract branch?
        if (substr($body[$i + 1], 0, 6) == 'branch') {
          $commit['branch'] = trim(str_replace('branch ', null, $body[++$i]));
        }


        // account for merges
        if (substr($body[$i + 1], 0, 5) == 'Merge') {
          $fileDelimiter = 'diff --cc';
          ++$i;

        } else {
          // no merge found
          $fileDelimiter = 'diff --git';
        }


        // date
        $commit['date'] = date('Y-m-d H:i:s', strtotime(trim(str_replace('Date:', null, $body[$i + 2]))));


        // author
        $tmp              = explode('<', $body[$i + 1]);
        $tmp              = rtrim(str_replace('>', null, end($tmp)));

        $commit['author'] = Enzyme::getAuthorInfo('account', $tmp, 'email');

        if (empty($commit['author'])) {
          // cannot find email => username, log and set as email address
          $commit['author'] = $tmp;
        }


        // extract message text
        $i              = $i + 3;
        $totalLines     = count($body);

        $commit['msg']  = null;

        while (isset($body[$i]) &&
               (strpos($body[$i], $fileDelimiter) === FALSE) &&
               ($i < $totalLines)) {

          $commit['msg'] .= $body[$i] . "\n";
          ++$i;
        }

        $commit['msg'] = Enzyme::processCommitMsg($commit['revision'], trim($commit['msg']));


        // get modified files
        while ($i < $totalLines) {
          if (strpos($body[$i], 'diff --git') !== FALSE) {
            $tmp         = explode(' ', $body[$i]);
            $tmp         = ltrim($tmp[2], 'a');

            // add to files list?
            if (!isset($commitFiles[$tmp])) {
              $commitFiles[$tmp]  = array('operation' => 'M',
                                          'path'      => $tmp);

              // remember path so we can calculate basepath later
              $tmpPaths[]  = $tmp;
            }
          }

          ++$i;
        }


        // determine base commit path
        $commit['basepath'] = Enzyme::getBasePath($tmpPaths);


        // insert modified/added/deleted files?
        if ($commitFiles) {
          foreach ($commitFiles as $commitFile) {
            // add revision ID
            $commitFile['revision'] = $commit['revision'];

            // insert into db
            Db::insert('commit_files', $commitFile, true);
          }
        }


        // insert commit into database
        Db::insert('commits', $commit, true);

        // report successful process/insertion
        Ui::displayMsg(sprintf(_('Processed revision %s'), $commit['revision']));

        // delete email message
        imap_delete($inbox, $emailNumber);

        // increment summary counter
        ++$this->summary['processed']['value'];
      }
    }


    // close connection to inbox
    imap_close($inbox);
  }
}

?>